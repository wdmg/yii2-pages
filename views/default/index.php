<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model wdmg\pages\models\Pages */

if (empty($model->title))
    $this->title = $model->title;
else
    $this->title = $model->name;

if (!empty($model->description))
    $this->registerMetaTag(['content' => Html::encode($model->description), 'name' => 'description']);

if (!empty($model->keywords))
    $this->registerMetaTag(['content' => Html::encode($model->keywords), 'name' => 'keywords']);


if (isset($model->route))
    $this->registerLinkTag(['rel' => 'canonical', 'href' => Url::to($model->route.'/'.$model->alias, true)]);
elseif (isset($route))
    $this->registerLinkTag(['rel' => 'canonical', 'href' => Url::to($route.'/'.$model->alias, true)]);
else
    $this->registerLinkTag(['rel' => 'canonical', 'href' => Url::canonical()]);

?>

<?= $model->content; ?>