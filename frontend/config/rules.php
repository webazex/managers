<?php
return [
    // Главная
    '' => 'site/index',
    // Всё, что не попало в другие правила — пробуем как site/<action>
    '<action:\w+>' => 'site/<action>',

    // Более конкретные правила выше (чтобы не перехватывались)
    '<controller:\w+>/<id:\d+>'             => '<controller>/view',
    '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
    '<controller:\w+>/<action:\w+>'         => '<controller>/<action>',
    '<controller:\w+>'                      => '<controller>/index',
];