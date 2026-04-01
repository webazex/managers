<?php

use yii\db\Migration;
use yii\helpers\Json;

final class m260401_000130_seed_admin_user extends Migration
{
    public function safeUp(): void
    {
        $security = Yii::$app->security;

        $username = 'admin';
        $email = 'admin@managers.local';
        $password = 'ChangeMe123!';

        $this->insert('{{%user}}', [
            'username' => $username,
            'email' => $email,
            'password_hash' => $security->generatePasswordHash($password),
            'auth_key' => $security->generateRandomString(),
            'status' => 10,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'created_by' => null,
            'updated_by' => null,
        ]);

        $userId = (string) $this->db->getLastInsertID();

        $this->insert('{{%auth_assignment}}', [
            'item_name' => 'admin',
            'user_id' => $userId,
            'created_at' => time(),
        ]);

        echo PHP_EOL;
        echo 'Seed admin user created:' . PHP_EOL;
        echo '  login: ' . $username . PHP_EOL;
        echo '  email: ' . $email . PHP_EOL;
        echo '  password: ' . $password . PHP_EOL;
        echo '  IMPORTANT: change this password immediately after first login.' . PHP_EOL;
        echo PHP_EOL;
    }

    public function safeDown(): void
    {
        $user = (new \yii\db\Query())
            ->from('{{%user}}')
            ->select(['id'])
            ->where(['username' => 'admin'])
            ->one();

        if ($user !== false && isset($user['id'])) {
            $this->delete('{{%auth_assignment}}', [
                'item_name' => 'admin',
                'user_id' => (string) $user['id'],
            ]);

            $this->delete('{{%user}}', ['id' => $user['id']]);
        }
    }
}