<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wdmg\widgets\Editor;
use wdmg\widgets\SelectInput;

/* @var $this yii\web\View */
/* @var $model wdmg\pages\models\Pages */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tasks-form">
    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'alias')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'content')->widget(Editor::className(), [
        'options' => [],
        'pluginOptions' => []
    ]) ?>
    <?= $form->field($model, 'title')->textInput() ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>
    <?= $form->field($model, 'keywords')->textarea(['rows' => 3]) ?>
    <?= $form->field($model, 'status')->widget(SelectInput::className(), [
        'items' => $statusModes,
        'options' => [
            'class' => 'form-control'
        ]
    ]); ?>
    <?= $form->field($model, 'route')->textInput() ?>
    <?= $form->field($model, 'layout')->textInput() ?>
    <hr/>
    <div class="form-group">
        <?= Html::a(Yii::t('app/modules/pages', '&larr; Back to list'), ['pages/index'], ['class' => 'btn btn-default pull-left']) ?>&nbsp;
        <?= Html::submitButton(Yii::t('app/modules/pages', 'Save'), ['class' => 'btn btn-success pull-right']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
