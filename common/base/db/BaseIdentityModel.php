<?php

namespace common\base\db;

use Yii;
use yii\web\IdentityInterface;

abstract class BaseIdentityModel extends BaseModel implements IdentityInterface
{
    public const STATUS_DELETED = 0;
    public const STATUS_ACTIVE = 10;

    public static function findIdentity($id): ?static
    {
        return static::findOne([
            'id' => $id,
            'status' => static::STATUS_ACTIVE,
        ]);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?static
    {
        return null;
    }

    abstract public static function findByUsername(string $username): ?static;

    public function getId(): int|string
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

    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }
}