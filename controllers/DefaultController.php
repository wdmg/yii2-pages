<?php

namespace wdmg\pages\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use wdmg\pages\models\Pages;

/**
 * DefaultController implements actions for Pages model.
 */
class DefaultController extends Controller
{

    public $defaultAction = 'view';

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        // Set a default layout
        $this->layout = $this->module->pagesLayout;

        return parent::beforeAction($action);
    }

    /**
     * View of page.
     * If the page was found and it has a route setup that does not match the current route
     * of the request, an NotFoundHttpException will be thrown.
     * If the page does not have a route, such a check is not performed and the page can be
     * displayed if such a route is allowed as the default setting in the module.
     *
     * @param $alias
     * @param null $route
     * @param null $lang
     * @param bool $draft
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionView($alias, $route = null, $lang = null, $draft = false)
    {

        // Check probably need redirect to new page URL
        if (isset(Yii::$app->redirects)) {
            if (Yii::$app->redirects->check(Yii::$app->request->getUrl()))
                return Yii::$app->redirects->check(Yii::$app->request->getUrl());
        }

        // Separate route from request URL
        if (is_null($route) && preg_match('/^([\/]+[A-Za-z0-9_\-\_\/]+[\/])*([A-Za-z0-9_\-\_]*)/i', Yii::$app->request->url, $matches)) {
            if ($alias == $matches[2])
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

        // Search page model with alias
        if (!($model = $this->findModel($alias, $route, $lang, $draft)))
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
     * @param null $lang
     * @param bool $draft
     * @return Pages|null
     * @throws NotFoundHttpException
     */
    protected function findModel($alias, $route = null, $lang = null, $draft = false)
    {
        $locale = null;
        if (!is_null($lang)) {
            $locales = [];
            if (isset(Yii::$app->translations) && class_exists('wdmg\translations\models\Languages')) {
                $locales = Yii::$app->translations->getLocales(true, true, true);
                $locales = ArrayHelper::map($locales, 'url', 'locale');
            } else {
                if (is_array($this->module->supportLocales)) {
                    $supportLocales = $this->module->supportLocales;
                    foreach ($supportLocales as $locale) {
                        if ($lang === \Locale::getPrimaryLanguage($locale)) {
                            $locales[$lang] = $locale;
                            break;
                        }
                    }
                }
            }
            if (isset($locales[$lang])) {
                $locale = $locales[$lang];
            }
        }

        // Throw an exception if a page with a language locale was requested,
        // which is unavailable or disabled for display in the frontend
        if (!is_null($lang) && is_null($locale)) {
            throw new NotFoundHttpException(Yii::t('app/modules/pages', 'The requested page does not exist.'));
        }

        if (!$draft) {
            $cond = [
                'alias' => $alias,
                'route' => $route,
                'locale' => ($locale) ? $locale : null,
                'status' => 1,
            ];
        } else {
            $cond = [
                'alias' => $alias,
                'route' => $route,
                'status' => 0,
            ];
        }

        if (($model = Pages::getPublished($cond, false, true)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app/modules/pages', 'The requested page does not exist.'));
        }
    }
}
