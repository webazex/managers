<?php

use yii\db\Migration;

final class m260401_000000_reset_project_schema extends Migration
{
    private array $tables = [
        '{{%domain_snapshot_item}}',
        '{{%domain_snapshot}}',
        '{{%domain_zone}}',
        '{{%ingest_batch}}',

        '{{%provider_snapshot_note}}',
        '{{%market_snapshot_item}}',
        '{{%market_snapshot}}',
        '{{%product}}',
        '{{%product_category}}',
        '{{%audit_log}}',
        '{{%provider}}',

        '{{%provider_service_link}}',
        '{{%provider_service}}',
        '{{%data_history}}',
        '{{%report}}',
        '{{%property}}',
        '{{%data}}',
        '{{%provider_type}}',
        '{{%provider_name}}',
        '{{%reset}}',

        '{{%auth_assignment}}',
        '{{%auth_item_child}}',
        '{{%auth_item}}',
        '{{%auth_rule}}',
        '{{%user}}',
    ];

    public function safeUp(): void
    {
        $this->execute('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($this->tables as $table) {
            $this->dropTableIfExists($table);
        }

        $this->execute('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function safeDown(): bool
    {
        echo "m260401_000000_reset_project_schema cannot be reverted.\n";
        return false;
    }

    private function dropTableIfExists(string $table): void
    {
        $schema = $this->db->schema->getTableSchema($table, true);

        if ($schema !== null) {
            $this->dropTable($table);
        }
    }
}