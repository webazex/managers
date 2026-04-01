<?php

use yii\db\Migration;

final class m260401_000320_create_domain_snapshot_item_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%domain_snapshot_item}}', [
            'id' => $this->primaryKey()->unsigned(),
            'snapshot_id' => $this->integer()->unsigned()->notNull(),
            'provider_id' => $this->integer()->unsigned()->notNull(),
            'domain_zone_id' => $this->integer()->unsigned()->notNull(),
            'operation_type' => $this->string(16)->notNull(),
            'price' => $this->decimal(10, 2)->notNull(),
            'currency' => $this->char(3)->notNull()->defaultValue('UAH'),
            'cost_price' => $this->decimal(10, 2)->null(),
            'markup_percent' => $this->decimal(8, 2)->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex(
            'uq_domain_snapshot_item_unique',
            '{{%domain_snapshot_item}}',
            ['snapshot_id', 'provider_id', 'domain_zone_id', 'operation_type'],
            true
        );

        $this->createIndex('idx_domain_snapshot_item_provider', '{{%domain_snapshot_item}}', ['provider_id', 'domain_zone_id']);
        $this->createIndex('idx_domain_snapshot_item_zone', '{{%domain_snapshot_item}}', ['domain_zone_id', 'operation_type']);

        $this->addForeignKey(
            'fk_domain_snapshot_item_snapshot',
            '{{%domain_snapshot_item}}',
            'snapshot_id',
            '{{%domain_snapshot}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_domain_snapshot_item_provider',
            '{{%domain_snapshot_item}}',
            'provider_id',
            '{{%provider}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_domain_snapshot_item_zone',
            '{{%domain_snapshot_item}}',
            'domain_zone_id',
            '{{%domain_zone}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%domain_snapshot_item}}');
    }
}