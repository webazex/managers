<?php

namespace common\models\catalog;

use common\base\db\BaseModel;
use common\interfaces\AuditableInterface;
use common\traits\ProvidesAuditContextTrait;
use yii\db\ActiveQuery;

final class Provider extends BaseModel implements AuditableInterface
{
    use ProvidesAuditContextTrait;

    public static function tableName(): string
    {
        return '{{%provider}}';
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['code', 'name'], 'required'],
            [['code'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 255],
            [['website_url'], 'string', 'max' => 512],
            [['is_our', 'is_active'], 'boolean'],
            [['sort_order'], 'integer'],
            [['code'], 'unique'],
        ]);
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'code' => 'Код',
            'name' => 'Название',
            'website_url' => 'Сайт',
            'is_our' => 'Наш провайдер',
            'is_active' => 'Активен',
            'sort_order' => 'Порядок сортировки',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
            'created_by' => 'Создал',
            'updated_by' => 'Обновил',
        ];
    }

    public function getAuditContext(): array
    {
        return [
            'provider_id' => $this->id,
            'provider_code' => $this->code,
            'provider_name' => $this->name,
        ];
    }

    public function getCreatedByUser(): ActiveQuery
    {
        return $this->hasOne(\common\models\auth\User::class, ['id' => 'created_by']);
    }

    public function getUpdatedByUser(): ActiveQuery
    {
        return $this->hasOne(\common\models\auth\User::class, ['id' => 'updated_by']);
    }
    public function getSnapshotItems(): ActiveQuery
    {
        return $this->hasMany(\common\models\snapshot\MarketSnapshotItem::class, ['provider_id' => 'id']);
    }

    public function getSnapshotNotes(): ActiveQuery
    {
        return $this->hasMany(\common\models\snapshot\ProviderSnapshotNote::class, ['provider_id' => 'id']);
    }

    public function getTriggeredSnapshots(): ActiveQuery
    {
        return $this->hasMany(\common\models\snapshot\MarketSnapshot::class, ['trigger_provider_id' => 'id']);
    }
}