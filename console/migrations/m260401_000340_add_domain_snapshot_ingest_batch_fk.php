<?php

use yii\db\Migration;

final class m260401_000340_add_domain_snapshot_ingest_batch_fk extends Migration
{
    public function safeUp(): void
    {
        $this->addForeignKey(
            'fk_domain_snapshot_ingest_batch',
            '{{%domain_snapshot}}',
            'ingest_batch_id',
            '{{%ingest_batch}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_domain_snapshot_ingest_batch', '{{%domain_snapshot}}');
    }
}