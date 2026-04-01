<?php

use yii\db\Migration;

final class m260401_000200_create_provider_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%provider}}', [
            'id' => $this->primaryKey()->unsigned(),
            'code' => $this->string(64)->notNull(),
            'name' => $this->string(255)->notNull(),
            'website_url' => $this->string(512)->null(),
            'is_our' => $this->boolean()->notNull()->defaultValue(false),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'created_by' => $this->integer()->unsigned()->null(),
            'updated_by' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createIndex('uq_provider_code', '{{%provider}}', 'code', true);
        $this->createIndex('idx_provider_active_sort', '{{%provider}}', ['is_active', 'sort_order', 'id']);
        $this->createIndex('idx_provider_is_our', '{{%provider}}', 'is_our');
        $this->createIndex('idx_provider_created_by', '{{%provider}}', 'created_by');
        $this->createIndex('idx_provider_updated_by', '{{%provider}}', 'updated_by');

        $this->addForeignKey(
            'fk_provider_created_by_user',
            '{{%provider}}',
            'created_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_provider_updated_by_user',
            '{{%provider}}',
            'updated_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%provider}}');
    }
}