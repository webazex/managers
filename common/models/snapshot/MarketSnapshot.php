<?php

namespace common\models\snapshot;

use common\base\db\BaseModel;
use common\interfaces\AuditableInterface;
use common\traits\ProvidesAuditContextTrait;
use common\models\catalog\ProductCategory;
use common\models\catalog\Provider;
use yii\db\ActiveQuery;

final class MarketSnapshot extends BaseModel implements AuditableInterface
{
    use ProvidesAuditContextTrait;

    public const STATUS_PUBLISHED = 'published';

    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_IMPORT = 'import';
    public const SOURCE_SYSTEM = 'system';
    public const SOURCE_PARSER = 'parser';

    public static function tableName(): string
    {
        return '{{%market_snapshot}}';
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['category_id', 'snapshot_date'], 'required'],
            [['category_id', 'revision', 'trigger_provider_id', 'based_on_snapshot_id', 'created_by'], 'integer'],
            [['snapshot_date', 'created_at'], 'safe'],
            [['status'], 'string', 'max' => 32],
            [['source_type'], 'string', 'max' => 32],
            [['comment'], 'string', 'max' => 255],
            [['category_id', 'snapshot_date', 'revision'], 'unique', 'targetAttribute' => ['category_id', 'snapshot_date', 'revision']],
            [['category_id'], 'exist', 'targetClass' => ProductCategory::class, 'targetAttribute' => 'id'],
            [['trigger_provider_id'], 'exist', 'targetClass' => Provider::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['based_on_snapshot_id'], 'exist', 'targetClass' => self::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['created_by'], 'exist', 'targetClass' => \common\models\auth\User::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
        ]);
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'category_id' => 'Категория',
            'snapshot_date' => 'Дата среза',
            'revision' => 'Ревизия',
            'status' => 'Статус',
            'source_type' => 'Источник',
            'trigger_provider_id' => 'Провайдер-инициатор',
            'based_on_snapshot_id' => 'Базовый срез',
            'comment' => 'Комментарий',
            'created_at' => 'Создано',
            'created_by' => 'Создал',
        ];
    }

    public function getAuditContext(): array
    {
        return [
            'market_snapshot_id' => $this->id,
            'category_id' => $this->category_id,
            'snapshot_date' => $this->snapshot_date,
            'revision' => $this->revision,
            'status' => $this->status,
        ];
    }

    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(ProductCategory::class, ['id' => 'category_id']);
    }

    public function getTriggerProvider(): ActiveQuery
    {
        return $this->hasOne(Provider::class, ['id' => 'trigger_provider_id']);
    }

    public function getBaseSnapshot(): ActiveQuery
    {
        return $this->hasOne(self::class, ['id' => 'based_on_snapshot_id']);
    }

    public function getChildSnapshots(): ActiveQuery
    {
        return $this->hasMany(self::class, ['based_on_snapshot_id' => 'id']);
    }

    public function getItems(): ActiveQuery
    {
        return $this->hasMany(MarketSnapshotItem::class, ['snapshot_id' => 'id']);
    }

    public function getProviderNotes(): ActiveQuery
    {
        return $this->hasMany(ProviderSnapshotNote::class, ['snapshot_id' => 'id']);
    }

    public function getCreatedByUser(): ActiveQuery
    {
        return $this->hasOne(\common\models\auth\User::class, ['id' => 'created_by']);
    }
}