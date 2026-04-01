<?php

use yii\db\Migration;

final class m260401_000250_create_provider_snapshot_note_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%provider_snapshot_note}}', [
            'id' => $this->primaryKey()->unsigned(),
            'snapshot_id' => $this->integer()->unsigned()->notNull(),
            'snapshot_date' => $this->date()->notNull(),
            'provider_id' => $this->integer()->unsigned()->notNull(),
            'promotion_text' => $this->text()->null(),
            'loyalty_text' => $this->text()->null(),
            'editor_note' => $this->text()->null(),
            'source_type' => $this->string(32)->notNull()->defaultValue('carry_forward'),
            'is_copied' => $this->boolean()->notNull()->defaultValue(true),
            'changed_in_snapshot' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createIndex(
            'uq_provider_snapshot_note_unique',
            '{{%provider_snapshot_note}}',
            ['snapshot_id', 'provider_id'],
            true
        );

        $this->createIndex('idx_provider_snapshot_note_provider_date', '{{%provider_snapshot_note}}', ['provider_id', 'snapshot_date']);
        $this->createIndex('idx_provider_snapshot_note_changed', '{{%provider_snapshot_note}}', ['snapshot_id', 'changed_in_snapshot']);
        $this->createIndex('idx_provider_snapshot_note_created_by', '{{%provider_snapshot_note}}', 'created_by');

        $this->addForeignKey(
            'fk_provider_snapshot_note_snapshot',
            '{{%provider_snapshot_note}}',
            'snapshot_id',
            '{{%market_snapshot}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_provider_snapshot_note_provider',
            '{{%provider_snapshot_note}}',
            'provider_id',
            '{{%provider}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_provider_snapshot_note_created_by_user',
            '{{%provider_snapshot_note}}',
            'created_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%provider_snapshot_note}}');
    }
}