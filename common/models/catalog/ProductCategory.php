<?php

namespace common\models\catalog;

use common\base\db\BaseModel;
use common\interfaces\AuditableInterface;
use common\traits\ProvidesAuditContextTrait;
use yii\db\ActiveQuery;

final class ProductCategory extends BaseModel implements AuditableInterface
{
    use ProvidesAuditContextTrait;

    public const CODE_INTERNET = 'internet';
    public const CODE_INTERNET_TV = 'internet_tv';
    public const CODE_DOMAINS = 'domains';

    public static function tableName(): string
    {
        return '{{%product_category}}';
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['code', 'name'], 'required'],
            [['code'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 255],
            [['is_active'], 'boolean'],
            [['sort_order'], 'integer'],
            [['code'], 'unique'],
        ]);
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'code' => 'Код категории',
            'name' => 'Название',
            'is_active' => 'Активна',
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
            'product_category_id' => $this->id,
            'product_category_code' => $this->code,
            'product_category_name' => $this->name,
        ];
    }

    public function getProducts(): ActiveQuery
    {
        return $this->hasMany(Product::class, ['category_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getSnapshots(): ActiveQuery
    {
        return $this->hasMany(\common\models\snapshot\MarketSnapshot::class, ['category_id' => 'id'])
            ->orderBy(['snapshot_date' => SORT_DESC, 'revision' => SORT_DESC, 'id' => SORT_DESC]);
    }

    public function getCreatedByUser(): ActiveQuery
    {
        return $this->hasOne(\common\models\auth\User::class, ['id' => 'created_by']);
    }

    public function getUpdatedByUser(): ActiveQuery
    {
        return $this->hasOne(\common\models\auth\User::class, ['id' => 'updated_by']);
    }
}