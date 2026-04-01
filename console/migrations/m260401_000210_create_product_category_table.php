<?php

use yii\db\Migration;

final class m260401_000210_create_product_category_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%product_category}}', [
            'id' => $this->primaryKey()->unsigned(),
            'code' => $this->string(64)->notNull(),
            'name' => $this->string(255)->notNull(),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'created_by' => $this->integer()->unsigned()->null(),
            'updated_by' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createIndex('uq_product_category_code', '{{%product_category}}', 'code', true);
        $this->createIndex('idx_product_category_active_sort', '{{%product_category}}', ['is_active', 'sort_order', 'id']);
        $this->createIndex('idx_product_category_created_by', '{{%product_category}}', 'created_by');
        $this->createIndex('idx_product_category_updated_by', '{{%product_category}}', 'updated_by');

        $this->addForeignKey(
            'fk_product_category_created_by_user',
            '{{%product_category}}',
            'created_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_product_category_updated_by_user',
            '{{%product_category}}',
            'updated_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%product_category}}');
    }
}