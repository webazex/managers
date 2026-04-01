<?php

use yii\db\Migration;

final class m260401_000230_create_market_snapshot_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%market_snapshot}}', [
            'id' => $this->primaryKey()->unsigned(),
            'category_id' => $this->integer()->unsigned()->notNull(),
            'snapshot_date' => $this->date()->notNull(),
            'revision' => $this->smallInteger()->unsigned()->notNull()->defaultValue(1),
            'status' => $this->string(32)->notNull()->defaultValue('published'),
            'source_type' => $this->string(32)->notNull()->defaultValue('manual'),
            'trigger_provider_id' => $this->integer()->unsigned()->null(),
            'based_on_snapshot_id' => $this->integer()->unsigned()->null(),
            'comment' => $this->string(255)->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createIndex(
            'uq_market_snapshot_category_date_revision',
            '{{%market_snapshot}}',
            ['category_id', 'snapshot_date', 'revision'],
            true
        );

        $this->createIndex(
            'idx_market_snapshot_category_date',
            '{{%market_snapshot}}',
            ['category_id', 'snapshot_date', 'revision']
        );

        $this->createIndex('idx_market_snapshot_created_by', '{{%market_snapshot}}', ['created_by', 'created_at']);
        $this->createIndex('idx_market_snapshot_trigger_provider', '{{%market_snapshot}}', ['trigger_provider_id', 'created_at']);
        $this->createIndex('idx_market_snapshot_based_on', '{{%market_snapshot}}', 'based_on_snapshot_id');
        $this->createIndex('idx_market_snapshot_status', '{{%market_snapshot}}', 'status');

        $this->addForeignKey(
            'fk_market_snapshot_category',
            '{{%market_snapshot}}',
            'category_id',
            '{{%product_category}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_market_snapshot_trigger_provider',
            '{{%market_snapshot}}',
            'trigger_provider_id',
            '{{%provider}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_market_snapshot_based_on',
            '{{%market_snapshot}}',
            'based_on_snapshot_id',
            '{{%market_snapshot}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_market_snapshot_created_by_user',
            '{{%market_snapshot}}',
            'created_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%market_snapshot}}');
    }
}