<?php

namespace backend\controllers\base;

use common\base\web\BaseController;
use Yii;
use yii\filters\AccessControl;

abstract class BaseBackendController extends BaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $permission = $this->permissionMap()[$action->id] ?? null;

        if ($permission !== null && !Yii::$app->user->can($permission)) {
            $this->deny();
        }

        return true;
    }

    protected function permissionMap(): array
    {
        return [];
    }
}