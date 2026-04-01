<?php

namespace console\controllers;

use common\models\catalog\Provider;
use common\models\snapshot\MarketSnapshotItem;
use common\services\snapshot\SnapshotReaderService;
use common\services\snapshot\SnapshotWriterService;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

final class SnapshotController extends Controller
{
    public function actionCreateTest(): int
    {
        /** @var Provider|null $provider */
        $provider = Provider::findOne(['code' => 'tenet']);

        if ($provider === null) {
            $this->stderr("Provider TENET not found.\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        /** @var SnapshotWriterService $writer */
        $writer = Yii::createObject(SnapshotWriterService::class);

        $snapshot = $writer->createProviderSnapshotRevision(
            categoryCode: 'internet',
            providerId: (int) $provider->id,
            pricesByProductCode: [
                '75' => 259,
                '100' => 338,
                '250' => 385,
                '500' => 439,
                '1000' => 560,
            ],
            notes: [
                'promotion_text' => 'Гигабит + ТВ - 130 грн/мес (6 мес). Терминал 599 грн.',
                'loyalty_text' => 'Скидка до 20% в зависимости от стажа. Бонусы на счет.',
                'editor_note' => 'Тестовый снапшот после hard reset.',
            ],
            comment: 'Первичный тестовый снапшот TENET'
        );

        $this->stdout("Snapshot created successfully.\n", Console::FG_GREEN);
        $this->stdout("Snapshot ID: {$snapshot->id}\n");
        $this->stdout("Date: {$snapshot->snapshot_date}\n");
        $this->stdout("Revision: {$snapshot->revision}\n");

        return ExitCode::OK;
    }

    public function actionCreateCarryForwardTest(): int
    {
        $provider = $this->findOrCreateProvider(
            code: 'briz',
            name: 'BRIZ',
            websiteUrl: 'https://briz.ua',
            isOur: false,
            sortOrder: 20
        );

        /** @var SnapshotWriterService $writer */
        $writer = Yii::createObject(SnapshotWriterService::class);

        $snapshot = $writer->createProviderSnapshotRevision(
            categoryCode: 'internet',
            providerId: (int) $provider->id,
            pricesByProductCode: [
                '100' => 299,
                '250' => 349,
                '500' => 399,
                '1000' => 499,
            ],
            notes: [
                'promotion_text' => 'Подключение 1 грн до конца месяца.',
                'loyalty_text' => 'Скидка при оплате за 6 месяцев.',
                'editor_note' => 'Тест carry-forward для второго провайдера.',
            ],
            comment: 'Тестовый снапшот BRIZ с переносом предыдущих данных'
        );

        $this->stdout("Carry-forward snapshot created successfully.\n", Console::FG_GREEN);
        $this->stdout("Snapshot ID: {$snapshot->id}\n");
        $this->stdout("Date: {$snapshot->snapshot_date}\n");
        $this->stdout("Revision: {$snapshot->revision}\n");
        $this->stdout("Trigger provider: {$provider->code}\n");

        return ExitCode::OK;
    }

    public function actionShowLatest(string $categoryCode = 'internet'): int
    {
        /** @var SnapshotReaderService $reader */
        $reader = Yii::createObject(SnapshotReaderService::class);

        $snapshot = $reader->findLatestSnapshotByCategoryCode($categoryCode);

        if ($snapshot === null) {
            $this->stderr("Snapshot not found for category: {$categoryCode}\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Latest snapshot\n", Console::FG_GREEN);
        $this->stdout("ID: {$snapshot->id}\n");
        $this->stdout("Category: {$snapshot->category->code}\n");
        $this->stdout("Date: {$snapshot->snapshot_date}\n");
        $this->stdout("Revision: {$snapshot->revision}\n");
        $this->stdout("Trigger provider: " . ($snapshot->triggerProvider?->code ?? '-') . "\n");
        $this->stdout("Based on snapshot: " . ($snapshot->based_on_snapshot_id ?? '-') . "\n");
        $this->stdout("Comment: " . ($snapshot->comment ?? '-') . "\n\n");

        $items = MarketSnapshotItem::find()
            ->where(['snapshot_id' => $snapshot->id])
            ->with(['provider', 'product'])
            ->orderBy([
                'provider_id' => SORT_ASC,
                'product_id' => SORT_ASC,
            ])
            ->all();

        foreach ($items as $item) {
            $providerCode = $item->provider?->code ?? 'unknown';
            $productCode = $item->product?->code ?? 'unknown';
            $price = $item->price !== null ? $item->price : 'NULL';

            $this->stdout(sprintf(
                "[%s] product=%s price=%s source=%s copied=%s changed=%s\n",
                $providerCode,
                $productCode,
                $price,
                $item->source_type,
                $item->is_copied ? '1' : '0',
                $item->changed_in_snapshot ? '1' : '0'
            ));
        }

        return ExitCode::OK;
    }

    private function findOrCreateProvider(
        string $code,
        string $name,
        ?string $websiteUrl = null,
        bool $isOur = false,
        int $sortOrder = 0
    ): Provider {
        $provider = Provider::findOne(['code' => $code]);

        if ($provider !== null) {
            return $provider;
        }

        $provider = new Provider([
            'code' => $code,
            'name' => $name,
            'website_url' => $websiteUrl,
            'is_our' => $isOur,
            'is_active' => true,
            'sort_order' => $sortOrder,
        ]);

        if (!$provider->save()) {
            throw new \RuntimeException('Failed to create provider: ' . json_encode($provider->errors, JSON_UNESCAPED_UNICODE));
        }

        return $provider;
    }
}