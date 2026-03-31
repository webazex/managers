<?php
// m241003_123500_create_provider_service_link.php
use yii\db\Migration;

class m241003_123500_create_provider_service_link extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%provider_service_link}}', [
            'provider_id' => $this->integer()->unsigned()->notNull(),
            'service_id'  => $this->integer()->unsigned()->notNull(),
            'created_at'  => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'PRIMARY KEY(provider_id, service_id)',
        ]);

        $this->addForeignKey(
            'fk_provider_service_link_provider',
            '{{%provider_service_link}}', 'provider_id',
            '{{%provider}}', 'id',
            'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'fk_provider_service_link_service',
            '{{%provider_service_link}}', 'service_id',
            '{{%provider_service}}', 'id',
            'RESTRICT', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_provider_service_link_provider', '{{%provider_service_link}}');
        $this->dropForeignKey('fk_provider_service_link_service', '{{%provider_service_link}}');
        $this->dropTable('{{%provider_service_link}}');
    }
}
?>