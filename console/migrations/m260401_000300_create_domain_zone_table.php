<?php

use yii\db\Migration;

final class m260401_000300_create_domain_zone_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%domain_zone}}', [
            'id' => $this->primaryKey()->unsigned(),
            'zone' => $this->string(32)->notNull(),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex('uq_domain_zone_zone', '{{%domain_zone}}', 'zone', true);
        $this->createIndex('idx_domain_zone_active_sort', '{{%domain_zone}}', ['is_active', 'sort_order', 'id']);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%domain_zone}}');
    }
}