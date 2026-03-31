<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%report}}`.
 */
class m260211_103309_create_report_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%report}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'title' => $this->string(255)->notNull(), // TEXT to VARCHAR(255)
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'data' => $this->text()->notNull(), // TEXT ok
        ]);

        // FK
        $this->addForeignKey(
            'fk_report_user_id',
            '{{%report}}',
            'user_id',
            '{{%user}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        // Indexes
        $this->createIndex('idx_report_user_id', '{{%report}}', 'user_id');
        $this->createIndex('idx_report_title', '{{%report}}', 'title');
        $this->createIndex('idx_report_created_at', '{{%report}}', 'created_at');
    }

    public function safeDown()
    {
        $this->dropTable('{{%report}}');
    }
}