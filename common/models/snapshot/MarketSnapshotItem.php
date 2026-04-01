<?php

namespace common\models\snapshot;

use common\base\db\BaseModel;
use common\interfaces\AuditableInterface;
use common\traits\ProvidesAuditContextTrait;
use common\models\catalog\Product;
use common\models\catalog\Provider;
use yii\db\ActiveQuery;

final class MarketSnapshotItem extends BaseModel implements AuditableInterface
{
    use ProvidesAuditContextTrait;

    public const STATUS_AVAILABLE = 'available';

    public const SOURCE_CARRY_FORWARD = 'carry_forward';
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_IMPORT = 'import';
    public const SOURCE_SYSTEM = 'system';

    public static function tableName(): string
    {
        return '{{%market_snapshot_item}}';
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['snapshot_id', 'snapshot_date', 'provider_id', 'product_id'], 'required'],
            [['snapshot_id', 'provider_id', 'product_id', 'created_by'], 'integer'],
            [['price'], 'number'],
            [['snapshot_date', 'created_at'], 'safe'],
            [['currency'], 'string', 'length' => 3],
            [['availability_status', 'source_type'], 'string', 'max' => 32],
            [['is_copied', 'changed_in_snapshot'], 'boolean'],
            [['snapshot_id', 'provider_id', 'product_id'], 'unique', 'targetAttribute' => ['snapshot_id', 'provider_id', 'product_id']],
            [['snapshot_id'], 'exist', 'targetClass' => MarketSnapshot::class, 'targetAttribute' => 'id'],
            [['provider_id'], 'exist', 'targetClass' => Provider::class, 'targetAttribute' => 'id'],
            [['product_id'], 'exist', 'targetClass' => Product::class, 'targetAttribute' => 'id'],
            [['created_by'], 'exist', 'targetClass' => \common\models\auth\User::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
        ]);
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'snapshot_id' => 'Срез',
            'snapshot_date' => 'Дата среза',
            'provider_id' => 'Провайдер',
            'product_id' => 'Продукт',
            'price' => 'Цена',
            'currency' => 'Валюта',
            'availability_status' => 'Статус доступности',
            'source_type' => 'Источник',
            'is_copied' => 'Скопировано',
            'changed_in_snapshot' => 'Изменено в срезе',
            'created_at' => 'Создано',
            'created_by' => 'Создал',
        ];
    }

    public function getAuditContext(): array
    {
        return [
            'market_snapshot_item_id' => $this->id,
            'snapshot_id' => $this->snapshot_id,
            'provider_id' => $this->provider_id,
            'product_id' => $this->product_id,
            'snapshot_date' => $this->snapshot_date,
        ];
    }

    public function getSnapshot(): ActiveQuery
    {
        return $this->hasOne(MarketSnapshot::class, ['id' => 'snapshot_id']);
    }

    public function getProvider(): ActiveQuery
    {
        return $this->hasOne(Provider::class, ['id' => 'provider_id']);
    }

    public function getProduct(): ActiveQuery
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    public function getCreatedByUser(): ActiveQuery
    {
        return $this->hasOne(\common\models\auth\User::class, ['id' => 'created_by']);
    }
}