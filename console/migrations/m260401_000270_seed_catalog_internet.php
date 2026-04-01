<?php

use yii\db\Migration;

final class m260401_000270_seed_catalog_internet extends Migration
{
    public function safeUp(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->batchInsert(
            '{{%product_category}}',
            ['code', 'name', 'is_active', 'sort_order', 'created_at', 'updated_at', 'created_by', 'updated_by'],
            [
                ['internet', 'Интернет', 1, 10, $now, $now, null, null],
                ['internet_tv', 'Интернет + ТВ', 1, 20, $now, $now, null, null],
                ['domains', 'Домены', 1, 30, $now, $now, null, null],
            ]
        );

        $categories = (new \yii\db\Query())
            ->from('{{%product_category}}')
            ->indexBy('code')
            ->all();

        $internetId = (int) $categories['internet']['id'];
        $bundleId = (int) $categories['internet_tv']['id'];

        $this->batchInsert(
            '{{%product}}',
            ['category_id', 'code', 'name', 'speed_mbps', 'tv_included', 'domain_zone', 'unit', 'sort_order', 'is_active', 'created_at', 'updated_at', 'created_by', 'updated_by'],
            [
                [$internetId, '50', '50 Мбит/с', 50, 0, null, 'mbps', 10, 1, $now, $now, null, null],
                [$internetId, '75', '75 Мбит/с', 75, 0, null, 'mbps', 20, 1, $now, $now, null, null],
                [$internetId, '100', '100 Мбит/с', 100, 0, null, 'mbps', 30, 1, $now, $now, null, null],
                [$internetId, '200', '200 Мбит/с', 200, 0, null, 'mbps', 40, 1, $now, $now, null, null],
                [$internetId, '250', '250 Мбит/с', 250, 0, null, 'mbps', 50, 1, $now, $now, null, null],
                [$internetId, '300', '300 Мбит/с', 300, 0, null, 'mbps', 60, 1, $now, $now, null, null],
                [$internetId, '500', '500 Мбит/с', 500, 0, null, 'mbps', 70, 1, $now, $now, null, null],
                [$internetId, '1000', '1 Гбит/с', 1000, 0, null, 'mbps', 80, 1, $now, $now, null, null],

                [$bundleId, '50+TV', '50 Мбит/с + ТВ', 50, 1, null, 'mbps', 10, 1, $now, $now, null, null],
                [$bundleId, '75+TV', '75 Мбит/с + ТВ', 75, 1, null, 'mbps', 20, 1, $now, $now, null, null],
                [$bundleId, '100+TV', '100 Мбит/с + ТВ', 100, 1, null, 'mbps', 30, 1, $now, $now, null, null],
                [$bundleId, '200+TV', '200 Мбит/с + ТВ', 200, 1, null, 'mbps', 40, 1, $now, $now, null, null],
                [$bundleId, '250+TV', '250 Мбит/с + ТВ', 250, 1, null, 'mbps', 50, 1, $now, $now, null, null],
                [$bundleId, '300+TV', '300 Мбит/с + ТВ', 300, 1, null, 'mbps', 60, 1, $now, $now, null, null],
                [$bundleId, '500+TV', '500 Мбит/с + ТВ', 500, 1, null, 'mbps', 70, 1, $now, $now, null, null],
                [$bundleId, '1000+TV', '1 Гбит/с + ТВ', 1000, 1, null, 'mbps', 80, 1, $now, $now, null, null],
                [$bundleId, '2500+TV', '2.5 Гбит/с + ТВ', 2500, 1, null, 'mbps', 90, 1, $now, $now, null, null],
            ]
        );

        $this->insert('{{%provider}}', [
            'code' => 'tenet',
            'name' => 'TENET',
            'website_url' => 'https://www.tenet.ua',
            'is_our' => 1,
            'is_active' => 1,
            'sort_order' => 10,
            'created_at' => $now,
            'updated_at' => $now,
            'created_by' => null,
            'updated_by' => null,
        ]);
    }

    public function safeDown(): void
    {
        $this->delete('{{%provider}}', ['code' => 'tenet']);
        $this->delete('{{%product}}', ['code' => [
            '50','75','100','200','250','300','500','1000',
            '50+TV','75+TV','100+TV','200+TV','250+TV','300+TV','500+TV','1000+TV','2500+TV',
        ]]);
        $this->delete('{{%product_category}}', ['code' => ['internet', 'internet_tv', 'domains']]);
    }
}