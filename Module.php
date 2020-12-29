<?php

namespace wdmg\pages;

/**
 * Yii2 Pages
 *
 * @category        Module
 * @version         1.2.5
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-pages
 * @copyright       Copyright (c) 2019 - 2020 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use wdmg\translations\components\UrlManager;
use Yii;
use wdmg\base\BaseModule;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Pages module definition class
 */
class Module extends BaseModule
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'wdmg\pages\controllers';

    /**
     * {@inheritdoc}
     */
    public $defaultRoute = "pages/index";

    /**
     * @var string, the name of module
     */
    public $name = "Pages";

    /**
     * @var string, the description of module
     */
    public $description = "Static Page Manager";

    /**
     * @var string the module version
     */
    private $version = "1.2.5";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 2;

    /**
     * @var string or array, the default routes to rendered page (use "/" - for root)
     */
    public $baseRoute = "/pages";

    /**
     * @var string, the default controller for pages in @frontend
     */
    public $defaultController = "admin/pages/default";

    /**
     * @var string, the default layout to rendered page
     */
    public $baseLayout = "@app/views/layouts/main";

    /**
     * @var array, the list of support locales for multi-language versions of page.
     * @note This variable will be override if you use the `wdmg\yii2-translations` module.
     */
    public $supportLocales = ['ru-RU', 'uk-UA', 'en-US'];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Set version of current module
        $this->setVersion($this->version);

        // Set priority of current module
        $this->setPriority($this->priority);

        if (isset(Yii::$app->params["pages.baseRoute"]))
            $this->baseRoute = Yii::$app->params["pages.baseRoute"];

        if (isset(Yii::$app->params["pages.defaultController"]))
            $this->defaultController = Yii::$app->params["pages.defaultController"];

        if (isset(Yii::$app->params["pages.supportLocales"]))
            $this->supportLocales = Yii::$app->params["pages.supportLocales"];

        if (!isset($this->baseRoute))
            throw new InvalidConfigException("Required module property `baseRoute` isn't set.");

        // Process and normalize route for news in frontend
        $this->baseRoute = self::normalizeRoute($this->baseRoute);

    }

    /**
     * Normalization to normal path/route
     *
     * @param string or array $routes
     * @return string or array of normalized route`s
     */
    public function normalizePagesRoute($routes)
    {
        if (is_array($routes)) {
            $routes = array_unique($routes);
            foreach ($routes as &$route) {
                $route = self::normalizeRoute($route);
            }
        } else {
            $routes = self::normalizeRoute($routes);
        }
        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function dashboardNavItems($createLink = false)
    {
        $items = [
            'label' => $this->name,
            'url' => [$this->routePrefix . '/'. $this->id],
            'icon' => 'fa fa-fw fa-layer-group',
            'active' => in_array(\Yii::$app->controller->module->id, [$this->id])
        ];
        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        parent::bootstrap($app);

        if (!$this->isBackend() && !is_null($this->defaultController)) {

            // Get language scheme if available
            $custom = false;
            $hide = false;
            $scheme = null;
            if (isset(Yii::$app->translations)) {
                $custom = true;
                $hide = Yii::$app->translations->module->hideDefaultLang;
                $scheme = Yii::$app->translations->module->languageScheme;
            }

            $baseRoute = ltrim($this->baseRoute, '/');

            // Add routes for frontend
            switch ($scheme) {
                case "after":

                    $app->getUrlManager()->addRules([
                        '/<route:' . $baseRoute . '.*?>/<alias:[\w-]+>/<lang:\w+>' => $this->defaultController . '/view',
                    ], true);

                    if ($hide) {
                        $app->getUrlManager()->addRules([
                            '/<route:' . $baseRoute . '.*?>/<alias:[\w-]+>' => $this->defaultController . '/view',
                        ], true);
                    }

                    break;

                case "query":

                    $app->getUrlManager()->addRules([
                        '/<route:' . $baseRoute . '.*?>/<alias:[\w-]+>' => $this->defaultController . '/view',
                    ], true);

                    /*if ($hide) {

                    }*/

                    break;

                case "subdomain":

                    if ($host = $app->getRequest()->getHostName()) {
                        $app->getUrlManager()->addRules([
                            'http(s)?://' . $host. '/<route:' . $baseRoute . '.*?>/<alias:[\w-]+>' => $this->defaultController . '/view',
                        ], true);
                    }

                    /*if ($hide) {

                    }*/

                    break;

                default:

                    $app->getUrlManager()->addRules([
                        '/<lang:\w+>/<route:' . $baseRoute . '.*?>/<alias:[\w-]+>' => $this->defaultController . '/view',
                    ], true);

                    if ($hide || !$custom) {
                        $app->getUrlManager()->addRules([
                            '/<route:' . $baseRoute . '.*?>/<alias:[\w-]+>' => $this->defaultController . '/view',
                        ], true);
                    }

                    break;
            }
        }
    }
}