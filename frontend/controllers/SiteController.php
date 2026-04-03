<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;

final class SiteController extends Controller
{
    public function actions(): array
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
        ];
    }

    public function actionIndex(): Response
    {
        return $this->redirect('/dashboard');
    }

    public function actionLogin(): Response
    {
        return $this->redirect('/login');
    }

    public function actionLogout(): Response
    {
        return $this->redirect('/logout');
    }

    public function actionAbout(): Response
    {
        return $this->redirect('/dashboard');
    }

    public function actionContact(): Response
    {
        return $this->redirect('/dashboard');
    }

    public function actionSignup(): Response
    {
        return $this->redirect('/login');
    }

    public function actionRequestPasswordReset(): Response
    {
        return $this->redirect('/login');
    }

    public function actionResetPassword($token): Response
    {
        return $this->redirect('/login');
    }

    public function actionVerifyEmail($token): Response
    {
        return $this->redirect('/login');
    }

    public function actionResendVerificationEmail(): Response
    {
        return $this->redirect('/login');
    }
}