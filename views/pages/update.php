<?php
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $model wdmg\pages\models\Pages */

$this->title = Yii::t('app/modules/pages', 'Updating page: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/pages', 'All pages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app/modules/pages', 'Edit');

?>
<div class="page-header">
    <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small></h1>
</div>
<div class="pages-update">
    <?= $this->render('_form', [
        'model' => $model,
        'statusModes' => $model->getStatusesList(),
        'parentsList' => $model->getParentsList(false, true)
    ]) ?>
</div>