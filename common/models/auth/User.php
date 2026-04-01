<?php

namespace common\models\auth;

use common\base\db\BaseIdentityModel;

final class User extends BaseIdentityModel
{
    public ?string $password = null;

    public static function tableName(): string
    {
        return '{{%user}}';
    }

    public static function findByUsername(string $username): ?static
    {
        return static::findOne([
            'username' => $username,
            'status' => static::STATUS_ACTIVE,
        ]);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['username', 'email'], 'required'],
            [['username', 'email'], 'string', 'max' => 255],
            ['email', 'email'],
            [['username'], 'unique'],
            [['email'], 'unique'],
            ['status', 'integer'],
            ['password', 'string', 'min' => 8],
        ]);
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert && empty($this->auth_key)) {
            $this->generateAuthKey();
        }

        if (!empty($this->password)) {
            $this->setPassword($this->password);
        }

        return true;
    }
}