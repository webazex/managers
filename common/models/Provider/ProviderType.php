<?php

namespace common\models\Provider;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Справочник типов провайдеров
 * (internet, internet+TV, domains и т.д.)
 *
 * @property int    $id
 * @property string $name           название типа (например: "internet")
 * @property string $created_at     datetime
 * @property string $updated_at     datetime
 */
class ProviderType extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%provider_type}}';
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
                'message' => 'Тип провайдера с таким названием уже существует',
            ],

            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'         => 'ID',
            'name'       => 'Название типа',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }

    /**
     * Провайдеры, которые относятся к этому типу
     */
    public function getProviders()
    {
        return $this->hasMany(Provider::class, ['type_id' => 'id']);
    }

    public function __toString(): string
    {
        return $this->name ?: '(тип не указан)';
    }

    /**
     * метод для получения списка в виде id => name
     * (удобно для dropdown в формах)
     */
    public static function getDropdownList(): array
    {
        return self::find()
            ->select(['name', 'id'])
            ->orderBy(['name' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }
}