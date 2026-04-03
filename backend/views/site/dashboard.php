<?php

/**
 * @var yii\web\View $this
 * @var string $activeTab
 * @var string $currentUserName
 * @var string $currentUserRole
 * @var array<string, string> $availableTabs
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard';
?>
<style>
    body {
        margin: 0;
        background: #0b1020;
        color: #f5f7fb;
        font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .shell {
        min-height: 100vh;
        display: flex;
        background:
                radial-gradient(circle at top right, rgba(0,122,255,.22), transparent 28%),
                radial-gradient(circle at bottom left, rgba(52,199,89,.16), transparent 26%),
                linear-gradient(160deg, #0b1020 0%, #11182c 48%, #0d1324 100%);
    }

    .sidebar {
        width: 280px;
        padding: 28px 20px;
        border-right: 1px solid rgba(255,255,255,.08);
        background: rgba(8,13,28,.64);
        backdrop-filter: blur(22px);
    }

    .brand {
        font-size: 20px;
        font-weight: 900;
        margin-bottom: 28px;
    }

    .nav {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .nav a, .logout-btn {
        display: flex;
        align-items: center;
        min-height: 48px;
        padding: 0 14px;
        border-radius: 14px;
        text-decoration: none;
        color: #f5f7fb;
        background: rgba(255,255,255,.04);
        border: 0;
        cursor: pointer;
        font-size: 14px;
        font-weight: 700;
    }

    .nav a.active {
        background: rgba(10,132,255,.18);
    }

    .main {
        flex: 1;
        padding: 28px;
    }

    .card {
        max-width: 760px;
        padding: 28px;
        border-radius: 24px;
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.10);
        backdrop-filter: blur(22px);
    }

    .title {
        font-size: 30px;
        font-weight: 900;
        margin: 0 0 8px;
    }

    .subtitle {
        color: rgba(245,247,251,.68);
        margin: 0 0 26px;
    }

    .row {
        display: grid;
        grid-template-columns: 180px 1fr;
        gap: 16px;
        padding: 14px 0;
        border-bottom: 1px solid rgba(255,255,255,.08);
    }

    .row:last-child {
        border-bottom: 0;
    }

    .label {
        color: rgba(245,247,251,.62);
        font-weight: 700;
    }

    .value {
        font-weight: 800;
        color: white;
    }

    .section-note {
        margin-top: 22px;
        color: rgba(245,247,251,.68);
        line-height: 1.6;
    }

    .logout-wrap {
        margin-top: 16px;
    }
</style>

<div class="shell">
    <aside class="sidebar">
        <div class="brand">Competitive Tariffs</div>

        <nav class="nav">
            <?php foreach ($availableTabs as $tabKey => $tabLabel): ?>
                <a
                        href="<?= Html::encode(Url::to(['/site/dashboard', 'tab' => $tabKey])) ?>"
                        class="<?= $activeTab === $tabKey ? 'active' : '' ?>"
                >
                    <?= Html::encode($tabLabel) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="logout-wrap">
            <?= Html::beginForm(['/site/logout'], 'post', ['style' => 'margin:0;']) ?>
            <button type="submit" class="logout-btn">Выход</button>
            <?= Html::endForm() ?>
        </div>
    </aside>

    <main class="main">
        <div class="card">
            <h1 class="title">Профиль <span><?= Html::encode($currentUserName) ?></span></h1>

            <div class="row">
                <div class="label">Имя</div>
                <div class="value"><?= Html::encode($currentUserName) ?></div>
            </div>

            <div class="row">
                <div class="label">Роль</div>
                <div class="value"><?= Html::encode($currentUserRole) ?></div>
            </div>

            <div class="row">
                <div class="label">Активная вкладка</div>
                <div class="value"><?= Html::encode($activeTab) ?></div>
            </div>

            <div class="section-note">

            </div>
        </div>
    </main>
</div>