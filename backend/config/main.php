<?php

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

$prodRules = require __DIR__ . '/rules.php';

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            'baseUrl' => '',
            'csrfParam' => '_csrf-backend',
        ],
        'user' => [
            'identityClass' => 'common\models\auth\User',
            'enableAutoLogin' => true,
            'identityCookie' => [
                'name' => '_identity-backend',
                'httpOnly' => true,
                'path' => '/',
            ],
            'loginUrl' => ['/site/login'],
        ],
        'session' => [
            'name' => 'advanced-backend',
            'cookieParams' => [
                'path' => '/',
                'httpOnly' => true,
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'baseUrl' => '',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => $prodRules,
        ],
        'authManager' => [
            'class' => \yii\rbac\DbManager::class,
        ],
    ],
    'params' => $params,
];