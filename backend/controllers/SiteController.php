<?php

namespace backend\controllers;

use common\models\LoginForm;
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
                        'actions' => ['logout', 'dashboard'],
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
            return $this->redirect(['/site/dashboard']);
        }

        $this->layout = 'blank';

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(['/site/dashboard']);
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionDashboard(string $tab = 'overview'): string
    {
        $allowedTabs = ['overview', 'comparison', 'internet', 'bundle', 'promotions', 'adddata'];

        if (!in_array($tab, $allowedTabs, true)) {
            $tab = 'overview';
        }

        if ($tab === 'adddata' && !Yii::$app->user->can('editCompetitors')) {
            throw new ForbiddenHttpException('Доступ запрещён.');
        }

        return $this->render('dashboard', [
            'activeTab' => $tab,
            'currentUserName' => $this->resolveCurrentUserName(),
            'currentUserRole' => $this->resolveCurrentUserRoleLabel(),
            'availableTabs' => $this->resolveAvailableTabs(),
        ]);
    }

    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->redirect(['/site/login']);
    }

    private function resolveCurrentUserName(): string
    {
        $identity = Yii::$app->user->identity;

        if ($identity === null) {
            return 'Гость';
        }

        return $identity->username
            ?? $identity->email
            ?? ('Пользователь #' . Yii::$app->user->id);
    }

    private function resolveCurrentUserRoleLabel(): string
    {
        $roles = Yii::$app->authManager->getRolesByUser((int)Yii::$app->user->id);
        $roleNames = array_keys($roles);

        if (in_array('admin', $roleNames, true)) {
            return 'Администратор';
        }

        if (in_array('editor', $roleNames, true)) {
            return 'Редактор';
        }

        if (in_array('manager', $roleNames, true)) {
            return 'Менеджер';
        }

        return 'Без роли';
    }

    private function resolveAvailableTabs(): array
    {
        $tabs = [
            'overview' => 'Профиль',
        ];

        if (Yii::$app->user->can('viewAnalytics')) {
            $tabs['comparison'] = 'Сравнение';
            $tabs['internet'] = 'Интернет';
            $tabs['bundle'] = 'Интернет + ТВ';
            $tabs['promotions'] = 'Акции';
        }

        if (Yii::$app->user->can('editCompetitors')) {
            $tabs['adddata'] = 'Редактор';
        }

        return $tabs;
    }
}