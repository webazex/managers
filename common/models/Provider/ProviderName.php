<?php

namespace common\models\Provider;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Справочник нормализованных / уникальных названий провайдеров
 *
 * @property int    $id
 * @property string $name           уникальное название
 * @property string $created_at
 * @property string $updated_at
 */
class ProviderName extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%provider_name}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique',
                'message' => 'Провайдер с таким названием уже есть в справочнике',
            ],

            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'         => 'ID',
            'name'       => 'Название провайдера',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }

    public function getProviders()
    {
        return $this->hasMany(Provider::class, ['provider_name_id' => 'id']);
    }

    public function __toString(): string
    {
        return $this->name ?: '(без названия)';
    }
}