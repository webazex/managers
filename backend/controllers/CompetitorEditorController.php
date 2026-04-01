<?php

namespace backend\controllers;

use backend\controllers\base\BaseBackendController;
use backend\models\CompetitorEditorForm;
use common\services\snapshot\SnapshotReaderService;
use common\services\snapshot\SnapshotWriterService;
use Yii;
use yii\web\Response;

final class CompetitorEditorController extends BaseBackendController
{
    protected function permissionMap(): array
    {
        return [
            'index' => 'editCompetitors',
        ];
    }

    public function actionIndex(): string|Response
    {
        $request = Yii::$app->request;
        $reader = Yii::createObject(SnapshotReaderService::class);
        $writer = Yii::createObject(SnapshotWriterService::class);

        $form = new CompetitorEditorForm();

        if ($request->isGet) {
            $form->provider_id = $request->get('provider_id') !== null
                ? (int)$request->get('provider_id')
                : null;

            if ($form->provider_id !== null) {
                $form->loadFromLatestSnapshot($reader);
            }
        }

        if ($form->load($request->post()) && $form->validate()) {
            $snapshot = $writer->createProviderSnapshotRevision(
                categoryCode: $form->category_code,
                providerId: (int)$form->provider_id,
                pricesByProductCode: $form->getNormalizedPrices(),
                notes: $form->getNormalizedNotes(),
                comment: $form->comment
            );

            Yii::$app->session->setFlash(
                'success',
                "Снимок рынка сохранён. ID: {$snapshot->id}, дата: {$snapshot->snapshot_date}, ревизия: {$snapshot->revision}."
            );

            return $this->redirect([
                'index',
                'provider_id' => $form->provider_id,
            ]);
        }

        return $this->render('index', [
            'model' => $form,
            'providerOptions' => CompetitorEditorForm::getProviderOptions(),
        ]);
    }
}