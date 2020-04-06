<?php

use yii\bootstrap\Button;
use yii\bootstrap\ButtonGroup;
use yii\helpers\ArrayHelper;
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

    <?php
        $buttons = [];

        if ($model->source_id)
            $versions = $model->getAllVersions($model->source_id, true);
        else
            $versions = $model->getAllVersions($model->id, true);

        if ($versions) {
            $existing = ArrayHelper::map($versions, 'id', 'locale');
        } else {
            $existing = [];
        }

        if (isset(Yii::$app->translations) && class_exists('wdmg\translations\models\Languages')) {

            $locales = Yii::$app->translations->getLocales(false, false, true);
            $locales = ArrayHelper::map($locales, 'id', 'locale');

            $bundle = \wdmg\translations\FlagsAsset::register(Yii::$app->view);

            // List of current language version of page (include source page)
            if (is_array($versions)) {
                foreach ($versions as $version) {

                    $locale = Yii::$app->translations->parseLocale($version['locale'], Yii::$app->language);
                    if (!($country = $locale['domain']))
                        $country = '_unknown';

                    $flag = \yii\helpers\Html::img($bundle->baseUrl . '/flags-iso/flat/24/' . $country . '.png', [
                        'alt' => $locale['name']
                    ]);

                    $buttons[] = Button::widget([
                        'label' => $flag . '&nbsp;' . $locale['name'],
                        'tagName' => 'a',
                        'encodeLabel' => false,
                        'options' => [
                            'class' => 'btn btn-sm btn-edit ' . (($model->locale == $locale['locale']) ? 'btn-primary' : 'btn-default'),
                            'href' => $url = Url::to(['pages/update', 'id' => $version['id']]),
                            'title' => Yii::t('app/modules/pages', 'Edit language version: {language}', [
                                'language' => $locale['name']
                            ]),
                            'data-pjax' => 0
                        ]
                    ]);
                }
            }
            // List of available languages for add (exluding already existing)
            foreach ($locales as $item) {

                $locale = Yii::$app->translations->parseLocale($item, Yii::$app->language);
                if ($item === $locale['locale']) { // Fixing default locale from PECL intl
                    if (!($country = $locale['domain']))
                        $country = '_unknown';

                    $flag = \yii\helpers\Html::img($bundle->baseUrl . '/flags-iso/flat/24/' . $country . '.png', [
                        'alt' => $locale['name']
                    ]);

                    if (!in_array($locale['locale'], $existing, true)) {
                        $buttons[] = Button::widget([
                            'label' => $flag . '&nbsp;' . $locale['name'],
                            'tagName' => 'a',
                            'encodeLabel' => false,
                            'options' => [
                                'class' => 'btn btn-sm btn-add ' . (($this->context->getLocale() == $locale['locale']) ? 'btn-primary' : 'btn-default'),
                                'href' => Url::to(['pages/create', 'source_id' => (($model->source_id) ? $model->source_id : $model->id), 'locale' => $locale['locale']]),
                                'title' => Yii::t('app/modules/pages', 'Add language version: {language}', [
                                    'language' => $locale['name']
                                ]),
                                'data-pjax' => 0
                            ]
                        ]);
                    }
                }
            }
        } else {

            // List of current language version of page (include source page)
            if (is_array($versions)) {
                foreach ($versions as $version) {

                    if (extension_loaded('intl'))
                        $language = mb_convert_case(trim(\Locale::getDisplayLanguage($version['locale'], Yii::$app->language)), MB_CASE_TITLE, "UTF-8");
                    else
                        $language = $version['locale'];

                    $buttons[] = Button::widget([
                        'label' => $language,
                        'tagName' => 'a',
                        'encodeLabel' => false,
                        'options' => [
                            'class' => 'btn btn-sm btn-edit ' . (($model->locale == $version['locale']) ? 'btn-primary' : 'btn-default'),
                            'href' => Url::to(['pages/update', 'id' => $version['id']]),
                            'title' => Yii::t('app/modules/pages', 'Edit language version: {language}', [
                                'language' => $language
                            ]),
                            'data-pjax' => 0
                        ]
                    ]);
                }
            }

            // List of available languages for add (exluding already existing)
            foreach ($this->context->module->supportLocales as $locale) {
                if (!empty($locale)) {
                    if (!array_search($locale, $existing, true)) {

                        if (extension_loaded('intl'))
                            $language = mb_convert_case(trim(\Locale::getDisplayLanguage($locale, Yii::$app->language)), MB_CASE_TITLE, "UTF-8");
                        else
                            $language = $locale;

                        $buttons[] = Button::widget([
                            'label' => $language,
                            'tagName' => 'a',
                            'encodeLabel' => false,
                            'options' => [
                                'class' => 'btn btn-sm btn-add ' . (($this->context->getLocale() == $locale) ? 'btn-primary' : 'btn-default'),
                                'href' => Url::to(['pages/create', 'source_id' => (($model->source_id) ? $model->source_id : $model->id), 'locale' => $locale]),
                                'title' => Yii::t('app/modules/pages', 'Add language version: {language}', [
                                    'language' => $language
                                ]),
                                'data-pjax' => 0
                            ]
                        ]);
                    }

                }
            }
        }

        // Render the locale switcher
        if (!empty($buttons) && is_array($buttons)) {
            echo '<div class="form-group">';

            echo Html::tag('label', Yii::t('app/modules/pages', 'Language version'), [
                'for' => 'locale-switcher',
                'class' => 'control-label',
                'style' => 'display:inline-block;vertical-align:middle;float:none;padding-top:6px;'
            ]);

            echo ButtonGroup::widget([
                'encodeLabels' => false,
                'options' => [
                    'id' => 'locale-switcher',
                    'class' => 'pull-right'
                ],
                'buttons' => $buttons
            ]);
            echo '</div>';
        }
    ?>

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
            'id' => 'page-form-status',
            'class' => 'form-control'
        ]
    ]); ?>

    <?= $form->field($model, 'locale')->widget(SelectInput::class, [
        'items' => $languagesList,
        'options' => [
            'id' => 'page-form-locale',
            'class' => 'form-control'
        ]
    ]); ?>

    <?= $form->field($model, 'parent_id')->widget(SelectInput::class, [
        'items' => $parentsList,
        'options' => [
            'id' => 'page-form-parent',
            'class' => 'form-control',
            'disabled' => (!is_null($model->source_id)) ? true : false
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