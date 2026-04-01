<?php

namespace console\controllers;

use common\models\catalog\Provider;
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
}