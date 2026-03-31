<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%property}}`.
 */
class m260211_103308_create_property_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%property}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255)->notNull(), // TEXT to VARCHAR(255)
            'val' => $this->string(255)->notNull(), // TEXT to VARCHAR(255), adjust if needed
            'data_id' => $this->integer()->unsigned()->notNull(),
        ]);

        // FK
        $this->addForeignKey(
            'fk_property_data_id',
            '{{%property}}',
            'data_id',
            '{{%data}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Indexes
        $this->createIndex('idx_property_data_id', '{{%property}}', 'data_id');
        $this->createIndex('idx_property_name', '{{%property}}', 'name');
    }

    public function safeDown()
    {
        $this->dropTable('{{%property}}');
    }
}