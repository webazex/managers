<?php

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);
$localRulesPath = __DIR__.DIRECTORY_SEPARATOR.'rules-local.php';
$prodlRulesPath = __DIR__.DIRECTORY_SEPARATOR.'rules.php';
$localRules = (file_exists($localRulesPath))? include  $localRulesPath : [];
$prodRules = (file_exists($prodlRulesPath))? include $prodlRulesPath : [];
$rules = array_merge($prodRules, $localRules);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
            'baseUrl' => '',
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
            'rules' => $rules,
        ],
    ],
    'params' => $params,
];
