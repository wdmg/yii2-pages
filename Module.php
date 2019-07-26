<?php

namespace wdmg\pages;

/**
 * Yii2 Pages
 *
 * @category        Module
 * @version         1.0.1
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-pages
 * @copyright       Copyright (c) 2019 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use Yii;
use wdmg\base\BaseModule;

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
    private $version = "1.0.1";

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

        // Process to normalize route for pages in frontend
        $this->pagesRoute = self::normalizePagesRoute($this->pagesRoute);

    }

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
            'icon' => 'fa-folder',
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

        // Add routes to pages in frontend
        $pagesRoute = $this->pagesRoute;
        if (is_array($pagesRoute)) {
            foreach ($pagesRoute as $route) {
                if (empty($route) || $route == "/") {
                    $app->getUrlManager()->addRules([
                        [
                            'pattern' => '/<page:[\w-]+>',
                            'route' => 'admin/pages/default/index',
                            'suffix' => ''
                        ],
                        '/<page:[\w-]+>' => 'admin/pages/default/index',
                    ], true);
                } else {
                    $app->getUrlManager()->addRules([
                        [
                            'pattern' => $route . '/<page:[\w-]+>',
                            'route' => 'admin/pages/default/index',
                            'suffix' => ''
                        ],
                        $route . '/<page:[\w-]+>' => 'admin/pages/default/index',
                    ], true);
                }
            }
        } else {
            if (empty($pagesRoute) || $pagesRoute == "/") {
                $app->getUrlManager()->addRules([
                    [
                        'pattern' => '/<page:[\w-]+>',
                        'route' => 'admin/pages/default/index',
                        'suffix' => ''
                    ],
                    '/<page:[\w-]+>' => 'admin/pages/default/index',
                ], true);
            } else {
                $app->getUrlManager()->addRules([
                    [
                        'pattern' => $pagesRoute . '/<page:[\w-]+>',
                        'route' => 'admin/pages/default/index',
                        'suffix' => ''
                    ],
                    $pagesRoute . '/<page:[\w-]+>' => 'admin/pages/default/index',
                ], true);
            }
        }
    }
}