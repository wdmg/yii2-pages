<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model wdmg\pages\models\Pages */

$this->title = Yii::t('app/modules/pages', 'Create page');
$this->params['breadcrumbs'][] = ['label' => $this->context->module->name, 'url' => ['pages/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-header">
    <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small></h1>
</div>
<div class="pages-create">
    <?= $this->render('_form', [
        'model' => $model,
        'statusModes' => $model->getStatusesList(),
        'parentsList' => $model->getParentsList(false, true)
    ]); ?>
</div>