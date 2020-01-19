<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use wdmg\widgets\Editor;
use wdmg\widgets\SelectInput;

/* @var $this yii\web\View */
/* @var $model wdmg\pages\models\Pages */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pages-form">
    <?php $form = ActiveForm::begin([
        'id' => "addPageForm",
        'enableAjaxValidation' => true
    ]); ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?php
        if (($pageURL = $model->getPageUrl()) && $model->id)
            echo  Html::tag('label', Yii::t('app/modules/pages', 'Page URL')) . Html::tag('fieldset', Html::a($pageURL, $pageURL, [
                'target' => '_blank',
                'data-pjax' => 0
            ])) . '<br/>';
    ?>
    <?= $form->field($model, 'alias')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'content')->widget(Editor::class, [
        'options' => [],
        'pluginOptions' => []
    ]) ?>
    <?= $form->field($model, 'title')->textInput() ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>
    <?= $form->field($model, 'keywords')->textarea(['rows' => 3]) ?>

    <?= $form->field($model, 'in_sitemap', [
        'template' => "{label}\n<br/>{input}\n{hint}\n{error}"
    ])->checkbox(['label' => Yii::t('app/modules/pages', '- display in the sitemap')])->label(Yii::t('app/modules/pages', 'Sitemap'))
    ?>
    <?= $form->field($model, 'in_turbo', [
        'template' => "{label}\n<br/>{input}\n{hint}\n{error}"
    ])->checkbox(['label' => Yii::t('app/modules/pages', '- display in the turbo-pages')])->label(Yii::t('app/modules/pages', 'Yandex turbo'))
    ?>
    <?= $form->field($model, 'in_amp', [
        'template' => "{label}\n<br/>{input}\n{hint}\n{error}"
    ])->checkbox(['label' => Yii::t('app/modules/pages', '- display in the AMP pages')])->label(Yii::t('app/modules/pages', 'Google AMP'))
    ?>

    <?= $form->field($model, 'status')->widget(SelectInput::class, [
        'items' => $statusModes,
        'options' => [
            'class' => 'form-control'
        ]
    ]); ?>

    <?= $form->field($model, 'parent_id')->widget(SelectInput::class, [
        'items' => $parentsList,
        'options' => [
            'class' => 'form-control'
        ]
    ]); ?>

    <?= $form->field($model, 'route')->textInput([
        'placeholder' => (is_null($model->route)) ? ((is_array($this->context->module->pagesRoute)) ? array_shift($this->context->module->pagesRoute) : $this->context->module->pagesRoute) : false,
        'disabled' => (!is_null($model->parent_id)) ? true : false
    ]) ?>

    <?= $form->field($model, 'layout')->textInput(['placeholder' => (is_null($model->layout)) ? ((isset($this->context->module->pagesLayout)) ? $this->context->module->pagesLayout : '') : false]) ?>
    <hr/>
    <div class="form-group">
        <?= Html::a(Yii::t('app/modules/pages', '&larr; Back to list'), ['pages/index'], ['class' => 'btn btn-default pull-left']) ?>&nbsp;
        <?= Html::submitButton(Yii::t('app/modules/pages', 'Save'), ['class' => 'btn btn-success pull-right']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php $this->registerJs(<<< JS
$(document).ready(function() {
    function afterValidateAttribute(event, attribute, messages)
    {
        if (attribute.name && !attribute.alias && messages.length == 0) {
            var form = $(event.target);
            $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: form.serializeArray(),
                }
            ).done(function(data) {
                if (data.alias && form.find('#pages-alias').val().length == 0) {
                    form.find('#pages-alias').val(data.alias);
                    form.yiiActiveForm('validateAttribute', 'pages-alias');
                }
            }).fail(function () {
                /*form.find('#options-type').val("");
                form.find('#options-type').trigger('change');*/
            });
            return false; // prevent default form submission
        }
    }
    $("#addPageForm").on("afterValidateAttribute", afterValidateAttribute);
});
JS
); ?>