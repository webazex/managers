<?php

namespace common\base\db;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\NotFoundHttpException;

abstract class BaseModel extends ActiveRecord
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ]);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'safe'],
        ]);
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $userId = $this->resolveCurrentUserId();

        if ($insert && $this->hasAttribute('created_by') && $this->created_by === null) {
            $this->created_by = $userId;
        }

        if ($this->hasAttribute('updated_by')) {
            $this->updated_by = $userId;
        }

        return true;
    }

    public static function findOneOrFail($condition): static
    {
        $model = static::findOne($condition);

        if ($model === null) {
            throw new NotFoundHttpException(static::class . ' not found');
        }

        return $model;
    }

    protected function getCurrentActorLabel(): string
    {
        if (!$this->hasUserComponent()) {
            return 'Система';
        }

        $user = Yii::$app->get('user');

        if ($user->isGuest) {
            return 'Система';
        }

        $identity = $user->identity;

        return $identity->username
            ?? $identity->email
            ?? ('Пользователь #' . $user->id);
    }

    protected function getCurrentUserId(): ?int
    {
        return $this->resolveCurrentUserId();
    }

    protected function resolveCurrentUserId(): ?int
    {
        if (!$this->hasUserComponent()) {
            return null;
        }

        $user = Yii::$app->get('user');

        if ($user->isGuest) {
            return null;
        }

        return (int) $user->id;
    }

    protected function hasUserComponent(): bool
    {
        return Yii::$app !== null && Yii::$app->has('user', true);
    }
}