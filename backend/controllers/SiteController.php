<?php

namespace backend\controllers;

use common\models\LoginForm;
use common\services\snapshot\SnapshotReaderService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
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
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions(): array
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
        ];
    }

    public function actionLogin(): string|Response
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['site/index']);
        }

        $this->layout = 'blank';

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(['site/index']);
        }

        $model->password = '';

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
        $allowedTabs = [
            'overview',
            'comparison',
            'internet',
            'bundle',
            'promotions',
            'adddata',
        ];

        if (!in_array($tab, $allowedTabs, true)) {
            $tab = 'overview';
        }

        if ($tab === 'adddata' && !Yii::$app->user->can('editCompetitors')) {
            throw new ForbiddenHttpException('Доступ запрещён.');
        }

        /** @var SnapshotReaderService $reader */
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