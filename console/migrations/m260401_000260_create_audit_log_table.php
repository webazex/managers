<?php

use yii\db\Migration;

final class m260401_000260_create_audit_log_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%audit_log}}', [
            'id' => $this->primaryKey()->unsigned(),
            'actor_user_id' => $this->integer()->unsigned()->null(),
            'entity_type' => $this->string(64)->notNull(),
            'entity_id' => $this->bigInteger()->unsigned()->null(),
            'action' => $this->string(32)->notNull(),
            'before_json' => $this->text()->null(),
            'after_json' => $this->text()->null(),
            'diff_json' => $this->text()->null(),
            'context_json' => $this->text()->null(),
            'request_id' => $this->string(64)->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex('idx_audit_log_actor_date', '{{%audit_log}}', ['actor_user_id', 'created_at']);
        $this->createIndex('idx_audit_log_entity', '{{%audit_log}}', ['entity_type', 'entity_id', 'created_at']);
        $this->createIndex('idx_audit_log_action_date', '{{%audit_log}}', ['action', 'created_at']);
        $this->createIndex('idx_audit_log_request_id', '{{%audit_log}}', 'request_id');

        $this->addForeignKey(
            'fk_audit_log_actor_user',
            '{{%audit_log}}',
            'actor_user_id',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%audit_log}}');
    }
}