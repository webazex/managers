<?php
// m241003_123456_create_provider_service.php
use yii\db\Migration;

class m241003_123456_create_provider_service extends Migration
{
public function safeUp()
{
$this->createTable('{{%provider_service}}', [
'id'         => $this->primaryKey()->unsigned(),
'code'       => $this->string(32)->notNull()->unique(),
'name'       => $this->string(100)->notNull(),
'sort_order' => $this->integer()->defaultValue(0),
'created_at' => $this->dateTime()->notNull(),
'updated_at' => $this->dateTime()->notNull(),
]);

$this->createIndex('idx_provider_service_code', '{{%provider_service}}', 'code');

// Начальные данные
$this->batchInsert('{{%provider_service}}', ['code', 'name', 'sort_order', 'created_at', 'updated_at'], [
['internet', 'Інтернет',          10, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')],
['tv',       'Телебачення',       20, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')],
['domains',  'Домени / Хостинг',  30, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')],
]);
}

public function safeDown()
{
$this->dropTable('{{%provider_service}}');
}
}