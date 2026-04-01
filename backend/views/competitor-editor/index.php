<?php

/** @var yii\web\View $this */
/** @var backend\models\CompetitorEditorForm $model */
/** @var array<int, string> $providerOptions */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Редактор конкурентов';
?>

<div class="competitor-editor-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Html::encode(Yii::$app->session->getFlash('success')) ?>
        </div>
    <?php endif; ?>

    <div class="card" style="padding: 16px; margin-bottom: 20px;">
        <?php $providerSelectForm = ActiveForm::begin([
            'method' => 'get',
            'action' => ['index'],
        ]); ?>

        <?= $providerSelectForm->field($model, 'provider_id')->dropDownList(
            $providerOptions,
            ['prompt' => 'Выберите провайдера']
        ) ?>

        <div class="form-group">
            <?= Html::submitButton('Загрузить данные', ['class' => 'btn btn-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <?php if ($model->provider_id !== null): ?>
        <div class="card" style="padding: 16px;">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'provider_id')->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'category_code')->hiddenInput()->label(false) ?>

            <h3>
                Провайдер:
                <?= Html::encode($model->getProvider()?->name ?? '—') ?>
            </h3>

            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th style="width: 220px;">Тариф</th>
                    <th style="width: 200px;">Цена, грн</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($model->getProducts() as $product): ?>
                    <tr>
                        <td><?= Html::encode($product->name) ?></td>
                        <td>
                            <?= Html::input(
                                'text',
                                "CompetitorEditorForm[prices][{$product->code}]",
                                $model->prices[$product->code] ?? '',
                                [
                                    'class' => 'form-control',
                                    'placeholder' => 'Например, 299',
                                    'onclick' => 'this.select()',
                                ]
                            ) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?= $form->field($model, 'promotion_text')->textarea([
                'rows' => 4,
                'placeholder' => 'Текст акций',
            ]) ?>

            <?= $form->field($model, 'loyalty_text')->textarea([
                'rows' => 4,
                'placeholder' => 'Текст лояльности',
            ]) ?>

            <?= $form->field($model, 'editor_note')->textarea([
                'rows' => 4,
                'placeholder' => 'Внутренняя заметка редактора',
            ]) ?>

            <?= $form->field($model, 'comment')->textInput([
                'placeholder' => 'Комментарий к сохранению ревизии',
            ]) ?>

            <div class="form-group">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    <?php endif; ?>
</div>