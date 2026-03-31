<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Пользователь системы (менеджер, редактор, администратор)
 *
 * @property int    $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $email
 * @property int    $status
 * @property int    $created_at
 * @property int    $updated_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE  = 10;

    /** Виртуальное поле — пароль при создании / смене */
    public ?string $password = null;

    public static function tableName(): string
    {
        return '{{%user}}';
    }

    public function rules(): array
    {
        return [
            [['username', 'email'], 'trim'],
            [['username', 'email'], 'required'],
            [['username', 'email'], 'string', 'max' => 255],
            ['email', 'email'],
            [['username'], 'unique'],
            [['email'],   'unique'],

            ['password', 'string', 'min' => 6, 'when' => fn($m) => $m->password !== null],
            ['status',   'default', 'value' => self::STATUS_ACTIVE],
            ['status',   'in',      'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'         => 'ID',
            'username'   => 'Логин',
            'email'      => 'E-mail',
            'password'   => 'Пароль',
            'status'     => 'Статус',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлён',
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->password !== null && $this->password !== '') {
            $this->password_hash = Yii::$app->security->generatePasswordHash($this->password);
        }

        if ($insert && !$this->auth_key) {
            $this->auth_key = Yii::$app->security->generateRandomString();
        }

        return true;
    }

    // ─────────────────────────────────────────────
    // IdentityInterface
    // ─────────────────────────────────────────────

    public static function findIdentity($id): ?self
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?self
    {
        throw new NotSupportedException('Access token auth не поддерживается');
    }

    public static function findByUsername(string $username): ?self
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey(): ?string
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    // ─────────────────────────────────────────────
    // RBAC — самые часто используемые методы
    // ─────────────────────────────────────────────

    public function getRoles(): array
    {
        return Yii::$app->authManager->getRolesByUser($this->id);
    }

    public function hasRole(string $roleName): bool
    {
        return Yii::$app->authManager->checkAccess($this->id, $roleName);
    }

    public function assignRole(string $roleName): bool
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);
        if (!$role) {
            return false;
        }

        // Один пользователь — одна основная роль (отзываем старые)
        $auth->revokeAll($this->id);
        return $auth->assign($role, $this->id) !== null;
    }

    // ─────────────────────────────────────────────
    // Хелперы для контроллеров / представлений
    // ─────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isEditor(): bool
    {
        return $this->hasRole('editor') || $this->isAdmin();
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager') || $this->isEditor();
    }
}