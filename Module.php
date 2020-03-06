<?php

namespace wdmg\pages;

/**
 * Yii2 Pages
 *
 * @category        Module
 * @version         1.1.9
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-pages
 * @copyright       Copyright (c) 2019 - 2020 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

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
    public $description = "Static pages manager";

    /**
     * @var string the module version
     */
    private $version = "1.1.9";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 2;

    /**
     * @var string or array, the default routes to rendered page (use "/" - for root)
     */
    public $pagesRoute = "/pages";

    /**
     * @var string, the default layout to rendered page
     */
    public $pagesLayout = "@app/views/layouts/main";

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

        // Process and normalize route for pages in frontend
        $model = new \wdmg\pages\models\Pages();
        $pages = $model->getRoutes(true);
        $this->pagesRoute = ArrayHelper::merge(
            is_array($this->pagesRoute) ? $this->pagesRoute : [$this->pagesRoute],
            array_unique(ArrayHelper::getColumn($pages, 'route'))
        );
        $this->pagesRoute = self::normalizePagesRoute($this->pagesRoute);
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
            foreach ($routes as $route) {
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

        if (isset(Yii::$app->params["pages.pagesRoute"]))
            $this->pagesRoute = Yii::$app->params["pages.pagesRoute"];

        if (!isset($this->pagesRoute))
            throw new InvalidConfigException("Required module property `pagesRoute` isn't set.");

        // Add routes to pages in frontend
        $pagesRoute = $this->pagesRoute;
        if (empty($pagesRoute) || $pagesRoute == "/") {
            $app->getUrlManager()->addRules([
                [
                    'pattern' => '/<route:[\w-\/]+>/<page:[\w-]+>',
                    'route' => 'admin/pages/default',
                    'suffix' => ''
                ],
                '/<route:[\w-\/]+>/<page:[\w-]+>' => 'admin/pages/default',
            ], true);
        } else if (is_string($pagesRoute)) {
            $app->getUrlManager()->addRules([
                [
                    'pattern' => $pagesRoute . '/<page:[\w-]+>',
                    'route' => 'admin/pages/default',
                    'suffix' => ''
                ],
                $pagesRoute . '/<page:[\w-]+>' => 'admin/pages/default'
            ], true);
            $app->getUrlManager()->addRules([
                [
                    'pattern' => $pagesRoute . '/<route:[\w-\/]+>/<page:[\w-]+>',
                    'route' => 'admin/pages/default',
                    'suffix' => ''
                ],
                $pagesRoute . '/<route:[\w-\/]+>/<page:[\w-]+>' => 'admin/pages/default'
            ], true);
        }

    }
}