<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user}}`.
 * Adapted to standard Yii2 Advanced user table with RBAC compatibility.
 * Removed id_role as RBAC uses auth_assignment instead.
 * Added standard fields: username (from login), auth_key, password_hash (from password), status, password_reset_token.
 * Timestamps as integers (Unix timestamps) to match Yii2 default.
 */
class m260211_103301_create_user_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey()->unsigned(),
            'username' => $this->string(255)->notNull()->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string(255)->notNull(),
            'password_reset_token' => $this->string(255)->unique(),
            'email' => $this->string(255)->notNull()->unique(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Indexes for performance
        $this->createIndex('idx_user_username', '{{%user}}', 'username');
        $this->createIndex('idx_user_email', '{{%user}}', 'email');
        $this->createIndex('idx_user_status', '{{%user}}', 'status');
    }

    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}