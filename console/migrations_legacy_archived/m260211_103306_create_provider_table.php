<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%provider}}`.
 * FK user_id corrected to user.id (was wrongly to data.provider_id in export).
 * type_id default changed to integer value (assuming 1 for 'default').
 */
class m260211_103306_create_provider_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%provider}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(), // Last editor or creator
            'title' => $this->string(255)->notNull(), // Changed TEXT to VARCHAR(255)
            'type_id' => $this->integer()->unsigned()->notNull()->defaultValue(1), // Assume ID for 'default' type
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'provider_name_id' => $this->integer()->unsigned()->notNull(),
        ]);

        // FKs
        $this->addForeignKey(
            'fk_provider_user_id',
            '{{%provider}}',
            'user_id',
            '{{%user}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_provider_type_id',
            '{{%provider}}',
            'type_id',
            '{{%provider_type}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_provider_provider_name_id',
            '{{%provider}}',
            'provider_name_id',
            '{{%provider_name}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Indexes
        $this->createIndex('idx_provider_user_id', '{{%provider}}', 'user_id');
        $this->createIndex('idx_provider_type_id', '{{%provider}}', 'type_id');
        $this->createIndex('idx_provider_provider_name_id', '{{%provider}}', 'provider_name_id');
        $this->createIndex('idx_provider_title', '{{%provider}}', 'title');
        $this->createIndex('idx_provider_created_at', '{{%provider}}', 'created_at');
    }

    public function safeDown()
    {
        $this->dropTable('{{%provider}}');
    }
}