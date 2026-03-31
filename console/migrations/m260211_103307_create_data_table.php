<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%data}}`.
 * FK provider_id to provider.id, user_id to user.id.
 */
class m260211_103307_create_data_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%data}}', [
            'id' => $this->primaryKey()->unsigned(),
            'provider_id' => $this->integer()->unsigned()->notNull(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'title' => $this->string(255)->notNull(), // Changed TEXT to VARCHAR(255)
            'content' => $this->text()->notNull(), // LONGTEXT to text() for Yii
        ]);

        // FKs
        $this->addForeignKey(
            'fk_data_provider_id',
            '{{%data}}',
            'provider_id',
            '{{%provider}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_data_user_id',
            '{{%data}}',
            'user_id',
            '{{%user}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        // Indexes
        $this->createIndex('idx_data_provider_id', '{{%data}}', 'provider_id');
        $this->createIndex('idx_data_user_id', '{{%data}}', 'user_id');
        $this->createIndex('idx_data_title', '{{%data}}', 'title');
        $this->createIndex('idx_data_created_at', '{{%data}}', 'created_at');
    }

    public function safeDown()
    {
        $this->dropTable('{{%data}}');
    }
}