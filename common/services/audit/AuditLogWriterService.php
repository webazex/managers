<?php

namespace common\services\audit;

use common\interfaces\AuditableInterface;
use common\models\audit\AuditLog;
use Yii;
use yii\base\Component;
use yii\base\Exception;

final class AuditLogWriterService extends Component
{
    public function write(
        string $action,
        ?AuditableInterface $entity = null,
        ?array $before = null,
        ?array $after = null,
        array $context = []
    ): AuditLog {
        $model = new AuditLog();

        $model->actor_user_id = Yii::$app->user && !Yii::$app->user->isGuest
            ? (int) Yii::$app->user->id
            : null;

        $model->entity_type = $entity?->getAuditEntityType() ?? 'system';
        $model->entity_id = property_exists($entity, 'id') ? ($entity->id ?? null) : null;
        $model->action = $action;
        $model->before_json = $before !== null ? json_encode($before, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
        $model->after_json = $after !== null ? json_encode($after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
        $model->diff_json = json_encode($this->buildDiff($before, $after), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $model->context_json = json_encode(array_merge(
            $entity?->getAuditContext() ?? [],
            $context
        ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $model->request_id = $this->resolveRequestId();

        if (!$model->save()) {
            throw new Exception('Failed to write audit log: ' . json_encode($model->errors, JSON_UNESCAPED_UNICODE));
        }

        return $model;
    }

    private function buildDiff(?array $before, ?array $after): array
    {
        $before ??= [];
        $after ??= [];

        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        $diff = [];

        foreach ($keys as $key) {
            $beforeValue = $before[$key] ?? null;
            $afterValue = $after[$key] ?? null;

            if ($beforeValue !== $afterValue) {
                $diff[$key] = [
                    'before' => $beforeValue,
                    'after' => $afterValue,
                ];
            }
        }

        return $diff;
    }

    private function resolveRequestId(): ?string
    {
        $headers = Yii::$app->request?->headers;

        return $headers?->get('X-Request-Id')
            ?: $headers?->get('X-Correlation-Id')
                ?: null;
    }
}