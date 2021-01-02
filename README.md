[![Yii2](https://img.shields.io/badge/required-Yii2_v2.0.33-blue.svg)](https://packagist.org/packages/yiisoft/yii2)
[![Downloads](https://img.shields.io/packagist/dt/wdmg/yii2-pages.svg)](https://packagist.org/packages/wdmg/yii2-pages)
[![Packagist Version](https://img.shields.io/packagist/v/wdmg/yii2-pages.svg)](https://packagist.org/packages/wdmg/yii2-pages)
![Progress](https://img.shields.io/badge/progress-ready_to_use-green.svg)
[![GitHub license](https://img.shields.io/github/license/wdmg/yii2-pages.svg)](https://github.com/wdmg/yii2-pages/blob/master/LICENSE)

<img src="./docs/images/yii2-pages.png" width="100%" alt="Yii2 Pages" />

# Yii2 Pages
Page manager for Yii2.

This module is an integral part of the [Butterfly.СMS](https://butterflycms.com/) content management system, but can also be used as an standalone extension.

Copyrights (c) 2019-2021 [W.D.M.Group, Ukraine](https://wdmg.com.ua/)

# Requirements 
* PHP 5.6 or higher
* Yii2 v.2.0.33 and newest
* [Yii2 Base](https://github.com/wdmg/yii2-base) module (required)
* [Yii2 Translations](https://github.com/wdmg/yii2-translations) module (optionaly)
* [Yii2 Editor](https://github.com/wdmg/yii2-editor) widget
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
            'baseRoute'  => '/pages', // route for frontend (string or array), use "/" - for root
            'baseLayout' => '@app/views/layouts/main', // path to default layout for render in frontend
            'supportLocales' => ['ru-RU', 'uk-UA', 'en-US'] // list of support locales for multi-language versions
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

# Status and version [ready to use]
* v.1.2.5 - RBAC implementation
* v.1.2.4 - URL redirect notify, defaultController property, update dependencies and README.md
* v.1.2.3 - Update README.md and dependencies
* v.1.2.2 - Added AliasInput::widget()
* v.1.2.1 - Rebased to ActiveRecordML model
* v.1.2.0 - Multi-language implementation
* v.1.1.12 - Log activity
* v.1.1.11 - Added pagination, up to date dependencies
* v.1.1.10 - Refactoring. Migrations bugfix
* v.1.1.9 - Added multiple nesting for pages
* v.1.1.8 - Added support for Yandex.Turbo and Google AMP modules
* v.1.1.7 - Added support for Sitemap module