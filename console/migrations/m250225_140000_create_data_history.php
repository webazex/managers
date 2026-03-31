<?php

use yii\db\Migration;

/**
 * Class m250225_140000_create_data_history
 */
class m250225_140000_create_data_history extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%data_history}}', [
            'id'            => $this->bigPrimaryKey(),
            'create_at'     => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),

            'actor_type'    => "enum('user','parser','system','guest') NOT NULL DEFAULT 'system'",
            'actor_id'      => $this->bigInteger()->unsigned()->null(),
            'actor_label'   => $this->string(255)->null(),

            'action'        => "enum('insert','update','delete','restore') NOT NULL",
            'snapshot_type' => "enum('snapshot','diff') NOT NULL DEFAULT 'snapshot'",

            'table_name'    => $this->string(128)->notNull(),
            'row_id'        => $this->bigInteger()->unsigned()->notNull(),

            'old_data'      => $this->longText()->notNull(),     // JSON со снимком ДО изменения
            'new_data'      => $this->longText()->null(),        // JSON со снимком ПОСЛЕ (опционально, для update)

            'ip'            => $this->string(45)->null(),
            'user_agent'    => $this->string(255)->null(),
            'request_id'    => $this->string(64)->null(),        // для корреляции в логах

            'INDEX idx_table_row (table_name, row_id)',
            'INDEX idx_actor (actor_type, actor_id)',
            'INDEX idx_action_create (action, create_at)',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function safeDown()
    {
        $this->dropTable('{{%data_history}}');
    }
}