<?php

use yii\db\Migration;

final class m260401_000310_create_domain_snapshot_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%domain_snapshot}}', [
            'id' => $this->primaryKey()->unsigned(),
            'snapshot_date' => $this->date()->notNull(),
            'revision' => $this->smallInteger()->unsigned()->notNull()->defaultValue(1),
            'source_type' => $this->string(32)->notNull()->defaultValue('parser'),
            'ingest_batch_id' => $this->integer()->unsigned()->null(),
            'received_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createIndex('uq_domain_snapshot_date_revision', '{{%domain_snapshot}}', ['snapshot_date', 'revision'], true);
        $this->createIndex('idx_domain_snapshot_ingest_batch', '{{%domain_snapshot}}', 'ingest_batch_id');
        $this->createIndex('idx_domain_snapshot_created_by', '{{%domain_snapshot}}', 'created_by');

        $this->addForeignKey(
            'fk_domain_snapshot_created_by_user',
            '{{%domain_snapshot}}',
            'created_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%domain_snapshot}}');
    }
}