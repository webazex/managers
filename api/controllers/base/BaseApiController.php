<?php

namespace api\controllers\base;

use Yii;
use yii\filters\ContentNegotiator;
use yii\rest\Controller;
use yii\web\Response;

abstract class BaseApiController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ]);
    }

    protected function success(array $data = [], int $statusCode = 200): array
    {
        Yii::$app->response->statusCode = $statusCode;

        return [
            'success' => true,
            'data' => $data,
        ];
    }

    protected function error(string $message, array $errors = [], int $statusCode = 400): array
    {
        Yii::$app->response->statusCode = $statusCode;

        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ];
    }
}