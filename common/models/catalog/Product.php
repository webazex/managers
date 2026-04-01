<?php

namespace common\models\catalog;

use common\base\db\BaseModel;
use common\interfaces\AuditableInterface;
use common\traits\ProvidesAuditContextTrait;
use yii\db\ActiveQuery;

final class Product extends BaseModel implements AuditableInterface
{
    use ProvidesAuditContextTrait;

    public static function tableName(): string
    {
        return '{{%product}}';
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['category_id', 'code', 'name'], 'required'],
            [['category_id', 'speed_mbps', 'sort_order'], 'integer'],
            [['tv_included', 'is_active'], 'boolean'],
            [['code'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 255],
            [['domain_zone', 'unit'], 'string', 'max' => 32],
            [['category_id', 'code'], 'unique', 'targetAttribute' => ['category_id', 'code']],
            [['category_id'], 'exist', 'targetClass' => ProductCategory::class, 'targetAttribute' => 'id'],
        ]);
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'category_id' => 'Категория',
            'code' => 'Код продукта',
            'name' => 'Название',
            'speed_mbps' => 'Скорость',
            'tv_included' => 'ТВ включено',
            'domain_zone' => 'Доменная зона',
            'unit' => 'Единица измерения',
            'sort_order' => 'Порядок сортировки',
            'is_active' => 'Активен',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
            'created_by' => 'Создал',
            'updated_by' => 'Обновил',
        ];
    }

    public function getAuditContext(): array
    {
        return [
            'product_id' => $this->id,
            'product_code' => $this->code,
            'product_name' => $this->name,
            'category_id' => $this->category_id,
        ];
    }

    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(ProductCategory::class, ['id' => 'category_id']);
    }

    public function getSnapshotItems(): ActiveQuery
    {
        return $this->hasMany(\common\models\snapshot\MarketSnapshotItem::class, ['product_id' => 'id']);
    }

    public function getCreatedByUser(): ActiveQuery
    {
        return $this->hasOne(\common\models\auth\User::class, ['id' => 'created_by']);
    }

    public function getUpdatedByUser(): ActiveQuery
    {
        return $this->hasOne(\common\models\auth\User::class, ['id' => 'updated_by']);
    }

    public function isInternet(): bool
    {
        return $this->category?->code === ProductCategory::CODE_INTERNET;
    }

    public function isBundle(): bool
    {
        return $this->category?->code === ProductCategory::CODE_INTERNET_TV;
    }

    public function isDomain(): bool
    {
        return $this->category?->code === ProductCategory::CODE_DOMAINS;
    }
}