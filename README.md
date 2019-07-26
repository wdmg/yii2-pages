[![Yii2](https://img.shields.io/badge/required-Yii2_v2.0.20-blue.svg)](https://packagist.org/packages/yiisoft/yii2)
[![Github all releases](https://img.shields.io/github/downloads/wdmg/yii2-pages/total.svg)](https://GitHub.com/wdmg/yii2-pages/releases/)
![Progress](https://img.shields.io/badge/progress-ready_to_use-green.svg)
[![GitHub license](https://img.shields.io/github/license/wdmg/yii2-pages.svg)](https://github.com/wdmg/yii2-pages/blob/master/LICENSE)
![GitHub release](https://img.shields.io/github/release/wdmg/yii2-pages/all.svg)

# Yii2 Pages
Static pages manager

# Requirements 
* PHP 5.6 or higher
* Yii2 v.2.0.20 and newest
* [Yii2 Base](https://github.com/wdmg/yii2-base) module (required)
* [Yii2 Editor](https://github.com/wdmg/yii2-editor) module (required)
* [Yii2 SelectInput](https://github.com/wdmg/yii2-selectinput) widget

# Installation
To install the module, run the following command in the console:

`$ composer require "wdmg/yii2-pages"`

After configure db connection, run the following command in the console:

`$ php yii pages/init`

And select the operation you want to perform:
  1) Apply all module migrations
  2) Revert all module migrations

# Migrations
In any case, you can execute the migration and create the initial data, run the following command in the console:

`$ php yii migrate --migrationPath=@vendor/wdmg/yii2-pages/migrations`

# Configure
To add a module to the project, add the following data in your configuration file:

    'modules' => [
        ...
        'pages' => [
            'class' => 'wdmg\pages\Module',
            'routePrefix' => 'admin',
            'pagesRoute'  => '/pages', // route for frontend (string or array), use "/" - for root
            'pagesLayout' => '@app/views/layouts/main' // path to default layout for render in frontend
        ],
        ...
    ],


# Routing
Use the `Module::dashboardNavItems()` method of the module to generate a navigation items list for NavBar, like this:

    <?php
        echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
            'label' => 'Modules',
            'items' => [
                Yii::$app->getModule('pages')->dashboardNavItems(),
                ...
            ]
        ]);
    ?>

# Status and version [in progress development]
* v.1.1.0 - Added routing functionality
* v.1.0.1 - Added default route and layout for rendered page
* v.1.0.0 - Added migrations, models, CRUD, views and translations