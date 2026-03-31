<?php

namespace common\models;
use common\traits\AuditableTrait;
use yii\db\ActiveRecord;
class Provider extends ActiveRecord
{
    use AuditableTrait;
    public bool $auditEnabled = true;
    public string $auditSnapshotType = 'snapshot';
}