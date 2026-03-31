<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%reset}}`.
 * Adapted to work with standard Yii2 user (password_reset_token is in user, but keeping separate reset table for potential multiple tokens/history).
 * FK corrected to user.id (was wrongly to provider.user_id in export).
 */
class m260211_103303_create_reset_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%reset}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'token' => $this->text()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        // FK and index
        $this->addForeignKey(
            'fk_reset_user_id',
            '{{%reset}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('idx_reset_user_id', '{{%reset}}', 'user_id');
        $this->createIndex('idx_reset_token', '{{%reset}}', 'token(255)'); // Truncated for index if needed
    }

    public function safeDown()
    {
        $this->dropTable('{{%reset}}');
    }
}