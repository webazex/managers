<?php

namespace common\models\snapshot;

use common\base\db\BaseModel;
use common\interfaces\AuditableInterface;
use common\traits\ProvidesAuditContextTrait;
use common\models\catalog\Provider;
use yii\db\ActiveQuery;

final class ProviderSnapshotNote extends BaseModel implements AuditableInterface
{
    use ProvidesAuditContextTrait;

    public const SOURCE_CARRY_FORWARD = 'carry_forward';
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_IMPORT = 'import';
    public const SOURCE_SYSTEM = 'system';

    public static function tableName(): string
    {
        return '{{%provider_snapshot_note}}';
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['snapshot_id', 'snapshot_date', 'provider_id'], 'required'],
            [['snapshot_id', 'provider_id', 'created_by'], 'integer'],
            [['promotion_text', 'loyalty_text', 'editor_note'], 'string'],
            [['snapshot_date', 'created_at'], 'safe'],
            [['source_type'], 'string', 'max' => 32],
            [['is_copied', 'changed_in_snapshot'], 'boolean'],
            [['snapshot_id', 'provider_id'], 'unique', 'targetAttribute' => ['snapshot_id', 'provider_id']],
            [['snapshot_id'], 'exist', 'targetClass' => MarketSnapshot::class, 'targetAttribute' => 'id'],
            [['provider_id'], 'exist', 'targetClass' => Provider::class, 'targetAttribute' => 'id'],
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
            'promotion_text' => 'Текст акции',
            'loyalty_text' => 'Текст лояльности',
            'editor_note' => 'Заметка редактора',
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
            'provider_snapshot_note_id' => $this->id,
            'snapshot_id' => $this->snapshot_id,
            'provider_id' => $this->provider_id,
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

    public function getCreatedByUser(): ActiveQuery
    {
        return $this->hasOne(\common\models\auth\User::class, ['id' => 'created_by']);
    }
}