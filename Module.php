<?php

namespace wdmg\pages;

/**
 * Yii2 Pages
 *
 * @category        Module
 * @version         1.0.0
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
    private $version = "1.0.0";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 2;

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

        // Add module URL rules
        $app->getUrlManager()->addRules([
            [
                'pattern' => 'pages/<page:[\w-]+>',
                'route' => 'admin/pages/default/index',
                'suffix' => ''
            ],
            'pages/<page:[\w-]+>' => 'admin/pages/default/index',
        ], true);
    }
}