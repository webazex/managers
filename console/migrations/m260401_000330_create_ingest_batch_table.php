<?php

use yii\db\Migration;

final class m260401_000330_create_ingest_batch_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%ingest_batch}}', [
            'id' => $this->primaryKey()->unsigned(),
            'source_code' => $this->string(64)->notNull(),
            'payload_hash' => $this->string(64)->notNull(),
            'payload_json' => $this->text()->null(),
            'status' => $this->string(32)->notNull()->defaultValue('received'),
            'received_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'processed_at' => $this->dateTime()->null(),
            'error_message' => $this->text()->null(),
        ], $tableOptions);

        $this->createIndex('uq_ingest_batch_payload_hash', '{{%ingest_batch}}', 'payload_hash', true);
        $this->createIndex('idx_ingest_batch_source_status', '{{%ingest_batch}}', ['source_code', 'status']);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%ingest_batch}}');
    }
}