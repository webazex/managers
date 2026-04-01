<?php

namespace common\models\audit;

use common\base\db\BaseModel;
use yii\db\ActiveQuery;

final class AuditLog extends BaseModel
{
    public static function tableName(): string
    {
        return '{{%audit_log}}';
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['entity_type', 'action'], 'required'],
            [['entity_id', 'actor_user_id'], 'integer'],
            [['before_json', 'after_json', 'diff_json', 'context_json'], 'string'],
            [['created_at'], 'safe'],
            [['entity_type', 'request_id'], 'string', 'max' => 64],
            [['action'], 'string', 'max' => 32],
            [['actor_user_id'], 'exist', 'targetClass' => \common\models\auth\User::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
        ]);
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'actor_user_id' => 'Пользователь',
            'entity_type' => 'Тип сущности',
            'entity_id' => 'ID сущности',
            'action' => 'Действие',
            'before_json' => 'До',
            'after_json' => 'После',
            'diff_json' => 'Разница',
            'context_json' => 'Контекст',
            'request_id' => 'Request ID',
            'created_at' => 'Создано',
        ];
    }

    public function getActorUser(): ActiveQuery
    {
        return $this->hasOne(\common\models\auth\User::class, ['id' => 'actor_user_id']);
    }

    public function getBeforeData(): ?array
    {
        return $this->before_json ? json_decode($this->before_json, true) : null;
    }

    public function getAfterData(): ?array
    {
        return $this->after_json ? json_decode($this->after_json, true) : null;
    }

    public function getDiffData(): ?array
    {
        return $this->diff_json ? json_decode($this->diff_json, true) : null;
    }

    public function getContextData(): ?array
    {
        return $this->context_json ? json_decode($this->context_json, true) : null;
    }
}