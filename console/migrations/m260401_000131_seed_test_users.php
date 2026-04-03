<?php

use \Yii\db\Migration;
use \Yii\db\Query;

final class m260401_000131_seed_test_users extends Migration
{
    private const PASSWORD = '123456';

    private array $users = [
        [
            'username' => 'admin',
            'email' => 'admin@managers.local',
            'role' => 'admin',
        ],
        [
            'username' => 'manager',
            'email' => 'manager@managers.local',
            'role' => 'manager',
        ],
        [
            'username' => 'editor',
            'email' => 'editor@managers.local',
            'role' => 'editor',
        ],
    ];

    public function safeUp(): void
    {
        $security = \Yii::$app->security;
        $now = date('Y-m-d H:i:s');
        $timestamp = time();

        foreach ($this->users as $userData) {
            $existingUser = (new Query())
                ->from('{{%user}}')
                ->select(['id'])
                ->where(['username' => $userData['username']])
                ->one();

            $payload = [
                'email' => $userData['email'],
                'password_hash' => $security->generatePasswordHash(self::PASSWORD),
                'auth_key' => $security->generateRandomString(),
                'status' => 10,
                'updated_at' => $now,
                'updated_by' => null,
            ];

            if ($existingUser !== false && isset($existingUser['id'])) {
                $userId = (int)$existingUser['id'];

                $this->update('{{%user}}', $payload, ['id' => $userId]);
            } else {
                $this->insert('{{%user}}', array_merge($payload, [
                    'username' => $userData['username'],
                    'created_at' => $now,
                    'created_by' => null,
                ]));

                $userId = (int)$this->db->getLastInsertID();
            }

            $this->delete('{{%auth_assignment}}', [
                'user_id' => (string)$userId,
            ]);

            $this->insert('{{%auth_assignment}}', [
                'item_name' => $userData['role'],
                'user_id' => (string)$userId,
                'created_at' => $timestamp,
            ]);

            echo sprintf(
                "Seeded user: %s / role: %s / password: %s\n",
                $userData['username'],
                $userData['role'],
                self::PASSWORD
            );
        }
    }

    public function safeDown(): void
    {
        foreach (['admin', 'manager', 'editor'] as $username) {
            $user = (new Query())
                ->from('{{%user}}')
                ->select(['id'])
                ->where(['username' => $username])
                ->one();

            if ($user !== false && isset($user['id'])) {
                $this->delete('{{%auth_assignment}}', [
                    'user_id' => (string)$user['id'],
                ]);

                $this->delete('{{%user}}', [
                    'id' => $user['id'],
                ]);
            }
        }
    }
}