<?php

use yii\db\Migration;

final class m260401_000240_create_market_snapshot_item_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%market_snapshot_item}}', [
            'id' => $this->primaryKey()->unsigned(),
            'snapshot_id' => $this->integer()->unsigned()->notNull(),
            'snapshot_date' => $this->date()->notNull(),
            'provider_id' => $this->integer()->unsigned()->notNull(),
            'product_id' => $this->integer()->unsigned()->notNull(),
            'price' => $this->decimal(10, 2)->null(),
            'currency' => $this->char(3)->notNull()->defaultValue('UAH'),
            'availability_status' => $this->string(32)->notNull()->defaultValue('available'),
            'source_type' => $this->string(32)->notNull()->defaultValue('carry_forward'),
            'is_copied' => $this->boolean()->notNull()->defaultValue(true),
            'changed_in_snapshot' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createIndex(
            'uq_market_snapshot_item_unique',
            '{{%market_snapshot_item}}',
            ['snapshot_id', 'provider_id', 'product_id'],
            true
        );

        $this->createIndex('idx_market_snapshot_item_snapshot_provider', '{{%market_snapshot_item}}', ['snapshot_id', 'provider_id']);
        $this->createIndex('idx_market_snapshot_item_snapshot_product', '{{%market_snapshot_item}}', ['snapshot_id', 'product_id']);
        $this->createIndex('idx_market_snapshot_item_provider_date', '{{%market_snapshot_item}}', ['provider_id', 'snapshot_date', 'product_id']);
        $this->createIndex('idx_market_snapshot_item_product_date', '{{%market_snapshot_item}}', ['product_id', 'snapshot_date', 'provider_id']);
        $this->createIndex('idx_market_snapshot_item_changed', '{{%market_snapshot_item}}', ['snapshot_id', 'changed_in_snapshot']);
        $this->createIndex('idx_market_snapshot_item_created_by', '{{%market_snapshot_item}}', 'created_by');

        $this->addForeignKey(
            'fk_market_snapshot_item_snapshot',
            '{{%market_snapshot_item}}',
            'snapshot_id',
            '{{%market_snapshot}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_market_snapshot_item_provider',
            '{{%market_snapshot_item}}',
            'provider_id',
            '{{%provider}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_market_snapshot_item_product',
            '{{%market_snapshot_item}}',
            'product_id',
            '{{%product}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_market_snapshot_item_created_by_user',
            '{{%market_snapshot_item}}',
            'created_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%market_snapshot_item}}');
    }
}