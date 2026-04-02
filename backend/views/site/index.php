<?php
/**
 * @var yii\web\View $this
 * @var string $activeTab
 * @var \common\models\snapshot\MarketSnapshot|null $latestInternetSnapshot
 * @var \common\models\snapshot\MarketSnapshotItem[] $internetRows
 * @var string $currentUserName
 * @var string $currentUserRole
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Анализ конкурентов';

$activeTab = $activeTab ?? 'overview';

$tabTitles = [
        'overview'   => 'Обзор рынка',
        'comparison' => 'Сравнение провайдеров',
        'internet'   => 'Интернет',
        'bundle'     => 'Интернет + Телевидение',
        'promotions' => 'Маркетинг и акции',
        'adddata'    => 'Редактор данных',
];

$canEdit = Yii::$app->user->can('editCompetitors');
$canManageUsers = Yii::$app->user->can('manageUsers');

$items = $internetRows ?? [];

$providerNames = [];
$productLabels = [];
$matrix = [];
$allPrices = [];
$changedCells = 0;

foreach ($items as $item) {
    $providerCode = $item->provider?->code ?? ('provider_' . $item->provider_id);
    $providerName = $item->provider?->name ?? $providerCode;
    $productCode = $item->product?->code ?? ('product_' . $item->product_id);
    $productName = $item->product?->name ?? $productCode;

    $providerNames[$providerCode] = $providerName;
    $productLabels[$productCode] = $productName;

    $matrix[$providerCode][$productCode] = [
            'price' => $item->price,
            'source_type' => $item->source_type,
            'is_copied' => (bool)$item->is_copied,
            'changed_in_snapshot' => (bool)$item->changed_in_snapshot,
            'availability_status' => $item->availability_status,
    ];

    if ($item->price !== null) {
        $allPrices[] = (float)$item->price;
    }

    if ((bool)$item->changed_in_snapshot) {
        $changedCells++;
    }
}

uksort($productLabels, static function (string $a, string $b): int {
    $aNum = (int)preg_replace('/\D+/', '', $a);
    $bNum = (int)preg_replace('/\D+/', '', $b);

    if ($aNum === $bNum) {
        return strcmp($a, $b);
    }

    return $aNum <=> $bNum;
});

asort($providerNames);

$totalProviders = count($providerNames);
$avgPrice = count($allPrices) > 0 ? round(array_sum($allPrices) / count($allPrices)) : 0;

$lastUpdateText = 'Срезов ещё нет';
if ($latestInternetSnapshot !== null) {
    $lastUpdateText = sprintf(
            'Последнее обновление: %s · ревизия #%d',
            Yii::$app->formatter->asDatetime($latestInternetSnapshot->created_at, 'php:d.m.Y H:i'),
            (int)$latestInternetSnapshot->revision
    );
}

$providerNotes = [];
if ($latestInternetSnapshot !== null) {
    foreach ($latestInternetSnapshot->providerNotes as $note) {
        $providerCode = $note->provider?->code ?? ('provider_' . $note->provider_id);
        $providerNotes[$providerCode] = $note;
    }
}

$tabUrl = static fn(string $tab): string => Url::to(['site/index', 'tab' => $tab]);
?>
<style>
    :root {
        --bg-main: #0b1020;
        --bg-accent: radial-gradient(circle at top right, rgba(0, 122, 255, 0.22), transparent 28%),
        radial-gradient(circle at bottom left, rgba(52, 199, 89, 0.16), transparent 26%),
        linear-gradient(160deg, #0b1020 0%, #11182c 48%, #0d1324 100%);
        --panel: rgba(255,255,255,0.08);
        --panel-strong: rgba(255,255,255,0.12);
        --panel-border: rgba(255,255,255,0.12);
        --text-main: #f5f7fb;
        --text-muted: rgba(245,247,251,0.68);
        --accent-blue: #0a84ff;
        --accent-green: #34c759;
        --accent-orange: #ff9f0a;
        --accent-red: #ff453a;
        --accent-cyan: #64d2ff;
        --sidebar-width: 92px;
        --sidebar-width-expanded: 280px;
        --radius-xl: 28px;
        --radius-lg: 20px;
        --shadow: 0 18px 50px rgba(0,0,0,0.28);
        --blur: blur(22px);
    }

    * {
        box-sizing: border-box;
    }

    body {
        background: var(--bg-main);
    }

    .dashboard-shell {
        min-height: 100vh;
        display: flex;
        background: var(--bg-accent);
        color: var(--text-main);
        font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .sidebar {
        width: var(--sidebar-width);
        min-height: 100vh;
        padding: 28px 18px;
        border-right: 1px solid rgba(255,255,255,0.08);
        background: rgba(8, 13, 28, 0.64);
        backdrop-filter: var(--blur);
        -webkit-backdrop-filter: var(--blur);
        position: sticky;
        top: 0;
        transition: width .22s ease;
        overflow: hidden;
    }

    .sidebar:hover {
        width: var(--sidebar-width-expanded);
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 28px;
        min-width: 240px;
    }

    .brand-logo {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(10,132,255,0.95), rgba(100,210,255,0.88));
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 24px rgba(10,132,255,0.28);
        flex-shrink: 0;
        font-size: 20px;
        font-weight: 800;
        color: white;
    }

    .brand-text {
        opacity: 0;
        transform: translateX(8px);
        transition: opacity .18s ease, transform .18s ease;
        white-space: nowrap;
    }

    .sidebar:hover .brand-text {
        opacity: 1;
        transform: translateX(0);
    }

    .brand-title {
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .brand-subtitle {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .nav-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-width: 240px;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 56px;
        padding: 0 14px;
        border-radius: 18px;
        text-decoration: none;
        color: var(--text-main);
        background: transparent;
        transition: background .18s ease, transform .18s ease, box-shadow .18s ease;
    }

    .nav-item:hover {
        background: rgba(255,255,255,0.08);
        transform: translateX(2px);
    }

    .nav-item.active {
        background: rgba(10,132,255,0.18);
        box-shadow: inset 0 0 0 1px rgba(100,210,255,0.18);
    }

    .nav-icon {
        width: 28px;
        height: 28px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: rgba(255,255,255,0.08);
        font-size: 13px;
        font-weight: 800;
    }

    .nav-label {
        opacity: 0;
        transform: translateX(8px);
        transition: opacity .18s ease, transform .18s ease;
        white-space: nowrap;
        font-weight: 700;
    }

    .sidebar:hover .nav-label {
        opacity: 1;
        transform: translateX(0);
    }

    .main {
        flex: 1;
        min-width: 0;
        padding: 28px;
    }

    .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 24px;
    }

    .page-title {
        font-size: 34px;
        font-weight: 900;
        letter-spacing: -0.03em;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .pill {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        border-radius: 999px;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.10);
        color: var(--text-main);
        font-size: 13px;
        font-weight: 700;
        backdrop-filter: var(--blur);
        -webkit-backdrop-filter: var(--blur);
    }

    .pill-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--accent-green);
        box-shadow: 0 0 0 6px rgba(52,199,89,0.14);
    }

    .content-scroll {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .view-section.hidden {
        display: none;
    }

    .glass-panel {
        background: var(--panel);
        border: 1px solid var(--panel-border);
        border-radius: var(--radius-xl);
        backdrop-filter: var(--blur);
        -webkit-backdrop-filter: var(--blur);
        box-shadow: var(--shadow);
    }

    .bento-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }

    .widget {
        padding: 22px;
        min-height: 150px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .widget-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-muted);
        margin-bottom: 10px;
    }

    .widget-value {
        font-size: 34px;
        font-weight: 900;
        letter-spacing: -0.03em;
    }

    .widget-footer {
        margin-top: 16px;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.02em;
    }

    .badge-blue { background: rgba(10,132,255,0.18); color: #9fd0ff; }
    .badge-cyan { background: rgba(100,210,255,0.18); color: #bff0ff; }
    .badge-orange { background: rgba(255,159,10,0.18); color: #ffd28a; }
    .badge-green { background: rgba(52,199,89,0.18); color: #98ecb0; }
    .badge-red { background: rgba(255,69,58,0.18); color: #ffb0ac; }

    .wide-card {
        padding: 24px;
    }

    .section-title {
        margin: 0 0 18px;
        font-size: 22px;
        font-weight: 850;
        letter-spacing: -0.02em;
    }

    .section-subtitle {
        margin: 0 0 18px;
        color: var(--text-muted);
        font-size: 14px;
        line-height: 1.55;
    }

    .table-wrap {
        overflow: auto;
        border-radius: var(--radius-xl);
    }

    .market-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 840px;
    }

    .market-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: rgba(11,16,32,0.90);
        color: rgba(245,247,251,0.88);
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        padding: 16px 14px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        white-space: nowrap;
    }

    .market-table tbody td,
    .market-table tbody th {
        padding: 16px 14px;
        border-bottom: 1px solid rgba(255,255,255,0.06);
        vertical-align: top;
    }

    .market-table tbody th {
        position: sticky;
        left: 0;
        z-index: 1;
        background: rgba(17,24,44,0.92);
        min-width: 180px;
        text-align: left;
    }

    .provider-name {
        font-weight: 800;
        font-size: 15px;
    }

    .cell-price {
        display: inline-flex;
        flex-direction: column;
        gap: 6px;
        min-width: 92px;
    }

    .price-value {
        font-weight: 850;
        font-size: 16px;
        letter-spacing: -0.01em;
    }

    .price-empty {
        color: rgba(245,247,251,0.36);
        font-weight: 800;
    }

    .cell-meta {
        display: inline-flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .mini-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
    }

    .mini-manual {
        background: rgba(10,132,255,0.18);
        color: #9fd0ff;
    }

    .mini-carry {
        background: rgba(255,255,255,0.10);
        color: rgba(245,247,251,0.72);
    }

    .mini-changed {
        background: rgba(52,199,89,0.18);
        color: #9be9b5;
    }

    .cell-changed {
        background: linear-gradient(180deg, rgba(52,199,89,0.12), rgba(52,199,89,0.03));
    }

    .cell-manual {
        box-shadow: inset 0 0 0 1px rgba(10,132,255,0.16);
        border-radius: 14px;
        padding: 10px 12px;
        display: inline-flex;
    }

    .notes-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .note-card {
        padding: 22px;
    }

    .note-provider {
        font-size: 18px;
        font-weight: 850;
        margin-bottom: 14px;
    }

    .note-block + .note-block {
        margin-top: 16px;
    }

    .note-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-muted);
        margin-bottom: 6px;
        font-weight: 800;
    }

    .note-text {
        white-space: pre-line;
        color: var(--text-main);
        line-height: 1.6;
        font-size: 14px;
    }

    .placeholder {
        padding: 28px;
        min-height: 220px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 12px;
    }

    .placeholder h3 {
        margin: 0;
        font-size: 24px;
        font-weight: 850;
    }

    .placeholder p {
        margin: 0;
        color: var(--text-muted);
        line-height: 1.65;
        max-width: 760px;
    }

    .placeholder ul {
        margin: 8px 0 0 18px;
        color: var(--text-muted);
        line-height: 1.7;
    }

    @media (max-width: 1240px) {
        .bento-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .notes-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 900px) {
        .sidebar {
            display: none;
        }

        .main {
            padding: 18px;
        }

        .header {
            flex-direction: column;
            align-items: flex-start;
        }

        .bento-grid {
            grid-template-columns: 1fr;
        }

        .page-title {
            font-size: 28px;
        }
    }
</style>

<div class="dashboard-shell">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-logo">CT</div>
            <div class="brand-text">
                <div class="brand-title">Competitive Tariffs</div>
                <div class="brand-subtitle">TENET dashboard</div>
            </div>
        </div>

        <nav class="nav-list">
            <a class="nav-item <?= $activeTab === 'overview' ? 'active' : '' ?>" href="<?= Html::encode($tabUrl('overview')) ?>">
                <span class="nav-icon">OV</span>
                <span class="nav-label">Обзор</span>
            </a>

            <a class="nav-item <?= $activeTab === 'comparison' ? 'active' : '' ?>" href="<?= Html::encode($tabUrl('comparison')) ?>">
                <span class="nav-icon">CP</span>
                <span class="nav-label">Сравнение</span>
            </a>

            <a class="nav-item <?= $activeTab === 'internet' ? 'active' : '' ?>" href="<?= Html::encode($tabUrl('internet')) ?>">
                <span class="nav-icon">IN</span>
                <span class="nav-label">Интернет</span>
            </a>

            <a class="nav-item <?= $activeTab === 'bundle' ? 'active' : '' ?>" href="<?= Html::encode($tabUrl('bundle')) ?>">
                <span class="nav-icon">TV</span>
                <span class="nav-label">Интернет + ТВ</span>
            </a>

            <a class="nav-item <?= $activeTab === 'promotions' ? 'active' : '' ?>" href="<?= Html::encode($tabUrl('promotions')) ?>">
                <span class="nav-icon">AK</span>
                <span class="nav-label">Акции</span>
            </a>

            <?php if ($canEdit): ?>
                <a class="nav-item <?= $activeTab === 'adddata' ? 'active' : '' ?>" href="<?= Html::encode($tabUrl('adddata')) ?>">
                    <span class="nav-icon">ED</span>
                    <span class="nav-label">Редактор</span>
                </a>
            <?php endif; ?>

            <?php if ($canManageUsers): ?>
                <a class="nav-item" href="javascript:void(0)">
                    <span class="nav-icon">AD</span>
                    <span class="nav-label">Администрирование</span>
                </a>
            <?php endif; ?>
            <div>
                <?= Html::beginForm(['site/logout'], 'post', ['style' => 'margin:0;']) ?>
                <button type="submit" class="nav-item" style="width: 100%; border: 0; text-align: left; cursor: pointer;">
                    <span class="nav-icon">⎋</span>
                    <span class="nav-label">Выход</span>
                </button>
                <?= Html::endForm() ?>
            </div>
        </nav>
    </aside>

    <main class="main">
        <header class="header">
            <div class="page-title"><?= Html::encode($tabTitles[$activeTab] ?? 'Анализ конкурентов') ?></div>

            <div class="header-right">
                <div class="pill">
                    <span class="pill-dot"></span>
                    <span><?= Html::encode($lastUpdateText) ?></span>
                </div>

                <div class="pill">
                    <span>👤</span>
                    <span>
                        <?= Html::encode($currentUserName) ?>
                        ·
                        <?= Html::encode($currentUserRole) ?>
                    </span>
                </div>
            </div>
        </header>

        <div class="content-scroll">
            <section class="view-section <?= $activeTab !== 'overview' ? 'hidden' : '' ?>">
                <div class="bento-grid">
                    <div class="widget glass-panel">
                        <div>
                            <div class="widget-label">Всего провайдеров</div>
                            <div class="widget-value"><?= Html::encode((string)$totalProviders) ?></div>
                        </div>
                        <div class="widget-footer">
                            <span class="badge badge-cyan">База</span>
                        </div>
                    </div>

                    <div class="widget glass-panel">
                        <div>
                            <div class="widget-label">Средний чек</div>
                            <div class="widget-value"><?= Html::encode($avgPrice > 0 ? $avgPrice . ' ₴' : '—') ?></div>
                        </div>
                        <div class="widget-footer">
                            <span class="badge badge-blue">Интернет</span>
                        </div>
                    </div>

                    <div class="widget glass-panel">
                        <div>
                            <div class="widget-label">Изменено в последней ревизии</div>
                            <div class="widget-value"><?= Html::encode((string)$changedCells) ?></div>
                        </div>
                        <div class="widget-footer">
                            <span class="badge badge-green">Δ Обновления</span>
                        </div>
                    </div>

                    <div class="widget glass-panel">
                        <div>
                            <div class="widget-label">Текущая ревизия</div>
                            <div class="widget-value">
                                <?= Html::encode($latestInternetSnapshot ? '#' . $latestInternetSnapshot->revision : '—') ?>
                            </div>
                        </div>
                        <div class="widget-footer">
                            <span class="badge badge-orange">
                                <?= Html::encode($latestInternetSnapshot ? $latestInternetSnapshot->snapshot_date : 'нет данных') ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="glass-panel wide-card" style="margin-top: 18px;">
                    <h2 class="section-title">Состояние рынка</h2>
                    <p class="section-subtitle">
                        Это серверная версия shell из HTML-прототипа: вкладки, заголовок страницы, индикатор последнего обновления
                        и базовые KPI теперь строятся уже не из браузерного JS-state, а из БД и последнего исторического среза.
                    </p>

                    <?php if ($latestInternetSnapshot !== null): ?>
                        <div class="pill">
                            <span class="pill-dot"></span>
                            <span>
                                Snapshot #<?= Html::encode((string)$latestInternetSnapshot->id) ?> ·
                                revision <?= Html::encode((string)$latestInternetSnapshot->revision) ?> ·
                                trigger <?= Html::encode($latestInternetSnapshot->triggerProvider?->name ?? '—') ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="view-section <?= $activeTab !== 'comparison' ? 'hidden' : '' ?>">
                <div class="glass-panel placeholder">
                    <h3>Сравнение провайдеров</h3>
                    <p>
                        Этот раздел следующим шагом подвяжем к <code>SnapshotReaderService::buildComparisonRows()</code>,
                        чтобы сравнение строилось по выбранной дате отчёта и двум провайдерам из исторических срезов.
                    </p>
                </div>
            </section>

            <section class="view-section <?= $activeTab !== 'internet' ? 'hidden' : '' ?>">
                <div class="glass-panel wide-card">
                    <h2 class="section-title">Интернет</h2>
                    <p class="section-subtitle">
                        Таблица построена из последнего актуального snapshot по категории <strong>internet</strong>.
                        Подсветка зелёным показывает значения, изменённые в последней ревизии, а метки <strong>manual</strong> /
                        <strong>carry</strong> показывают происхождение данных.
                    </p>

                    <div class="table-wrap">
                        <table class="market-table">
                            <thead>
                            <tr>
                                <th>Провайдер</th>
                                <?php foreach ($productLabels as $productCode => $productName): ?>
                                    <th><?= Html::encode($productName) ?></th>
                                <?php endforeach; ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($providerNames === []): ?>
                                <tr>
                                    <th>—</th>
                                    <td colspan="<?= max(1, count($productLabels)) ?>">
                                        Данных по последнему срезу пока нет.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($providerNames as $providerCode => $providerName): ?>
                                    <tr>
                                        <th>
                                            <div class="provider-name"><?= Html::encode($providerName) ?></div>
                                            <div class="widget-label" style="margin: 6px 0 0;"><?= Html::encode($providerCode) ?></div>
                                        </th>

                                        <?php foreach ($productLabels as $productCode => $productName): ?>
                                            <?php
                                            $cell = $matrix[$providerCode][$productCode] ?? null;
                                            $changed = $cell['changed_in_snapshot'] ?? false;
                                            $manual = ($cell['source_type'] ?? null) === 'manual';
                                            ?>
                                            <td class="<?= $changed ? 'cell-changed' : '' ?>">
                                                <?php if ($cell === null || $cell['price'] === null): ?>
                                                    <span class="price-empty">—</span>
                                                <?php else: ?>
                                                    <div class="<?= $manual ? 'cell-manual' : '' ?>">
                                                        <div class="cell-price">
                                                            <span class="price-value"><?= Html::encode(number_format((float)$cell['price'], 0, '.', ' ')) ?> ₴</span>
                                                            <span class="cell-meta">
                                                                <?php if ($manual): ?>
                                                                    <span class="mini-badge mini-manual">manual</span>
                                                                <?php else: ?>
                                                                    <span class="mini-badge mini-carry">carry</span>
                                                                <?php endif; ?>

                                                                <?php if ($changed): ?>
                                                                    <span class="mini-badge mini-changed">changed</span>
                                                                <?php endif; ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="view-section <?= $activeTab !== 'bundle' ? 'hidden' : '' ?>">
                <div class="glass-panel placeholder">
                    <h3>Интернет + Телевидение</h3>
                    <p>
                        В БД уже есть категория <code>internet_tv</code> и каталог пакетных тарифов, так что этот раздел
                        можно будет подключить следующим инкрементом без изменения схемы.
                    </p>
                </div>
            </section>

            <section class="view-section <?= $activeTab !== 'promotions' ? 'hidden' : '' ?>">
                <div class="glass-panel wide-card">
                    <h2 class="section-title">Маркетинг и акции</h2>
                    <p class="section-subtitle">
                        Блок строится из <code>provider_snapshot_note</code> последнего интернет-среза. Сейчас это серверный аналог
                        той части прототипа, где отдельно показывались акции и лояльность.
                    </p>

                    <div class="notes-grid">
                        <?php if ($providerNotes === []): ?>
                            <div class="glass-panel note-card">
                                <div class="note-provider">Нет данных</div>
                                <div class="note-text">Для текущего среза заметки по провайдерам ещё не сохранены.</div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($providerNames as $providerCode => $providerName): ?>
                                <?php $note = $providerNotes[$providerCode] ?? null; ?>
                                <div class="glass-panel note-card">
                                    <div class="note-provider"><?= Html::encode($providerName) ?></div>

                                    <div class="note-block">
                                        <div class="note-label">Акции</div>
                                        <div class="note-text"><?= Html::encode($note?->promotion_text ?: '—') ?></div>
                                    </div>

                                    <div class="note-block">
                                        <div class="note-label">Лояльность</div>
                                        <div class="note-text"><?= Html::encode($note?->loyalty_text ?: '—') ?></div>
                                    </div>

                                    <div class="note-block">
                                        <div class="note-label">Заметка редактора</div>
                                        <div class="note-text"><?= Html::encode($note?->editor_note ?: '—') ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <?php if ($canEdit): ?>
                <section class="view-section <?= $activeTab !== 'adddata' ? 'hidden' : '' ?>">
                    <div class="glass-panel placeholder">
                        <h3>Редактор данных</h3>
                        <p>
                            Визуально вкладка уже встроена в shell и скрывается по RBAC для тех, у кого нет
                            <code>editCompetitors</code>. Следующим шагом сюда можно подключать форму редактора провайдера
                            с сохранением через <code>SnapshotWriterService</code>.
                        </p>
                        <ul>
                            <li>выбор провайдера;</li>
                            <li>загрузка последних значений по категории <strong>internet</strong>;</li>
                            <li>изменение цен и текстовых заметок;</li>
                            <li>сохранение как новой ревизии рынка.</li>
                        </ul>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>
</div>