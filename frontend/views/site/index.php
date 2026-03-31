<?php

/** @var yii\web\View $this */

$this->title = 'Tarif-reports';
?>
<div class="content-scroll">

    <div id="overview-tab" class="view-section animate-fade">
        <div class="bento-grid">
            <div class="widget glass-panel">
                <div>
                    <div class="widget-label">Всего провайдеров</div>
                    <div class="widget-value" id="kpi-providers">0</div>
                </div>
                <div class="widget-footer">
                    <span class="badge badge-cyan">База</span>
                </div>
            </div>
            <div class="widget glass-panel">
                <div>
                    <div class="widget-label">Средний чек</div>
                    <div class="widget-value" id="kpi-avg">0 ₴</div>
                </div>
                <div class="widget-footer">
                </div>
            </div>
            <div class="widget glass-panel">
                <div>
                    <div class="widget-label">Пакетный ARPU</div>
                    <div class="widget-value" id="kpi-bundle">0 ₴</div>
                </div>
                <div class="widget-footer">
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 24px;">
            <select id="packageFilter" style="width: 240px; box-shadow: var(--shadow-float);">
                <option value="">Весь рынок</option>
                <option value="50">50 Мбит/с</option>
                <option value="75">75 Мбит/с</option>
                <option value="100">100 Мбит/с</option>
                <option value="200">200 Мбит/с</option>
                <option value="250">250 Мбит/с</option>
                <option value="300">300 Мбит/с</option>
                <option value="500">500 Мбит/с</option>
                <option value="1000">1 Гбит/с</option>
                <option value="50+TV">50 Мбит/с + ТВ</option>
                <option value="75+TV">75 Мбит/с + ТВ</option>
                <option value="100+TV">100 Мбит/с + ТВ</option>
                <option value="200+TV">200 Мбит/с + ТВ</option>
                <option value="250+TV">250 Мбит/с + ТВ</option>
                <option value="300+TV">300 Мбит/с + ТВ</option>
                <option value="500+TV">500 Мбит/с + ТВ</option>
                <option value="1000+TV">1 Гбит/с + ТВ</option>
                <option value="2500+TV">2.5 Гбит/с + ТВ</option>
            </select>
        </div>

        <div class="bento-grid" style="grid-template-columns: 1fr; min-height: 380px;">
            <div class="glass-panel chart-panel">
                <div class="chart-header">
                    <div class="chart-title">Анализ стоимости</div>
                </div>
                <div style="height: 340px;"><canvas id="providerChart"></canvas></div>
            </div>
        </div>
    </div>

    <div id="comparison-tab" class="view-section hidden animate-fade">
        <div class="glass-panel" style="padding: 32px; display: flex; gap: 24px; align-items: flex-end;">
            <div style="flex:1">
                <div class="widget-label">Базовый провайдер</div>
                <select id="provider1"></select>
            </div>
            <div style="padding-bottom: 16px; color: var(--text-tertiary); font-weight: 800; font-size: 20px;">VS</div>
            <div style="flex:1">
                <div class="widget-label">Конкурент</div>
                <select id="provider2"></select>
            </div>
        </div>

        <div class="comparison-widgets-grid">
            <div class="widget glass-panel comparison-widget">
                <div class="widget-label" id="lbl-p1">Base</div>
                <div class="widget-value" id="val-p1">0</div>
            </div>

            <div class="comparison-divider">VS</div>

            <div class="widget glass-panel comparison-widget">
                <div class="widget-label" id="lbl-p2">Comp</div>
                <div class="widget-value" id="val-p2">0</div>
            </div>

            <div class="comparison-divider">=</div>

            <div class="widget glass-panel comparison-widget">
                <div class="widget-label">Разница</div>
                <div class="widget-value" id="val-diff">0</div>
            </div>
        </div>

        <div class="glass-panel chart-panel">
            <div style="height: 360px;"><canvas id="comparisonBarChart"></canvas></div>
        </div>

        <div class="glass-panel table-wrap">
            <table id="comparisonTable"></table>
        </div>
    </div>

    <div id="internet-tab" class="view-section hidden animate-fade">
        <div class="glass-panel table-wrap">
            <table id="internetTable"></table>
        </div>
    </div>

    <div id="bundle-tab" class="view-section hidden animate-fade">
        <div class="glass-panel table-wrap">
            <table id="bundleTable"></table>
        </div>
    </div>

    <div id="promotions-tab" class="view-section hidden animate-fade">
        <div class="bento-grid" style="grid-template-columns: 1fr 1fr;">
            <div>
                <h3 style="margin-bottom: 24px; color: var(--accent-orange); font-weight: 800; font-size: 20px;">🔥 Акции</h3>
                <div id="promotionsGrid" style="display: flex; flex-direction: column; gap: 24px;"></div>
            </div>
            <div>
                <h3 style="margin-bottom: 24px; color: var(--accent-cyan); font-weight: 800; font-size: 20px;">🛡️ Лояльность</h3>
                <div id="loyaltyGrid" style="display: flex; flex-direction: column; gap: 24px;"></div>
            </div>
        </div>
    </div>

    <div id="adddata-tab" class="view-section hidden animate-fade">
        <div id="editorAlert"></div>
        <div class="glass-panel" style="padding: 40px; max-width: 960px; margin: 0 auto;">
            <h2 style="margin-bottom: 32px; color: var(--text-primary); font-weight: 800; font-size: 28px;">Редактор базы данных</h2>

            <div style="margin-bottom: 32px;">
                <div class="widget-label">Название провайдера *</div>
                <input type="text" id="providerName" placeholder="Например: TENET" required>
            </div>

            <div class="widget-label">Быстрый ввод тарифов</div>
            <div id="speedChips" class="chip-container" style="margin-bottom: 36px;"></div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 36px; margin-bottom: 36px;">
                <div>
                    <div class="widget-label" style="color: var(--accent-cyan);">Интернет</div>
                    <div id="tariffsInternetContainer" style="display: flex; flex-direction: column; gap: 16px;"></div>
                </div>
                <div>
                    <div class="widget-label" style="color: var(--accent-orange);">Пакеты (+ТВ)</div>
                    <div id="tariffsBundleContainer" style="display: flex; flex-direction: column; gap: 16px;"></div>
                </div>
            </div>

            <button class="btn btn-secondary" id="addCustomBtn" style="width: 100%; margin-bottom: 36px; font-weight: 700;">+ Добавить свой тариф</button>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 36px; margin-bottom: 36px;">
                <div>
                    <div class="widget-label">Текст акции</div>
                    <textarea id="promotionText" rows="4" placeholder="Описание акционных предложений..."></textarea>
                </div>
                <div>
                    <div class="widget-label">Текст лояльности</div>
                    <textarea id="loyaltyText" rows="4" placeholder="Описание программы лояльности..."></textarea>
                </div>
            </div>

            <button class="btn btn-cyan" id="saveBtn" style="width: 100%; padding: 18px; font-size: 18px;">Сохранить изменения</button>
        </div>
        <div style="margin-top: 48px; max-width: 960px; margin-left: auto; margin-right: auto; text-align: center;">
            <div class="widget-label" style="margin-bottom: 20px;">База провайдеров (Нажмите для редактирования)</div>
            <div id="existingProviders" class="chip-container" style="justify-content: center;"></div>
        </div>
    </div>

</div>
