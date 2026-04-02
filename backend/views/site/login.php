<?php

/** @var yii\web\View $this */
/** @var common\models\LoginForm $model */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Вход в панель';
?>
<style>
    :root {
        --bg-main: #0b1020;
        --bg-accent: radial-gradient(circle at top right, rgba(0, 122, 255, 0.22), transparent 28%),
        radial-gradient(circle at bottom left, rgba(52, 199, 89, 0.16), transparent 26%),
        linear-gradient(160deg, #0b1020 0%, #11182c 48%, #0d1324 100%);
        --panel: rgba(255,255,255,0.08);
        --panel-border: rgba(255,255,255,0.12);
        --text-main: #f5f7fb;
        --text-muted: rgba(245,247,251,0.68);
        --accent-blue: #0a84ff;
        --shadow: 0 18px 50px rgba(0,0,0,0.28);
        --blur: blur(22px);
    }

    body {
        margin: 0;
        min-height: 100vh;
        background: var(--bg-main);
        color: var(--text-main);
        font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .login-shell {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: var(--bg-accent);
    }

    .login-card {
        width: 100%;
        max-width: 460px;
        padding: 32px;
        border-radius: 28px;
        background: var(--panel);
        border: 1px solid var(--panel-border);
        backdrop-filter: var(--blur);
        -webkit-backdrop-filter: var(--blur);
        box-shadow: var(--shadow);
    }

    .login-logo {
        width: 62px;
        height: 62px;
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(10,132,255,0.95), rgba(100,210,255,0.88));
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 22px;
        font-weight: 900;
        margin-bottom: 18px;
    }

    .login-title {
        margin: 0 0 8px;
        font-size: 30px;
        font-weight: 900;
        letter-spacing: -0.03em;
    }

    .login-subtitle {
        margin: 0 0 26px;
        color: var(--text-muted);
        line-height: 1.6;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .control-label {
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 800;
        color: var(--text-muted);
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .form-control {
        width: 100%;
        min-height: 48px;
        padding: 12px 14px;
        border-radius: 14px;
        border: 1px solid rgba(255,255,255,0.10);
        background: rgba(255,255,255,0.08);
        color: var(--text-main);
        outline: none;
        box-shadow: none;
    }

    .form-control:focus {
        border-color: rgba(10,132,255,0.55);
        box-shadow: 0 0 0 4px rgba(10,132,255,0.14);
    }

    .help-block {
        margin-top: 8px;
        font-size: 13px;
        color: #ffb0ac;
    }

    .checkbox label {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: var(--text-muted);
        cursor: pointer;
        user-select: none;
    }

    .btn-login {
        width: 100%;
        min-height: 50px;
        border: 0;
        border-radius: 16px;
        background: linear-gradient(135deg, #0a84ff, #64d2ff);
        color: #fff;
        font-size: 15px;
        font-weight: 900;
        letter-spacing: 0.01em;
        cursor: pointer;
        box-shadow: 0 12px 26px rgba(10,132,255,0.24);
    }

    .btn-login:hover {
        filter: brightness(1.04);
    }
</style>

<div class="login-shell">
    <div class="login-card">
        <div class="login-logo">CT</div>

        <h1 class="login-title">Вход</h1>
        <p class="login-subtitle">
            Панель анализа конкурентных тарифов TENET
        </p>

        <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'options' => ['autocomplete' => 'off'],
        ]); ?>

        <?= $form->field($model, 'username')->textInput([
                'autofocus' => true,
                'placeholder' => 'Введите логин',
        ]) ?>

        <?= $form->field($model, 'password')->passwordInput([
                'placeholder' => 'Введите пароль',
        ]) ?>

        <?= $form->field($model, 'rememberMe')->checkbox() ?>

        <div class="form-group">
            <?= Html::submitButton('Войти', [
                    'class' => 'btn-login',
                    'name' => 'login-button',
            ]) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>