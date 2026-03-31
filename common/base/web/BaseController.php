<?php

namespace common\base\web;

use yii\web\Controller;
use yii\web\ForbiddenHttpException;

abstract class BaseController extends Controller
{
    protected function deny(string $message = 'Access denied'): never
    {
        throw new ForbiddenHttpException($message);
    }
}