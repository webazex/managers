<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%provider_name}}`.
 */
class m260211_103304_create_provider_name_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%provider_name}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255)->notNull(), // Changed TEXT to VARCHAR(255) for efficiency
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);

        // Indexes
        $this->createIndex('idx_provider_name_name', '{{%provider_name}}', 'name', true); // Unique for names
        $this->createIndex('idx_provider_name_created_at', '{{%provider_name}}', 'created_at');
    }

    public function safeDown()
    {
        $this->dropTable('{{%provider_name}}');
    }
}