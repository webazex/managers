<?php

namespace common\models\core;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Базовый класс для всех ActiveRecord-моделей проекта
 *
 * Основные общие фичи:
 * - автоматическое проставление created_at / updated_at
 * - базовая защита от массового присваивания
 * - подключение аудита через трейт (опционально)
 * - единый способ получения "автора" записи (для аудита и created_by/updated_by)
 */
abstract class BaseActiveRecord extends ActiveRecord
{
    /**
     * Список полей, которые автоматически заполняются при создании/обновлении
     */
    protected array $timestampAttributes = [
        'created_at' => self::TYPE_CREATE,
        'updated_at' => self::TYPE_UPDATE,
    ];

    const TYPE_CREATE = 'create';
    const TYPE_UPDATE = 'update';

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'timestamp' => [
                'class' => \yii\behaviors\TimestampBehavior::class,
                'attributes' => $this->timestampAttributes,
                'value' => new Expression('NOW()'),
            ],
        ]);
    }

    /**
     * Поля, которые нельзя массово присваивать
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * Автоматическое заполнение created_by / updated_by, если такие поля есть в таблице
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $user = Yii::$app->user ?? null;
        $userId = $user && !$user->isGuest ? $user->id : null;

        if ($insert && $this->hasAttribute('created_by')) {
            $this->created_by = $userId;
        }

        if ($this->hasAttribute('updated_by')) {
            $this->updated_by = $userId;
        }

        return true;
    }

    /**
     * Получить читаемое имя текущего пользователя / системы
     * Используется в аудите, уведомлениях и т.д.
     */
    protected function getCurrentActorLabel(): string
    {
        if (Yii::$app->user->isGuest) {
            return 'Система';
        }

        $identity = Yii::$app->user->identity;
        return $identity->username ?? $identity->email ?? 'Пользователь #' . Yii::$app->user->id;
    }

    /**
     * Получить ID текущего пользователя (или null)
     */
    protected function getCurrentUserId(): ?int
    {
        return Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id;
    }

    /**
     * Можно переопределить в конкретных моделях,
     * если нужно что-то особенное при сохранении
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Здесь можно добавить проектные хуки, например:
        // - очистка кэша
        // - отправка событий
        // - логирование в ELK / sentry (если будет)
    }
}