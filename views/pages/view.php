<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model wdmg\pages\models\Pages */

$this->title = Yii::t('app/modules/pages', 'View page');
$this->params['breadcrumbs'][] = ['label' => $this->context->module->name, 'url' => ['pages/index']];
$this->params['breadcrumbs'][] = $this->title;

$bundle = false;
if ($model->locale && isset(Yii::$app->translations) && class_exists('\wdmg\translations\FlagsAsset')) {
    $bundle = \wdmg\translations\FlagsAsset::register(Yii::$app->view);
}

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
                'contentOptions' => [
                    'lang' => ($model->locale ?? Yii::$app->language)
                ],
                'value' => function($model) {
                    $output = Html::tag('strong', $model->name);
                    if (($pageURL = $model->getPageUrl(true, true)) && $model->id) {
                        $output .= '<br/>' . Html::a($model->getUrl(true), $pageURL, [
                            'target' => '_blank',
                            'data-pjax' => 0
                        ]);
                    }

                    if (isset(Yii::$app->redirects) && $model->url && ($model->status == $model::STATUS_PUBLISHED)) {
                        if ($url = Yii::$app->redirects->check($model->url, false)) {
                            $output .= '&nbsp' . Html::tag('span', '', [
                                'class' => "text-danger fa fa-exclamation-circle",
                                'data' => [
                                    'toggle' => "tooltip",
                                    'placement' => "top"
                                ],
                                'title' => Yii::t('app/modules/redirects', 'For this URL is active redirect to {url}', [
                                    'url' => $url
                                ])
                            ]);
                        }
                    }
                    return $output;
                }
            ],
            [
                'attribute' => 'title',
                'format' => 'ntext',
                'contentOptions' => [
                    'lang' => ($model->locale ?? Yii::$app->language)
                ]
            ],
            [
                'attribute' => 'content',
                'format' => 'html',
                'contentOptions' => [
                    'lang' => ($model->locale ?? Yii::$app->language),
                    'style' => 'display:inline-block;max-height:360px;overflow-x:auto;'
                ]
            ],
            [
                'attribute' => 'description',
                'format' => 'ntext',
                'contentOptions' => [
                    'lang' => ($model->locale ?? Yii::$app->language)
                ]
            ],
            [
                'attribute' => 'keywords',
                'format' => 'ntext',
                'contentOptions' => [
                    'lang' => ($model->locale ?? Yii::$app->language)
                ]
            ],
            [
                'attribute' => 'common',
                'label' => Yii::t('app/modules/pages','Common'),
                'format' => 'html',
                'value' => function($data) {
                    $output = '';
                    if ($data->in_sitemap)
                        $output .= '<span class="fa fa-fw fa-sitemap text-success" title="' . Yii::t('app/modules/pages','Present in sitemap') . '"></span>';
                    else
                        $output .= '<span class="fa fa-fw fa-sitemap text-danger" title="' . Yii::t('app/modules/pages','Not present in sitemap') . '"></span>';

                    $output .= "&nbsp;";

                    if ($data->in_turbo)
                        $output .= '<span class="fa fa-fw fa-rocket text-success" title="' . Yii::t('app/modules/pages','Present in Yandex.Turbo') . '"></span>';
                    else
                        $output .= '<span class="fa fa-fw fa-rocket text-danger" title="' . Yii::t('app/modules/pages','Not present in Yandex.Turbo') . '"></span>';

                    $output .= "&nbsp;";

                    if ($data->in_amp)
                        $output .= '<span class="fa fa-fw fa-bolt text-success" title="' . Yii::t('app/modules/pages','Present in Google AMP') . '"></span>';
                    else
                        $output .= '<span class="fa fa-fw fa-bolt text-danger" title="' . Yii::t('app/modules/pages','Not present in Google AMP') . '"></span>';

                    return $output;
                }
            ],
            [
                'attribute' => 'locale',
                'label' => Yii::t('app/modules/pages','Language'),
                'format' => 'raw',
                'value' => function($data) use ($bundle) {
                    if ($data->locale) {
                        if ($bundle) {
                            $locale = Yii::$app->translations->parseLocale($data->locale, Yii::$app->language);
                            if ($data->locale === $locale['locale']) { // Fixing default locale from PECL intl
                                if (!($country = $locale['domain']))
                                    $country = '_unknown';

                                $flag = \yii\helpers\Html::img($bundle->baseUrl . '/flags-iso/flat/24/' . $country . '.png', [
                                    'title' => $locale['name']
                                ]);
                                return $flag . " " . $locale['name'];
                            }
                        } else {
                            if (extension_loaded('intl'))
                                $language = mb_convert_case(trim(\Locale::getDisplayLanguage($data->locale, Yii::$app->language)), MB_CASE_TITLE, "UTF-8");
                            else
                                $language = $data->locale;

                            return $language;
                        }
                    }
                    return null;
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
                    elseif (isset($this->context->module->baseRoute))
                        return ((is_array($this->context->module->baseRoute)) ? array_shift($this->context->module->baseRoute) : $this->context->module->baseRoute) .'&nbsp;'. Html::tag('span', Yii::t('app/modules/pages','by default'), ['class' => 'label label-default']);
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
                    elseif (isset($this->context->module->baseLayout))
                        return $this->context->module->baseLayout .'&nbsp;'. Html::tag('span', Yii::t('app/modules/pages','by default'), ['class' => 'label label-default']);
                    else
                        return null;
                }
            ],

            [
                'attribute' => 'created',
                'label' => Yii::t('app/modules/pages','Created'),
                'format' => 'html',
                'value' => function($data) {

                    $output = "";
                    if ($user = $data->createdBy) {
                        $output = Html::a($user->username, ['../admin/users/view/?id='.$user->id], [
                            'target' => '_blank',
                            'data-pjax' => 0
                        ]);
                    } else if ($data->created_by) {
                        $output = $data->created_by;
                    }

                    if (!empty($output))
                        $output .= ", ";

                    $output .= Yii::$app->formatter->format($data->updated_at, 'datetime');
                    return $output;
                }
            ],
            [
                'attribute' => 'updated',
                'label' => Yii::t('app/modules/pages','Updated'),
                'format' => 'html',
                'value' => function($data) {

                    $output = "";
                    if ($user = $data->updatedBy) {
                        $output = Html::a($user->username, ['../admin/users/view/?id='.$user->id], [
                            'target' => '_blank',
                            'data-pjax' => 0
                        ]);
                    } else if ($data->updated_by) {
                        $output = $data->updated_by;
                    }

                    if (!empty($output))
                        $output .= ", ";

                    $output .= Yii::$app->formatter->format($data->updated_at, 'datetime');
                    return $output;
                }
            ],
        ],
    ]); ?>
    <hr/>
    <div class="form-group">
        <?= Html::a(Yii::t('app/modules/pages', '&larr; Back to list'), ['pages/index'], ['class' => 'btn btn-default pull-left']) ?>&nbsp;
        <?php if (Yii::$app->authManager && $this->context->module->moduleExist('rbac') && Yii::$app->user->can('updatePosts', [
                'created_by' => $model->created_by,
                'updated_by' => $model->updated_by
            ])) : ?>
            <div class="form-group pull-right">
                <?= Html::a(Yii::t('app/modules/pages', 'Delete'), ['pages/delete', 'id' => $model->id], [
                    'class' => 'btn btn-delete btn-danger',
                    'data-confirm' => Yii::t('app/modules/pages', 'Are you sure you want to delete this page?'),
                    'data-method' => 'post',
                ]) ?>
                <?= Html::a(Yii::t('app/modules/pages', 'Update'), ['pages/update', 'id' => $model->id], ['class' => 'btn btn-edit btn-primary']) ?>
            </div>
        <?php endif; ?>
    </div>
</div>