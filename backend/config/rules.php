<?php 
    return [
    '' => 'site/index',
    'login' => 'site/login',
    '<action:\w+>' => 'site/<action>',
    '<controller:\w+>' => '<controller>/index',
    '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
    '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
    '<controller:\w+>/<id:\d+>' => '<controller>/view',
    ];
?>