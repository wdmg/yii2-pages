<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model wdmg\pages\models\Pages */

$this->title = Yii::t('app/modules/pages', 'Create page');
$this->params['breadcrumbs'][] = ['label' => $this->context->module->name, 'url' => ['pages/index']];
$this->params['breadcrumbs'][] = $this->title;

$flag = '';
if ($model->locale && isset(Yii::$app->translations) && class_exists('\wdmg\translations\FlagsAsset')) {
    $bundle = \wdmg\translations\FlagsAsset::register(Yii::$app->view);
    $locale = Yii::$app->translations->parseLocale($model->locale, Yii::$app->language);
    if ($model->locale === $locale['locale']) { // Fixing default locale from PECL intl
        if (!($country = $locale['domain']))
            $country = '_unknown';

        $flag = '<sup>' . \yii\helpers\Html::img($bundle->baseUrl . '/flags-iso/flat/24/' . $country . '.png', [
            'title' => $locale['name']
        ]) . '</sup>';
    }
} else {
    if (extension_loaded('intl'))
        $language = mb_convert_case(trim(\Locale::getDisplayLanguage($model->locale, Yii::$app->language)), MB_CASE_TITLE, "UTF-8");
    else
        $language = $model->locale;

    $flag = '<sup><small class="text-muted">[' . $language . ']</small></sup>';
}

?>
<div class="page-header">
    <h1><?= Html::encode($this->title) . $flag ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small></h1>
</div>
<div class="pages-create">
    <?= $this->render('_form', [
        'model' => $model,
        'statusModes' => $model->getStatusesList(),
        'languagesList' => $model->getLanguagesList(false),
        'parentsList' => $model->getParentsList(false, true)
    ]); ?>
</div>