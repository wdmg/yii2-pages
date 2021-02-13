<?php

use wdmg\widgets\AliasInput;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use wdmg\widgets\Editor;
use wdmg\widgets\SelectInput;
use wdmg\widgets\LangSwitcher;

/* @var $this yii\web\View */
/* @var $model wdmg\pages\models\Pages */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pages-form">
    <?php
        echo LangSwitcher::widget([
            'label' => Yii::t('app/modules/pages', 'Language version'),
            'model' => $model,
            'renderWidget' => 'button-group',
            'createRoute' => 'pages/create',
            'updateRoute' => 'pages/update',
            'supportLocales' => $this->context->module->supportLocales,
            //'currentLocale' => $this->context->getLocale(),
            'versions' => (isset($model->source_id)) ? $model->getAllVersions($model->source_id, true) : $model->getAllVersions($model->id, true),
            'options' => [
                'id' => 'locale-switcher',
                'class' => 'pull-right'
            ]
        ]);
    ?>

    <?php $form = ActiveForm::begin([
        'id' => "addPageForm",
        'enableAjaxValidation' => true
    ]); ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'lang' => ($model->locale ?? Yii::$app->language)]) ?>

    <?= $form->field($model, 'alias')->widget(AliasInput::class, [
        'labels' => [
            'edit' => Yii::t('app/modules/pages', 'Edit'),
            'save' => Yii::t('app/modules/pages', 'Save')
        ],
        'options' => [
            'baseUrl' => ($model->id) ? $model->url : Url::to($model->getRoute(), true)
        ]
    ])->label(Yii::t('app/modules/pages', 'Page URL')); ?>

    <?php
        if (isset(Yii::$app->redirects) && $model->url && ($model->status == $model::STATUS_PUBLISHED)) {
            if ($url = Yii::$app->redirects->check($model->url, false)) {
                echo Html::tag('div', Yii::t('app/modules/redirects', 'For this URL is active redirect to {url}', [
                    'url' => $url
                ]), [
                    'class' => "alert alert-warning"
                ]);
            }
        }
    ?>

    <?= $form->field($model, 'content')->widget(Editor::class, [
        'options' => [
            'lang' => ($model->locale ?? Yii::$app->language)
        ],
        'pluginOptions' => []
    ]) ?>

    <?= $form->field($model, 'locale')->widget(SelectInput::class, [
        'items' => $languagesList,
        'options' => [
            'id' => 'page-form-locale',
            'class' => 'form-control'
        ]
    ])->label(Yii::t('app/modules/pages', 'Language')); ?>

    <?= $form->field($model, 'parent_id')->widget(SelectInput::class, [
        'items' => $parentsList,
        'options' => [
            'id' => 'page-form-parent',
            'class' => 'form-control',
            'disabled' => (count($parentsList) <=1 ) ? true : ((!is_null($model->source_id)) ? true : false)
        ]
    ]); ?>

    <?= $form->field($model, 'status')->widget(SelectInput::class, [
        'items' => $statusModes,
        'options' => [
            'id' => 'page-form-status',
            'class' => 'form-control'
        ]
    ]); ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h6 class="panel-title">
                <a data-toggle="collapse" href="#pageMetaTags">
                    <?= Yii::t('app/modules/pages', "SEO") ?>
                </a>
            </h6>
        </div>
        <div id="pageMetaTags" class="panel-collapse collapse">
            <div class="panel-body">
                <?= $form->field($model, 'title')->textInput(['lang' => ($model->locale ?? Yii::$app->language)]) ?>
                <?= $form->field($model, 'description')->textarea(['rows' => 3, 'lang' => ($model->locale ?? Yii::$app->language)]) ?>
                <?= $form->field($model, 'keywords')->textarea(['rows' => 3, 'lang' => ($model->locale ?? Yii::$app->language)]) ?>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h6 class="panel-title">
                <a data-toggle="collapse" href="#pageOptions">
                    <?= Yii::t('app/modules/pages', "Other options") ?>
                </a>
            </h6>
        </div>
        <div id="pageOptions" class="panel-collapse collapse">
            <div class="panel-body">

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

                <?= $form->field($model, 'route')->textInput([
                    'placeholder' => (is_null($model->route)) ? ((is_array($this->context->module->baseRoute)) ? array_shift($this->context->module->baseRoute) : $this->context->module->baseRoute) : false,
                    'disabled' => (!is_null($model->parent_id)) ? true : false
                ]) ?>

                <?= $form->field($model, 'layout')->textInput(['placeholder' => (is_null($model->layout)) ? ((isset($this->context->module->baseLayout)) ? $this->context->module->baseLayout : '') : false]) ?>

            </div>
        </div>
    </div>

    <hr/>
    <div class="form-group">
        <?= Html::a(Yii::t('app/modules/pages', '&larr; Back to list'), ['pages/index'], ['class' => 'btn btn-default pull-left']) ?>&nbsp;
        <?php if ((Yii::$app->authManager && $this->context->module->moduleExist('rbac') && Yii::$app->user->can('updatePosts', [
                'created_by' => $model->created_by,
                'updated_by' => $model->updated_by
            ])) || !$model->id) : ?>
            <?= Html::submitButton(Yii::t('app/modules/pages', 'Save'), ['class' => 'btn btn-save btn-success pull-right']) ?>
        <?php endif; ?>
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
                        form.find('#pages-alias').change();
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