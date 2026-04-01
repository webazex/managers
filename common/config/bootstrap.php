<?php

Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('@api', dirname(dirname(__DIR__)) . '/api');

Yii::$container->set(\common\services\audit\AuditLogWriterService::class);
Yii::$container->set(\common\services\snapshot\SnapshotReaderService::class);
Yii::$container->set(\common\services\snapshot\SnapshotWriterService::class, function () {
    return new \common\services\snapshot\SnapshotWriterService(
        Yii::createObject(\common\services\audit\AuditLogWriterService::class)
    );
});