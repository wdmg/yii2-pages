<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model wdmg\pages\models\Pages */

$this->title = Yii::t('app/modules/pages', 'View page');
$this->params['breadcrumbs'][] = ['label' => $this->context->module->name, 'url' => ['pages/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-header">
    <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small></h1>
</div>
<div class="pages-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function($model) {
                    $output = Html::tag('strong', $model->name);
                    if (($pageURL = $model->getPageUrl()) && $model->id)
                        $output .= '<br/>' . Html::a($pageURL, $pageURL, [
                                'target' => '_blank',
                                'data-pjax' => 0
                            ]);

                    return $output;
                }
            ],
            'title:ntext',
            [
                'attribute' => 'content',
                'format' => 'html',
            ],
            'description:ntext',
            'keywords:ntext',
            [
                'attribute' => 'in_sitemap',
                'format' => 'html',
                'value' => function($data) {
                    if ($data->in_sitemap)
                        return '<span class="fa fa-check text-success"></span>';
                    else
                        return '<span class="fa fa-remove text-danger"></span>';
                }
            ],
            [
                'attribute' => 'in_turbo',
                'format' => 'html',
                'value' => function($data) {
                    if ($data->in_turbo)
                        return '<span class="fa fa-check text-success"></span>';
                    else
                        return '<span class="fa fa-remove text-danger"></span>';
                }
            ],
            [
                'attribute' => 'in_amp',
                'format' => 'html',
                'value' => function($data) {
                    if ($data->in_amp)
                        return '<span class="fa fa-check text-success"></span>';
                    else
                        return '<span class="fa fa-remove text-danger"></span>';
                }
            ],
            [
                'attribute' => 'status',
                'format' => 'html',
                'value' => function($data) {
                    if ($data->status == $data::STATUS_PUBLISHED)
                        return '<span class="label label-success">'.Yii::t('app/modules/pages','Published').'</span>';
                    elseif ($data->status == $data::STATUS_DRAFT)
                        return '<span class="label label-default">'.Yii::t('app/modules/pages','Draft').'</span>';
                    else
                        return $data->status;
                }
            ],
            [
                'attribute' => 'route',
                'format' => 'html',
                'value' => function($data) {

                    if (isset($data->route))
                        return Html::tag('strong', $data->route);
                    elseif (isset($this->context->module->pagesRoute))
                        return ((is_array($this->context->module->pagesRoute)) ? array_shift($this->context->module->pagesRoute) : $this->context->module->pagesRoute) .'&nbsp;'. Html::tag('span', Yii::t('app/modules/pages','by default'), ['class' => 'label label-default']);
                    else
                        return null;
                }
            ],
            [
                'attribute' => 'layout',
                'format' => 'html',
                'value' => function($data) {
                    if (isset($data->layout))
                        return Html::tag('strong', $data->layout);
                    elseif (isset($this->context->module->pagesLayout))
                        return $this->context->module->pagesLayout .'&nbsp;'. Html::tag('span', Yii::t('app/modules/pages','by default'), ['class' => 'label label-default']);
                    else
                        return null;
                }
            ],
            'created_at:datetime',
            'updated_at:datetime'
        ],
    ]); ?>

</div>