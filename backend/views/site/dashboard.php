<?php

/**
 * @var yii\web\View $this
 * @var string $activeTab
 * @var string $currentUserName
 * @var string $currentUserRole
 * @var array<string, string> $availableTabs
 * @var array $internetSection
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard';

$internetSnapshot = $internetSection['snapshot'];
$internetProviders = $internetSection['providers'];
$internetProducts = $internetSection['products'];
$internetMatrix = $internetSection['matrix'];
$tabUrl = static function (string $tab): string {
    return $tab === 'overview'
            ? Url::to('/dashboard')
            : Url::to('/dashboard/' . $tab);
};

$bundleSnapshot = $bundleSection['snapshot'];
$bundleProviders = $bundleSection['providers'];
$bundleProducts = $bundleSection['products'];
$bundleMatrix = $bundleSection['matrix'];

$promotionsNotes = $promotionsSection['notes'];
$comparisonRows = $comparisonSection['rows'];
$comparisonProviders = $comparisonSection['providers'];
$comparisonProviderAId = $comparisonSection['providerAId'];
$comparisonProviderBId = $comparisonSection['providerBId'];
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<style>
    :root {
        --bg-light: #F2F5F9;
        --blob-1: #AEEEEE;
        --blob-2: #E6E6FA;
        --blob-3: #FFDAB9;

        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.85);
        --glass-highlight: rgba(255, 255, 255, 0.95);
        --glass-blur: blur(28px) saturate(180%);
        --shadow-float: 0 16px 40px rgba(0, 0, 0, 0.08);

        --text-primary: #000000;
        --text-secondary: #3C3C43;
        --text-tertiary: #3C3C4399;

        --accent-cyan: #007AFF;
        --accent-orange: #FF9500;
        --accent-green: #34C759;
        --accent-red: #FF3B30;
        --accent-bg-cyan: rgba(0, 122, 255, 0.12);
        --accent-bg-orange: rgba(255, 149, 0, 0.12);

        --radius-xl: 32px;
        --radius-lg: 24px;
        --spacing: 32px;
    }

    * { box-sizing: border-box; }
    body {
        margin: 0;
        background-color: var(--bg-light);
        color: var(--text-primary);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .ambient-light {
        position: fixed; inset: 0; pointer-events: none; z-index: 0;
        overflow: hidden;
        background: linear-gradient(135deg, #F5F7FA 0%, #FFFFFF 100%);
    }
    .blob { position: absolute; border-radius: 50%; filter: blur(100px); opacity: 0.55; }
    .blob-1 { top: -15%; left: -15%; width: 55vw; height: 55vw; background: var(--blob-1); }
    .blob-2 { bottom: -20%; right: -20%; width: 65vw; height: 65vw; background: var(--blob-2); }
    .blob-3 { top: 30%; left: 30%; width: 40vw; height: 40vw; background: var(--blob-3); }

    .app-container {
        position: relative;
        z-index: 1;
        display: flex;
        width: 100%;
        min-height: 100vh;
        padding: var(--spacing);
        gap: var(--spacing);
    }

    .glass-panel {
        background: var(--glass-bg);
        backdrop-filter: var(--glass-blur);
        -webkit-backdrop-filter: var(--glass-blur);
        border: 1px solid var(--glass-border);
        border-top: 1px solid var(--glass-highlight);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-float);
    }

    .sidebar {
        width: 260px;
        display: flex;
        flex-direction: column;
        padding: 28px;
        gap: 24px;
        flex-shrink: 0;
    }

    .brand {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    .nav-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .nav-item {
        min-height: 48px;
        display: flex;
        align-items: center;
        padding: 0 16px;
        border-radius: 16px;
        text-decoration: none;
        color: var(--text-secondary);
        font-weight: 600;
        transition: .2s;
    }

    .nav-item:hover {
        background: rgba(0,0,0,0.04);
        color: var(--text-primary);
    }

    .nav-item.active {
        background: var(--accent-bg-cyan);
        color: var(--accent-cyan);
        border: 1px solid rgba(0,122,255,.12);
    }

    .logout-wrap { margin-top: auto; }
    .logout-btn {
        width: 100%;
        min-height: 48px;
        border: 0;
        border-radius: 16px;
        background: rgba(255,255,255,.7);
        color: var(--text-primary);
        font-weight: 700;
        cursor: pointer;
    }

    .main {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: var(--spacing);
        min-width: 0;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 4px;
    }

    .page-title {
        font-size: 34px;
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    .last-update {
        font-size: 13px;
        color: var(--text-secondary);
        background: rgba(255,255,255,0.6);
        padding: 8px 16px;
        border-radius: 24px;
        border: 1px solid var(--glass-border);
    }

    .content-scroll {
        flex: 1;
        overflow-y: auto;
        padding-right: 12px;
        display: flex;
        flex-direction: column;
        gap: var(--spacing);
        padding-bottom: var(--spacing);
    }

    .bento-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: var(--spacing);
    }

    .widget {
        padding: 32px;
        min-height: 160px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .widget-label {
        font-size: 13px;
        color: var(--text-secondary);
        margin-bottom: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .widget-value {
        font-size: 42px;
        font-weight: 800;
        letter-spacing: -0.04em;
        line-height: 1;
    }

    .badge {
        padding: 6px 14px;
        border-radius: 16px;
        font-weight: 600;
        font-size: 12px;
    }
    .badge-cyan { background: var(--accent-bg-cyan); color: var(--accent-cyan); }
    .badge-orange { background: var(--accent-bg-orange); color: var(--accent-orange); }
    .badge-green { background: rgba(52, 199, 89, 0.15); color: var(--accent-green); }

    .card {
        padding: 32px;
    }

    .title {
        font-size: 28px;
        font-weight: 800;
        margin: 0 0 10px;
    }

    .subtitle {
        color: var(--text-secondary);
        line-height: 1.6;
        margin: 0 0 24px;
    }

    .row {
        display: grid;
        grid-template-columns: 180px 1fr;
        gap: 16px;
        padding: 14px 0;
        border-bottom: 1px solid rgba(0,0,0,.06);
    }

    .row:last-child { border-bottom: 0; }

    .table-wrap { overflow-x: auto; border-radius: var(--radius-xl); }
    table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 820px; }

    th {
        text-align: left;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-tertiary);
        padding: 20px 28px;
        font-weight: 600;
        border-bottom: 1px solid var(--glass-border);
    }

    td {
        padding: 18px 28px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        vertical-align: middle;
    }

    .price-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 14px;
        font-weight: 700;
        background: rgba(255,255,255,0.65);
    }

    .price-chip.changed {
        background: rgba(52,199,89,.12);
    }

    .mini-tag {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
    }

    .mini-manual { background: rgba(0,122,255,.12); color: var(--accent-cyan); }
    .mini-carry { background: rgba(0,0,0,.06); color: var(--text-secondary); }

    .hidden { display: none; }
</style>

<div class="ambient-light">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
</div>

<div class="app-container">
    <aside class="sidebar glass-panel">
        <div class="brand">TENET Dashboard</div>

        <nav class="nav-list">
            <?php foreach ($availableTabs as $tabKey => $tabLabel): ?>
                <a
                        href="<?= Html::encode($tabUrl($tabKey)) ?>"
                        class="nav-item <?= $activeTab === $tabKey ? 'active' : '' ?>"
                >
                    <?= Html::encode($tabLabel) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="logout-wrap">
            <?= Html::beginForm('/logout', 'post', ['style' => 'margin:0;']) ?>
            <button type="submit" class="logout-btn">Выход</button>
            <?= Html::endForm() ?>
        </div>
    </aside>

    <main class="main">
        <header class="header">
            <div class="page-title">
                <?= $activeTab === 'internet' ? 'Интернет' : 'Профиль пользователя' ?>
            </div>
            <div class="last-update">
                <?php if ($internetSnapshot): ?>
                    Последнее обновление: <?= Html::encode($internetSnapshot->snapshot_date) ?> · ревизия #<?= Html::encode((string)$internetSnapshot->revision) ?>
                <?php else: ?>
                    Данных пока нет
                <?php endif; ?>
            </div>
        </header>

        <div class="content-scroll">
            <section class="<?= $activeTab !== 'overview' ? 'hidden' : '' ?>">
                <div class="glass-panel card">
                    <h1 class="title">Профиль пользователя</h1>
                    <p class="subtitle">Это домашняя страница `/dashboard`. Разделы аналитики доступны через меню слева.</p>

                    <div class="row">
                        <div>Имя</div>
                        <div><strong><?= Html::encode($currentUserName) ?></strong></div>
                    </div>
                    <div class="row">
                        <div>Роль</div>
                        <div><strong><?= Html::encode($currentUserRole) ?></strong></div>
                    </div>
                    <div class="row">
                        <div>Доступные разделы</div>
                        <div><strong><?= Html::encode(implode(', ', array_values($availableTabs))) ?></strong></div>
                    </div>
                </div>
            </section>

            <section class="<?= $activeTab !== 'internet' ? 'hidden' : '' ?>">
                <div class="bento-grid">
                    <div class="widget glass-panel">
                        <div>
                            <div class="widget-label">Всего провайдеров</div>
                            <div class="widget-value"><?= Html::encode((string)$internetSection['providerCount']) ?></div>
                        </div>
                        <span class="badge badge-cyan">База</span>
                    </div>

                    <div class="widget glass-panel">
                        <div>
                            <div class="widget-label">Средняя цена</div>
                            <div class="widget-value"><?= Html::encode($internetSection['avgPrice']) ?> ₴</div>
                        </div>
                        <span class="badge badge-orange">Рынок</span>
                    </div>

                    <div class="widget glass-panel">
                        <div>
                            <div class="widget-label">Минимальная цена</div>
                            <div class="widget-value"><?= Html::encode($internetSection['minPrice']) ?> ₴</div>
                        </div>
                        <span class="badge badge-green">Low</span>
                    </div>

                    <div class="widget glass-panel">
                        <div>
                            <div class="widget-label">Изменено ячеек</div>
                            <div class="widget-value"><?= Html::encode((string)$internetSection['changedCells']) ?></div>
                        </div>
                        <span class="badge badge-green">Δ</span>
                    </div>
                </div>

                <div class="glass-panel card">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:20px; margin-bottom:24px;">
                        <div>
                            <h2 class="title" style="font-size:22px; margin-bottom:6px;">Анализ стоимости</h2>
                            <div class="subtitle" style="margin:0;">График строится по последнему snapshot категории internet.</div>
                        </div>

                        <div style="width:260px;">
                            <select id="internetPackageFilter">
                                <option value="">Весь рынок</option>
                                <?php foreach ($internetSection['chartProducts'] as $product): ?>
                                    <option value="<?= Html::encode($product['code']) ?>">
                                        <?= Html::encode($product['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="height:360px;">
                        <canvas id="internetChart"></canvas>
                    </div>
                </div>

                <div class="glass-panel table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Провайдер</th>
                            <?php foreach ($internetProducts as $product): ?>
                                <th><?= Html::encode($product['name']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($internetProviders as $providerCode => $providerName): ?>
                            <tr>
                                <td><strong><?= Html::encode($providerName) ?></strong></td>
                                <?php foreach ($internetProducts as $productCode => $product): ?>
                                    <?php $cell = $internetMatrix[$providerCode][$productCode] ?? null; ?>
                                    <td>
                                        <?php if ($cell === null || $cell['price'] === null): ?>
                                            —
                                        <?php else: ?>
                                            <div class="price-chip <?= $cell['changed_in_snapshot'] ? 'changed' : '' ?>">
                                                <?= Html::encode(number_format((float)$cell['price'], 0, '.', ' ')) ?> ₴

                                                <?php if ($cell['source_type'] === 'manual'): ?>
                                                    <span class="mini-tag mini-manual">manual</span>
                                                <?php else: ?>
                                                    <span class="mini-tag mini-carry">carry</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>

                        <?php if ($internetProviders === []): ?>
                            <tr>
                                <td colspan="<?= count($internetProducts) + 1 ?>">Нет данных по интернет-срезу.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="<?= !in_array($activeTab, ['comparison','bundle','promotions','adddata'], true) ? 'hidden' : '' ?>">
                <div class="glass-panel card">
                    <h2 class="title"><?= Html::encode($availableTabs[$activeTab] ?? $activeTab) ?></h2>
                    <p class="subtitle">Этот раздел подключим следующим шагом.</p>
                </div>
            </section>
            <section class="<?= $activeTab !== 'comparison' ? 'hidden' : '' ?>">
                <div class="glass-panel card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:24px; margin-bottom:24px;">
                        <div style="flex:1;">
                            <div class="widget-label">Базовый провайдер</div>
                            <select id="providerASelect">
                                <?php foreach ($comparisonProviders as $provider): ?>
                                    <option value="<?= Html::encode((string)$provider['id']) ?>" <?= $provider['id'] === $comparisonProviderAId ? 'selected' : '' ?>>
                                        <?= Html::encode($provider['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="padding-bottom: 14px; color: var(--text-tertiary); font-weight: 800;">VS</div>

                        <div style="flex:1;">
                            <div class="widget-label">Конкурент</div>
                            <select id="providerBSelect">
                                <?php foreach ($comparisonProviders as $provider): ?>
                                    <option value="<?= Html::encode((string)$provider['id']) ?>" <?= $provider['id'] === $comparisonProviderBId ? 'selected' : '' ?>>
                                        <?= Html::encode($provider['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="height:360px; margin-bottom:28px;">
                        <canvas id="comparisonChart"></canvas>
                    </div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                            <tr>
                                <th>Тариф</th>
                                <th>Базовый</th>
                                <th>Конкурент</th>
                                <th>Разница</th>
                            </tr>
                            </thead>
                            <tbody id="comparisonTableBody">
                            <?php foreach ($comparisonRows as $row): ?>
                                <tr>
                                    <td><strong><?= Html::encode($row['product_name']) ?></strong></td>
                                    <td><?= $row['provider_a_price'] !== null ? Html::encode(number_format((float)$row['provider_a_price'], 0, '.', ' ')) . ' ₴' : '—' ?></td>
                                    <td><?= $row['provider_b_price'] !== null ? Html::encode(number_format((float)$row['provider_b_price'], 0, '.', ' ')) . ' ₴' : '—' ?></td>
                                    <td>
                                        <?php if ($row['delta'] !== null): ?>
                                            <strong style="color: <?= $row['delta'] > 0 ? 'var(--accent-red)' : 'var(--accent-green)' ?>">
                                                <?= Html::encode(number_format((float)$row['delta'], 0, '.', ' ')) ?> ₴
                                            </strong>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if ($comparisonRows === []): ?>
                                <tr>
                                    <td colspan="4">Недостаточно данных для сравнения.</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
            <section class="<?= $activeTab !== 'comparison' ? 'hidden' : '' ?>">
                <div class="glass-panel card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:24px; margin-bottom:24px;">
                        <div style="flex:1;">
                            <div class="widget-label">Базовый провайдер</div>
                            <select id="providerASelect">
                                <?php foreach ($comparisonProviders as $provider): ?>
                                    <option value="<?= Html::encode((string)$provider['id']) ?>" <?= $provider['id'] === $comparisonProviderAId ? 'selected' : '' ?>>
                                        <?= Html::encode($provider['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="padding-bottom: 14px; color: var(--text-tertiary); font-weight: 800;">VS</div>

                        <div style="flex:1;">
                            <div class="widget-label">Конкурент</div>
                            <select id="providerBSelect">
                                <?php foreach ($comparisonProviders as $provider): ?>
                                    <option value="<?= Html::encode((string)$provider['id']) ?>" <?= $provider['id'] === $comparisonProviderBId ? 'selected' : '' ?>>
                                        <?= Html::encode($provider['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="height:360px; margin-bottom:28px;">
                        <canvas id="comparisonChart"></canvas>
                    </div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                            <tr>
                                <th>Тариф</th>
                                <th>Базовый</th>
                                <th>Конкурент</th>
                                <th>Разница</th>
                            </tr>
                            </thead>
                            <tbody id="comparisonTableBody">
                            <?php foreach ($comparisonRows as $row): ?>
                                <tr>
                                    <td><strong><?= Html::encode($row['product_name']) ?></strong></td>
                                    <td><?= $row['provider_a_price'] !== null ? Html::encode(number_format((float)$row['provider_a_price'], 0, '.', ' ')) . ' ₴' : '—' ?></td>
                                    <td><?= $row['provider_b_price'] !== null ? Html::encode(number_format((float)$row['provider_b_price'], 0, '.', ' ')) . ' ₴' : '—' ?></td>
                                    <td>
                                        <?php if ($row['delta'] !== null): ?>
                                            <strong style="color: <?= $row['delta'] > 0 ? 'var(--accent-red)' : 'var(--accent-green)' ?>">
                                                <?= Html::encode(number_format((float)$row['delta'], 0, '.', ' ')) ?> ₴
                                            </strong>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if ($comparisonRows === []): ?>
                                <tr>
                                    <td colspan="4">Недостаточно данных для сравнения.</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
            <section class="<?= $activeTab !== 'promotions' ? 'hidden' : '' ?>">
                <div class="glass-panel card">
                    <h2 class="title">Маркетинг и акции</h2>
                    <p class="subtitle">Данные строятся из `provider_snapshot_note` последнего internet-snapshot.</p>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
                        <?php foreach ($promotionsNotes as $providerCode => $note): ?>
                            <div class="glass-panel" style="padding:24px;">
                                <div style="font-size:20px; font-weight:800; margin-bottom:18px;">
                                    <?= Html::encode($note['provider_name']) ?>
                                </div>

                                <div style="margin-bottom:18px;">
                                    <div class="widget-label">Акции</div>
                                    <div><?= nl2br(Html::encode($note['promotion_text'] ?: '—')) ?></div>
                                </div>

                                <div style="margin-bottom:18px;">
                                    <div class="widget-label">Лояльность</div>
                                    <div><?= nl2br(Html::encode($note['loyalty_text'] ?: '—')) ?></div>
                                </div>

                                <div style="margin-bottom:18px;">
                                    <div class="widget-label">Заметка редактора</div>
                                    <div><?= nl2br(Html::encode($note['editor_note'] ?: '—')) ?></div>
                                </div>

                                <div style="display:flex; gap:8px;">
                                    <?php if ($note['source_type'] === 'manual'): ?>
                                        <span class="mini-tag mini-manual">manual</span>
                                    <?php else: ?>
                                        <span class="mini-tag mini-carry">carry</span>
                                    <?php endif; ?>

                                    <?php if ($note['changed_in_snapshot']): ?>
                                        <span class="mini-tag" style="background: rgba(52,199,89,.12); color: var(--accent-green);">changed</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if ($promotionsNotes === []): ?>
                            <div class="glass-panel" style="padding:24px;">
                                Пока нет данных по акциям и лояльности.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </main>
</div>

<script>
        const bundleProducts = <?= json_encode($bundleSection['chartProducts'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const bundleProviders = <?= json_encode($bundleSection['chartProviders'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const comparisonRows = <?= json_encode($comparisonRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const comparisonProviders = <?= json_encode($comparisonProviders, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const comparisonProviderAId = <?= json_encode($comparisonProviderAId) ?>;
        const comparisonProviderBId = <?= json_encode($comparisonProviderBId) ?>;

        let bundleChartInstance = null;
        let comparisonChartInstance = null;

        function renderBundleChart() {
        const canvas = document.getElementById('bundleChart');
        if (!canvas) return;

        const rows = [];

        bundleProviders.forEach(provider => {
        const values = Object.values(provider.prices).filter(v => v !== null && v !== undefined).map(Number);
        if (values.length > 0) {
        rows.push({
        name: provider.name,
        value: values.reduce((a, b) => a + b, 0) / values.length
    });
    }
    });

        rows.sort((a, b) => a.value - b.value);

        if (bundleChartInstance) {
        bundleChartInstance.destroy();
    }

        const ctx = canvas.getContext('2d');
        bundleChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
        labels: rows.map(r => r.name),
        datasets: [{
        label: 'Средняя цена пакета',
        data: rows.map(r => r.value),
    }]
    },
        options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
    }
    });
    }

        function renderComparisonChart() {
        const canvas = document.getElementById('comparisonChart');
        if (!canvas) return;

        const rows = comparisonRows.filter(r => r.provider_a_price !== null || r.provider_b_price !== null);

        if (comparisonChartInstance) {
        comparisonChartInstance.destroy();
    }

        const ctx = canvas.getContext('2d');
        comparisonChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
        labels: rows.map(r => r.product_name),
        datasets: [
    {
        label: 'Базовый',
        data: rows.map(r => r.provider_a_price),
    },
    {
        label: 'Конкурент',
        data: rows.map(r => r.provider_b_price),
    }
        ]
    },
        options: {
        responsive: true,
        maintainAspectRatio: false,
    }
    });
    }

        document.addEventListener('DOMContentLoaded', function () {
        renderBundleChart();
        renderComparisonChart();
    });

    let internetChartInstance = null;

    function renderInternetChart() {
        const filterEl = document.getElementById('internetPackageFilter');
        const canvas = document.getElementById('internetChart');

        if (!filterEl || !canvas) {
            return;
        }

        const filter = filterEl.value;
        const rows = [];

        chartProviders.forEach(provider => {
            let values = [];

            chartProducts.forEach(product => {
                const value = provider.prices[product.code];

                if (value !== null && value !== undefined && (!filter || product.code === filter)) {
                    values.push(Number(value));
                }
            });

            if (values.length > 0) {
                rows.push({
                    name: provider.name,
                    value: values.reduce((a, b) => a + b, 0) / values.length
                });
            }
        });

        rows.sort((a, b) => a.value - b.value);

        if (internetChartInstance) {
            internetChartInstance.destroy();
        }

        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, '#007AFF');
        gradient.addColorStop(1, 'rgba(0, 122, 255, 0.10)');

        Chart.defaults.color = '#3C3C4399';
        Chart.defaults.font.family = "'Inter', -apple-system, sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.font.weight = 600;

        internetChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: rows.map(row => row.name),
                datasets: [{
                    label: 'Средняя цена',
                    data: rows.map(row => row.value),
                    backgroundColor: gradient,
                    borderRadius: 10,
                    barThickness: 18
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(255,255,255,0.95)',
                        titleColor: '#000',
                        bodyColor: '#3C3C43',
                        borderColor: 'rgba(0,0,0,0.05)',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const filterEl = document.getElementById('internetPackageFilter');
        if (filterEl) {
            filterEl.addEventListener('change', renderInternetChart);
        }
        renderInternetChart();
    });
</script>