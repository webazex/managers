<?php

use yii\db\Migration;

final class m260401_000220_create_product_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%product}}', [
            'id' => $this->primaryKey()->unsigned(),
            'category_id' => $this->integer()->unsigned()->notNull(),
            'code' => $this->string(64)->notNull(),
            'name' => $this->string(255)->notNull(),
            'speed_mbps' => $this->integer()->null(),
            'tv_included' => $this->boolean()->notNull()->defaultValue(false),
            'domain_zone' => $this->string(32)->null(),
            'unit' => $this->string(32)->null(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'created_by' => $this->integer()->unsigned()->null(),
            'updated_by' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createIndex('uq_product_category_code', '{{%product}}', ['category_id', 'code'], true);
        $this->createIndex('idx_product_category_sort', '{{%product}}', ['category_id', 'sort_order', 'id']);
        $this->createIndex('idx_product_category_speed_tv', '{{%product}}', ['category_id', 'speed_mbps', 'tv_included']);
        $this->createIndex('idx_product_active', '{{%product}}', 'is_active');
        $this->createIndex('idx_product_created_by', '{{%product}}', 'created_by');
        $this->createIndex('idx_product_updated_by', '{{%product}}', 'updated_by');

        $this->addForeignKey(
            'fk_product_category',
            '{{%product}}',
            'category_id',
            '{{%product_category}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_product_created_by_user',
            '{{%product}}',
            'created_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_product_updated_by_user',
            '{{%product}}',
            'updated_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%product}}');
    }
}