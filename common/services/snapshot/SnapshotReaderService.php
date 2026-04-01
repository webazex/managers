<?php

namespace common\services\snapshot;

use common\models\catalog\ProductCategory;
use common\models\snapshot\MarketSnapshot;
use common\models\snapshot\MarketSnapshotItem;
use yii\base\Component;

final class SnapshotReaderService extends Component
{
    public function findLatestSnapshotByCategoryCode(string $categoryCode): ?MarketSnapshot
    {
        $category = ProductCategory::findOne(['code' => $categoryCode]);

        if ($category === null) {
            return null;
        }

        return MarketSnapshot::find()
            ->where(['category_id' => $category->id])
            ->orderBy([
                'snapshot_date' => SORT_DESC,
                'revision' => SORT_DESC,
                'id' => SORT_DESC,
            ])
            ->one();
    }

    public function findSnapshotByDate(string $categoryCode, string $snapshotDate): ?MarketSnapshot
    {
        $category = ProductCategory::findOne(['code' => $categoryCode]);

        if ($category === null) {
            return null;
        }

        return MarketSnapshot::find()
            ->where([
                'category_id' => $category->id,
                'snapshot_date' => $snapshotDate,
            ])
            ->orderBy([
                'revision' => SORT_DESC,
                'id' => SORT_DESC,
            ])
            ->one();
    }

    /**
     * @return MarketSnapshotItem[]
     */
    public function getSnapshotItems(int $snapshotId): array
    {
        return MarketSnapshotItem::find()
            ->where(['snapshot_id' => $snapshotId])
            ->with(['provider', 'product', 'snapshot'])
            ->orderBy([
                'provider_id' => SORT_ASC,
                'product_id' => SORT_ASC,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildComparisonRows(int $snapshotId, int $providerAId, int $providerBId): array
    {
        $items = MarketSnapshotItem::find()
            ->where([
                'snapshot_id' => $snapshotId,
                'provider_id' => [$providerAId, $providerBId],
            ])
            ->with(['product', 'provider'])
            ->all();

        $rows = [];

        foreach ($items as $item) {
            $productId = $item->product_id;

            if (!isset($rows[$productId])) {
                $rows[$productId] = [
                    'product_id' => $productId,
                    'product_code' => $item->product?->code,
                    'product_name' => $item->product?->name,
                    'provider_a_price' => null,
                    'provider_b_price' => null,
                    'delta' => null,
                ];
            }

            if ($item->provider_id === $providerAId) {
                $rows[$productId]['provider_a_price'] = $item->price;
            }

            if ($item->provider_id === $providerBId) {
                $rows[$productId]['provider_b_price'] = $item->price;
            }
        }

        foreach ($rows as &$row) {
            if ($row['provider_a_price'] !== null && $row['provider_b_price'] !== null) {
                $row['delta'] = (float) $row['provider_a_price'] - (float) $row['provider_b_price'];
            }
        }

        usort($rows, static function (array $a, array $b): int {
            return strcmp((string) $a['product_code'], (string) $b['product_code']);
        });

        return $rows;
    }
}