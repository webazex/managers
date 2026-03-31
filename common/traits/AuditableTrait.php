<?php

namespace common\traits;

use common\interfaces\AuditableInterface;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Json;

trait AuditableTrait
{
    public bool $auditEnabled = true;
    public bool $auditLogInsert = false;   // обычно не логируем создание
    public bool $auditLogUpdate = true;
    public bool $auditLogDelete = true;

    protected array $auditIgnore = ['created_at', 'updated_at', 'created_by', 'updated_by'];

    public string $auditSnapshotType = 'snapshot'; // 'diff' | 'snapshot'

    protected ?string $auditEntityLabel = null;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT  => 'afterSaveAudit',
            ActiveRecord::EVENT_AFTER_UPDATE  => 'afterSaveAudit',
            ActiveRecord::EVENT_AFTER_DELETE  => 'afterDeleteAudit',
        ];
    }

    public function afterSaveAudit($event)
    {
        if (!$this->shouldBeAudited()) {
            return;
        }

        $insert = $event->name === ActiveRecord::EVENT_AFTER_INSERT;

        if ($insert && !$this->auditLogInsert) return;
        if (!$insert && !$this->auditLogUpdate) return;

        $action = $insert ? 'insert' : 'update';
        $payload = $this->collectAuditPayload($insert);

        if (empty($payload)) {
            return;
        }

        $this->saveAuditRecord($action, $this->auditSnapshotType, $payload);
    }

    public function afterDeleteAudit()
    {
        if (!$this->shouldBeAudited() || !$this->auditLogDelete) {
            return;
        }

        $payload = $this->collectAuditPayload(true) ?: ['_deleted' => true];

        $this->saveAuditRecord('delete', 'snapshot', $payload);
    }

    // ──────────────────────────────────────────────
    // Методы, которые можно переопределять в моделях
    // ──────────────────────────────────────────────

    protected function shouldBeAudited(): bool
    {
        if ($this instanceof AuditableInterface) {
            return $this->shouldBeAudited();
        }
        return $this->auditEnabled;
    }

    protected function getAuditIgnoreFields(): array
    {
        if ($this instanceof AuditableInterface) {
            return $this->getAuditIgnoreFields();
        }
        return $this->auditIgnore;
    }

    protected function getAuditEntityLabel(): string
    {
        if ($this instanceof AuditableInterface) {
            return $this->getAuditEntityLabel();
        }
        return $this->auditEntityLabel ?? static::tableName();
    }

    // ──────────────────────────────────────────────
    // Внутренняя реализация (можно переопределять)
    // ──────────────────────────────────────────────

    protected function collectAuditPayload(bool $forDelete = false): array
    {
        if ($this->auditSnapshotType === 'snapshot' || $forDelete) {
            return $this->buildSnapshot();
        }

        return $this->buildDiff();
    }

    protected function buildDiff(): array
    {
        $old = $this->getOldAttributes() ?? [];
        $current = $this->getAttributes();
        $ignore = $this->getAuditIgnoreFields();

        $diff = [];
        foreach ($current as $key => $value) {
            if (in_array($key, $ignore, true)) continue;
            $oldValue = $old[$key] ?? null;
            if ($oldValue !== $value) {
                $diff[$key] = ['old' => $oldValue, 'new' => $value];
            }
        }
        return $diff;
    }

    protected function buildSnapshot(): array
    {
        return [
            'entity' => $this->getAuditEntityLabel(),
            'id'     => $this->getPrimaryKey(),
            'data'   => $this->getAttributes(),
        ];
    }

    protected function saveAuditRecord(string $action, string $type, array $payload): void
    {
        $actor = $this->resolveActor();

        try {
            Yii::$app->db->createCommand()->insert('{{%data_history}}', [
                'actor_type'    => $actor['type'],
                'actor_id'      => $actor['id'],
                'actor_name'    => $actor['name'],

                'action'        => $action,
                'snapshot_type' => $type,
                'payload'       => Json::encode($payload, JSON_UNESCAPED_UNICODE),

                'happened_at'   => new Expression('NOW()'),
                'table_name'    => static::tableName(),
                'record_id'     => (int)($this->getPrimaryKey() ?? 0),
            ])->execute();
        } catch (\Throwable $e) {
            Yii::error("Audit save failed: " . $e->getMessage(), __METHOD__);
        }
    }

    private function resolveActor(): array
    {
        if (Yii::$app->user->isGuest) {
            return ['type' => 'system', 'id' => null, 'name' => 'System'];
        }

        $user = Yii::$app->user->identity;
        $id = $user->getId() ?? null;

        return [
            'type' => 'user',
            'id'   => is_numeric($id) ? (int)$id : null,
            'name' => $user->username ?? 'User #' . $id,
        ];
    }
}