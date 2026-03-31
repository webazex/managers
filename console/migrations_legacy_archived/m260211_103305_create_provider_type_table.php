<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%provider_type}}`.
 */
class m260211_103305_create_provider_type_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%provider_type}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255)->notNull(), // Changed TEXT to VARCHAR(255)
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);

        // Indexes
        $this->createIndex('idx_provider_type_name', '{{%provider_type}}', 'name', true);
        $this->createIndex('idx_provider_type_created_at', '{{%provider_type}}', 'created_at');
    }

    public function safeDown()
    {
        $this->dropTable('{{%provider_type}}');
    }
}