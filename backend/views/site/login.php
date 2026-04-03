<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Ласкаво просимо'; $this->params['breadcrumbs'][] = $this->title;
?>


<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="blob blob-3"></div>


<div class="site-login">
    <div class="wrap-login">
        <img src="/img/logo.svg" alt="">
        <h1><?= Html::encode($this->title) ?></h1>


        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

        <?= $form->field($model, 'username')
                ->textInput(['autofocus' => true])
                ->label(Yii::t('app', 'Логін'))
        ?>

        <?= $form->field($model, 'password')
                ->passwordInput()
                ->label(Yii::t('app', 'Пароль'))
        ?>

        <?=
        $form->field($model, 'rememberMe')
                ->checkbox()
                ->label(Yii::t('app', 'Запамʼятати'))
        ?>

        <div class="form-group">
            <?= Html::submitButton('Войти', ['class' => 'btn btn-primary', 'name'
            => 'login-button']) ?>
        </div>
        <div class="password-reset">
            <?= Html::a('Забули логін або пароль',
                    ['site/request-password-reset']) ?>.
            <!-- <br> -->
            <!-- Need new verification email? <?= Html::a('Resend', ['site/resend-verification-email']) ?> -->
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>
