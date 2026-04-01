<?php

namespace common\services\snapshot;

use common\models\catalog\ProductCategory;
use common\models\catalog\Provider;
use common\models\snapshot\MarketSnapshot;
use common\models\snapshot\MarketSnapshotItem;
use common\models\snapshot\ProviderSnapshotNote;
use common\services\audit\AuditLogWriterService;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\db\Transaction;

final class SnapshotWriterService extends Component
{
    public function __construct(
        private readonly AuditLogWriterService $auditLogWriterService,
                                               $config = []
    ) {
        parent::__construct($config);
    }

    /**
     * @param array<string, int|float|string|null> $pricesByProductCode
     * @param array{promotion_text?: ?string, loyalty_text?: ?string, editor_note?: ?string} $notes
     */
    public function createProviderSnapshotRevision(
        string $categoryCode,
        int $providerId,
        array $pricesByProductCode,
        array $notes = [],
        ?string $comment = null
    ): MarketSnapshot {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction(Transaction::SERIALIZABLE);

        try {
            $category = ProductCategory::findOneOrFail(['code' => $categoryCode]);
            $provider = Provider::findOneOrFail($providerId);

            $snapshotDate = date('Y-m-d');
            $previousSnapshot = $this->findLatestSnapshot($category->id);
            $revision = $this->resolveNextRevision($category->id, $snapshotDate);

            $snapshot = new MarketSnapshot([
                'category_id' => $category->id,
                'snapshot_date' => $snapshotDate,
                'revision' => $revision,
                'status' => MarketSnapshot::STATUS_PUBLISHED,
                'source_type' => MarketSnapshot::SOURCE_MANUAL,
                'trigger_provider_id' => $provider->id,
                'based_on_snapshot_id' => $previousSnapshot?->id,
                'comment' => $comment,
                'created_by' => Yii::$app->user && !Yii::$app->user->isGuest ? (int) Yii::$app->user->id : null,
            ]);

            if (!$snapshot->save()) {
                throw new Exception('Failed to create market snapshot: ' . json_encode($snapshot->errors, JSON_UNESCAPED_UNICODE));
            }

            if ($previousSnapshot !== null) {
                $this->copyPreviousItems($previousSnapshot, $snapshot);
                $this->copyPreviousNotes($previousSnapshot, $snapshot);
            }

            $this->applyProviderPrices($snapshot, $provider, $pricesByProductCode);
            $this->applyProviderNotes($snapshot, $provider, $notes);

            $this->auditLogWriterService->write(
                action: 'snapshot_create',
                entity: $snapshot,
                before: $previousSnapshot?->attributes,
                after: $snapshot->attributes,
                context: [
                    'category_code' => $category->code,
                    'provider_code' => $provider->code,
                    'provider_name' => $provider->name,
                ]
            );

            $transaction->commit();

            return $snapshot;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function resolveNextRevision(int $categoryId, string $snapshotDate): int
    {
        $maxRevision = (int) MarketSnapshot::find()
            ->where([
                'category_id' => $categoryId,
                'snapshot_date' => $snapshotDate,
            ])
            ->max('revision');

        return $maxRevision > 0 ? $maxRevision + 1 : 1;
    }

    private function findLatestSnapshot(int $categoryId): ?MarketSnapshot
    {
        return MarketSnapshot::find()
            ->where(['category_id' => $categoryId])
            ->orderBy([
                'snapshot_date' => SORT_DESC,
                'revision' => SORT_DESC,
                'id' => SORT_DESC,
            ])
            ->one();
    }

    private function copyPreviousItems(MarketSnapshot $from, MarketSnapshot $to): void
    {
        /** @var MarketSnapshotItem[] $items */
        $items = MarketSnapshotItem::find()
            ->where(['snapshot_id' => $from->id])
            ->all();

        foreach ($items as $item) {
            $copy = new MarketSnapshotItem([
                'snapshot_id' => $to->id,
                'snapshot_date' => $to->snapshot_date,
                'provider_id' => $item->provider_id,
                'product_id' => $item->product_id,
                'price' => $item->price,
                'currency' => $item->currency,
                'availability_status' => $item->availability_status,
                'source_type' => MarketSnapshotItem::SOURCE_CARRY_FORWARD,
                'is_copied' => true,
                'changed_in_snapshot' => false,
                'created_by' => Yii::$app->user && !Yii::$app->user->isGuest ? (int) Yii::$app->user->id : null,
            ]);

            if (!$copy->save()) {
                throw new Exception('Failed to copy market snapshot item: ' . json_encode($copy->errors, JSON_UNESCAPED_UNICODE));
            }
        }
    }

    private function copyPreviousNotes(MarketSnapshot $from, MarketSnapshot $to): void
    {
        /** @var ProviderSnapshotNote[] $notes */
        $notes = ProviderSnapshotNote::find()
            ->where(['snapshot_id' => $from->id])
            ->all();

        foreach ($notes as $note) {
            $copy = new ProviderSnapshotNote([
                'snapshot_id' => $to->id,
                'snapshot_date' => $to->snapshot_date,
                'provider_id' => $note->provider_id,
                'promotion_text' => $note->promotion_text,
                'loyalty_text' => $note->loyalty_text,
                'editor_note' => $note->editor_note,
                'source_type' => ProviderSnapshotNote::SOURCE_CARRY_FORWARD,
                'is_copied' => true,
                'changed_in_snapshot' => false,
                'created_by' => Yii::$app->user && !Yii::$app->user->isGuest ? (int) Yii::$app->user->id : null,
            ]);

            if (!$copy->save()) {
                throw new Exception('Failed to copy provider snapshot note: ' . json_encode($copy->errors, JSON_UNESCAPED_UNICODE));
            }
        }
    }

    /**
     * @param array<string, int|float|string|null> $pricesByProductCode
     */
    private function applyProviderPrices(MarketSnapshot $snapshot, Provider $provider, array $pricesByProductCode): void
    {
        $products = $snapshot->category->products;

        foreach ($products as $product) {
            if (!array_key_exists($product->code, $pricesByProductCode)) {
                continue;
            }

            $item = MarketSnapshotItem::findOne([
                'snapshot_id' => $snapshot->id,
                'provider_id' => $provider->id,
                'product_id' => $product->id,
            ]);

            if ($item === null) {
                $item = new MarketSnapshotItem([
                    'snapshot_id' => $snapshot->id,
                    'snapshot_date' => $snapshot->snapshot_date,
                    'provider_id' => $provider->id,
                    'product_id' => $product->id,
                ]);
            }

            $item->price = $pricesByProductCode[$product->code] !== null
                ? (float) $pricesByProductCode[$product->code]
                : null;
            $item->currency = 'UAH';
            $item->availability_status = MarketSnapshotItem::STATUS_AVAILABLE;
            $item->source_type = MarketSnapshotItem::SOURCE_MANUAL;
            $item->is_copied = false;
            $item->changed_in_snapshot = true;
            $item->created_by = Yii::$app->user && !Yii::$app->user->isGuest ? (int) Yii::$app->user->id : null;

            if (!$item->save()) {
                throw new Exception('Failed to save provider price: ' . json_encode($item->errors, JSON_UNESCAPED_UNICODE));
            }
        }
    }

    /**
     * @param array{promotion_text?: ?string, loyalty_text?: ?string, editor_note?: ?string} $notes
     */
    private function applyProviderNotes(MarketSnapshot $snapshot, Provider $provider, array $notes): void
    {
        if ($notes === []) {
            return;
        }

        $model = ProviderSnapshotNote::findOne([
            'snapshot_id' => $snapshot->id,
            'provider_id' => $provider->id,
        ]);

        if ($model === null) {
            $model = new ProviderSnapshotNote([
                'snapshot_id' => $snapshot->id,
                'snapshot_date' => $snapshot->snapshot_date,
                'provider_id' => $provider->id,
            ]);
        }

        $model->promotion_text = $notes['promotion_text'] ?? null;
        $model->loyalty_text = $notes['loyalty_text'] ?? null;
        $model->editor_note = $notes['editor_note'] ?? null;
        $model->source_type = ProviderSnapshotNote::SOURCE_MANUAL;
        $model->is_copied = false;
        $model->changed_in_snapshot = true;
        $model->created_by = Yii::$app->user && !Yii::$app->user->isGuest ? (int) Yii::$app->user->id : null;

        if (!$model->save()) {
            throw new Exception('Failed to save provider snapshot note: ' . json_encode($model->errors, JSON_UNESCAPED_UNICODE));
        }
    }
}