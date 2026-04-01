<?php

return [
    '' => 'site/index',
    'login' => 'site/login',
    'logout' => 'site/logout',

    'editor' => 'competitor-editor/index',
    'editor/<provider_id:\d+>' => 'competitor-editor/index',

    '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
];