<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%data_history}}`.
 * For audit logs.
 */
class m260211_103310_create_data_history_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%data_history}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'old_data' => $this->text()->notNull(), // LONGTEXT to text()
            'create_at' => $this->dateTime()->notNull(), // Note: typo in export 'create_at' instead of 'created_at'
            'table_name' => $this->string(255)->notNull(), // TEXT to VARCHAR(255)
            'row_id' => $this->integer()->unsigned()->notNull(),
        ]);

        // FK
        $this->addForeignKey(
            'fk_data_history_user_id',
            '{{%data_history}}',
            'user_id',
            '{{%user}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        // Indexes
        $this->createIndex('idx_data_history_user_id', '{{%data_history}}', 'user_id');
        $this->createIndex('idx_data_history_table_name_row_id', '{{%data_history}}', ['table_name', 'row_id']);
        $this->createIndex('idx_data_history_create_at', '{{%data_history}}', 'create_at');
    }

    public function safeDown()
    {
        $this->dropTable('{{%data_history}}');
    }
}