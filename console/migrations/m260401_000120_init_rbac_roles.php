<?php

use yii\db\Migration;

final class m260401_000120_init_rbac_roles extends Migration
{
    private const TYPE_ROLE = 1;
    private const TYPE_PERMISSION = 2;

    public function safeUp(): void
    {
        $time = time();

        $this->batchInsert(
            '{{%auth_item}}',
            ['name', 'type', 'description', 'rule_name', 'data', 'created_at', 'updated_at'],
            [
                ['viewAnalytics', self::TYPE_PERMISSION, 'Просмотр аналитики, графиков и таблиц', null, null, $time, $time],
                ['editCompetitors', self::TYPE_PERMISSION, 'Редактирование конкурентов и данных рынка', null, null, $time, $time],
                ['manageUsers', self::TYPE_PERMISSION, 'Управление пользователями и ролями', null, null, $time, $time],

                ['manager', self::TYPE_ROLE, 'Менеджер', null, null, $time, $time],
                ['editor', self::TYPE_ROLE, 'Редактор', null, null, $time, $time],
                ['admin', self::TYPE_ROLE, 'Администратор', null, null, $time, $time],
            ]
        );

        $this->batchInsert(
            '{{%auth_item_child}}',
            ['parent', 'child'],
            [
                ['manager', 'viewAnalytics'],

                ['editor', 'viewAnalytics'],
                ['editor', 'editCompetitors'],

                ['admin', 'viewAnalytics'],
                ['admin', 'editCompetitors'],
                ['admin', 'manageUsers'],
            ]
        );
    }

    public function safeDown(): void
    {
        $this->delete('{{%auth_item_child}}', ['parent' => ['manager', 'editor', 'admin']]);
        $this->delete('{{%auth_item}}', [
            'name' => [
                'viewAnalytics',
                'editCompetitors',
                'manageUsers',
                'manager',
                'editor',
                'admin',
            ],
        ]);
    }
}