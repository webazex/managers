<?php

namespace common\models\Provider;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class ProviderService extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%provider_service}}';
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
            [['code', 'name'], 'required'],
            [['code'], 'string', 'max' => 32],
            [['code'], 'unique'],
            [['name'], 'string', 'max' => 100],
            [['sort_order'], 'integer'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'code'       => 'Код',
            'name'       => 'Назва послуги',
            'sort_order' => 'Порядок сортування',
        ];
    }

    public function getProviders()
    {
        return $this->hasMany(Provider::class, ['id' => 'provider_id'])
            ->viaTable('{{%provider_service_link}}', ['service_id' => 'id']);
    }

    public static function getListForForm(): array
    {
        return self::find()
            ->select('name')
            ->indexBy('id')
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->column();
    }
}