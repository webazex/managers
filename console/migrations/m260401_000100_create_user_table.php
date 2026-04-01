<?php

use yii\db\Migration;

final class m260401_000100_create_user_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey()->unsigned(),
            'username' => $this->string(255)->notNull(),
            'email' => $this->string(255)->notNull(),
            'password_hash' => $this->string(255)->notNull(),
            'auth_key' => $this->string(64)->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'created_by' => $this->integer()->unsigned()->null(),
            'updated_by' => $this->integer()->unsigned()->null(),
        ], $tableOptions);

        $this->createIndex('uq_user_username', '{{%user}}', 'username', true);
        $this->createIndex('uq_user_email', '{{%user}}', 'email', true);
        $this->createIndex('idx_user_status', '{{%user}}', 'status');
        $this->createIndex('idx_user_created_by', '{{%user}}', 'created_by');
        $this->createIndex('idx_user_updated_by', '{{%user}}', 'updated_by');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%user}}');
    }
}