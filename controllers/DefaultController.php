<?php

namespace wdmg\pages\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use wdmg\pages\models\Pages;

/**
 * DefaultController implements actions for Pages model.
 */
class DefaultController extends Controller
{


    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {

        // Set a default layout
        $this->layout = $this->module->pagesLayout;

        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

    /**
     * View of page.
     * If the page was found and it has a route setup that does not match the current route
     * of the request, an NotFoundHttpException will be thrown.
     * If the page does not have a route, such a check is not performed and the page can be
     * displayed if such a route is allowed as the default setting in the module.
     *
     * @param $page
     * @param null $route
     * @param bool $draft
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex($page, $route = null, $draft = false)
    {

        $module = $this->module;

        // Check probably need redirect to new page URL
        if (isset(Yii::$app->redirects)) {
            if (Yii::$app->redirects->check(Yii::$app->request->getUrl()))
                return Yii::$app->redirects->check(Yii::$app->request->getUrl());
        }

        // Separate route from request URL
        if (is_null($route) && preg_match('/^([\/]+[A-Za-z0-9_\-\_\/]+[\/])*([A-Za-z0-9_\-\_]*)/i', Yii::$app->request->url, $matches)) {
            if ($page == $matches[2])
                $route = rtrim($matches[1], '/');
        } else {
            // Normalize route
            $normalizer = new \yii\web\UrlNormalizer();
            $route = $normalizer->normalizePathInfo($route, '');
            $route = '/' . $route;
        }

        // If route is root
        if (empty($route))
            $route = '/';

        // Add default route to path
        if ($module->pagesRoute)
            $route = $module->pagesRoute . $route;

        // Search page model with alias
        if (!($model = $this->findModel($page, $route, $draft)))
            throw new NotFoundHttpException();

        // Checking requested route with page route if set
        if (isset($model->route)) {
            if ($model->route !== $route) {
                throw new NotFoundHttpException();
            }
        }

        // Set a custom layout to render page
        if (isset($model->layout))
            $this->layout = $model->layout;

        return $this->render('index', [
            'model' => $model,
            'route' => $route
        ]);
    }

    /**
     * Finds the Page model based on alias and route values.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param $alias
     * @param null $route
     * @param bool $draft
     * @return array|null|Pages|\yii\db\ActiveRecord
     * @throws NotFoundHttpException
     */
    protected function findModel($alias, $route = null, $draft = false)
    {
        if (!is_null($route)) {
            $model = Pages::find()->where([
                'alias' => $alias,
                'route' => $route,
                'status' => ($draft) ? 0 : 1,
            ])->one();
        } else {
            $model = Pages::find()->where([
                'alias' => $alias,
                'status' => ($draft) ? 0 : 1,
            ])->one();
        }

        if (!is_null($model))
            return $model;
        else
            throw new NotFoundHttpException();

    }
}
