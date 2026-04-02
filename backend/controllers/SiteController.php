<?php

namespace backend\controllers;

use common\models\LoginForm;
use common\services\snapshot\SnapshotReaderService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

final class SiteController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionLogin(): string|Response
    {
        if (!Yii::$app->user->isGuest) {
            echo "this";
            return $this->redirect(['site/index']);
        }

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            echo "this2";
            return $this->redirect(['site/index']);
        }

        $this->layout = 'blank';
        echo "this3";
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout(): Response
    {
        Yii::$app->user->logout();
        return $this->redirect(['site/login']);
    }

    public function actionIndex(string $tab = 'overview'): string
    {
        $allowedTabs = ['overview', 'comparison', 'internet', 'bundle', 'promotions', 'adddata'];

        if (!in_array($tab, $allowedTabs, true)) {
            $tab = 'overview';
        }

        if ($tab === 'adddata' && !Yii::$app->user->can('editCompetitors')) {
            throw new ForbiddenHttpException('Доступ запрещён.');
        }

        $reader = Yii::createObject(SnapshotReaderService::class);

        $latestInternetSnapshot = $reader->findLatestSnapshotByCategoryCode('internet');
        $internetRows = $latestInternetSnapshot !== null
            ? $reader->getSnapshotItems((int)$latestInternetSnapshot->id)
            : [];

        return $this->render('index', [
            'activeTab' => $tab,
            'latestInternetSnapshot' => $latestInternetSnapshot,
            'internetRows' => $internetRows,
        ]);
    }
}