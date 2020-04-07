<?php

namespace wdmg\pages\models;

use Yii;
use yii\db\Expression;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\base\InvalidArgumentException;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\SluggableBehavior;

/**
 * This is the model class for table "{{%pages}}".
 *
 * @property int $id
 * @property int $parent_id
 * @property int $source_id
 * @property string $name
 * @property string $alias
 * @property string $content
 * @property string $title
 * @property string $description
 * @property string $keywords
 * @property boolean $in_sitemap
 * @property boolean $in_turbo
 * @property boolean $in_amp
 * @property string $locale
 * @property boolean $status
 * @property string $route
 * @property string $layout
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class Pages extends ActiveRecord
{
    const PAGE_STATUS_DRAFT = 0; // Page has draft
    const PAGE_STATUS_PUBLISHED = 1; // Page has been published
    const PAGE_SCENARIO_CREATE = 'create';

    public $url;

    private $_module;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pages}}';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!($this->_module = Yii::$app->getModule('admin/pages', false)))
            $this->_module = Yii::$app->getModule('pages', false);

    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => new Expression('NOW()'),
            ],
            'sluggable' =>  [
                'class' => SluggableBehavior::class,
                'attribute' => ['name'],
                'slugAttribute' => 'alias',
                'ensureUnique' => true,
                'skipOnEmpty' => true,
                'immutable' => true,
                'value' => function ($event) {
                    return mb_substr($this->name, 0, 32);
                }
            ],
            'blameable' =>  [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [['name', 'alias', 'content', 'locale'], 'required'],
            [['name', 'alias'], 'string', 'min' => 3, 'max' => 128],
            [['name', 'alias'], 'string', 'min' => 3, 'max' => 128],
            [['title', 'description', 'keywords'], 'string', 'max' => 255],
            [['status', 'in_sitemap', 'in_turbo', 'in_amp'], 'boolean'],

            [['parent_id', 'source_id'], 'integer'],
            ['parent_id', 'checkParent'],
            ['source_id', 'checkSource'],

            ['route', 'string', 'max' => 32],
            ['route', 'match', 'pattern' => '/^[A-Za-z0-9\-\_\/]+$/', 'message' => Yii::t('app/modules/pages','It allowed only Latin alphabet, numbers and the «-», «_», «/» characters.')],

            ['locale', 'string', 'max' => 10],
            ['locale', 'checkLocale', 'on' => self::PAGE_SCENARIO_CREATE],

            [['parent_id', 'source_id'], 'doublesCheck', 'on' => self::PAGE_SCENARIO_CREATE],

            ['layout', 'string', 'max' => 64],
            ['layout', 'match', 'pattern' => '/^[A-Za-z0-9\-\_\/\@]+$/', 'message' => Yii::t('app/modules/pages','It allowed only Latin alphabet, numbers and the «@», «-», «_», «/» characters.')],

            //['alias', 'unique', 'message' => Yii::t('app/modules/pages', 'Param attribute must be unique.')],
            ['alias', 'checkAlias'],
            ['alias', 'match', 'pattern' => '/^[A-Za-z0-9\-\_]+$/', 'message' => Yii::t('app/modules/pages','It allowed only Latin alphabet, numbers and the «-», «_» characters.')],
            [['created_at', 'updated_at'], 'safe'],
        ];

        if (class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users'])) {
            $rules[] = [['created_by', 'updated_by'], 'safe'];
        }

        return $rules;
    }

    /**
     * Checks if there is a page (language version) with the same parent, source version and language locale.
     * Used according by the scenario when creating a new page.
     *
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function doublesCheck($attribute, $params)
    {
        $hasError = false;
        if (!empty($this->parent_id) && !empty($this->source_id) && !empty($this->locale)) {

            if (self::find()->where(['parent_id' => $this->parent_id, 'source_id' => $this->source_id, 'locale' => $this->locale])->count())
                $hasError = true;

        }

        if ($hasError) {
            $this->addError($attribute, Yii::t('app/modules/pages', 'It looks like the same language version of page or child page already exists.'));
        }

        return $hasError;
    }

    /**
     * Checks if the current page (language version) links to a page that is not the main version.
     *
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function checkParent($attribute, $params)
    {
        $hasError = false;
        if (!empty($this->parent_id) && !empty($this->source_id)) {

            if (self::find()->where(['id' => $this->parent_id])->andWhere(['!=', 'source_id', null])->count())
                $hasError = true;

        }

        if ($hasError) {
            $this->addError($attribute, Yii::t('app/modules/pages', 'Child page cannot link to the language version of the page.'));
        }

        return $hasError;
    }

    /**
     * Checks if the alias of the current page is not an alias duplicate of the main version.
     *
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function checkAlias($attribute, $params)
    {
        $hasError = false;
        if (!empty($this->alias) && !empty($this->source_id)) {

            if (self::find()->where(['alias' => $this->alias])->andWhere(['!=', 'source_id', $this->source_id])->count())
                $hasError = true;

            if (self::find()->where(['alias' => $this->alias, 'source_id' => null])->andWhere(['!=', 'id', $this->id])->count())
                $hasError = true;

        }

        if ($hasError) {
            $this->addError($attribute, Yii::t('app/modules/pages', 'Param attribute must be unique.'));
        }

        return $hasError;
    }

    /**
     * Checks if the current language version of the page is referencing itself
     *
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function checkSource($attribute, $params)
    {
        $hasError = false;
        if (isset($this->source_id)) {

            if ($this->id == $this->source_id)
                $hasError = true;

        }

        if ($hasError) {
            $this->addError($attribute, Yii::t('app/modules/pages', 'The language version must refer to the main version.'));
        }

        $hasError = false;
        if (isset($this->parent_id)) {

            if ($this->id == $this->parent_id)
                $hasError = true;

        }

        if ($hasError) {
            $this->addError($attribute, Yii::t('app/modules/pages', 'The current page should not link to itself.'));
        }

        return $hasError;
    }

    /**
     * Checks if the same language version of the current page exists.
     *
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function checkLocale($attribute, $params)
    {
        $hasError = false;
        if (!empty($this->locale) && !empty($this->source_id)) {

            if (self::find()->where(['locale' => $this->locale, 'source_id' => $this->source_id])->andWhere(['!=', 'id', $this->id])->count())
                $hasError = true;

            if (self::find()->where(['locale' => $this->locale, 'id' => $this->source_id])->count())
                $hasError = true;

        }

        if ($hasError) {
            $this->addError($attribute, Yii::t('app/modules/pages', 'A language version with the selected language already exists.'));
        }

        return $hasError;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/modules/pages', 'ID'),
            'parent_id' => Yii::t('app/modules/pages', 'Parent ID'),
            'source_id' => Yii::t('app/modules/pages', 'Source ID'),
            'name' => Yii::t('app/modules/pages', 'Name'),
            'alias' => Yii::t('app/modules/pages', 'Alias'),
            'content' => Yii::t('app/modules/pages', 'Content'),
            'title' => Yii::t('app/modules/pages', 'Title'),
            'description' => Yii::t('app/modules/pages', 'Description'),
            'keywords' => Yii::t('app/modules/pages', 'Keywords'),
            'in_sitemap' => Yii::t('app/modules/pages', 'In sitemap?'),
            'in_turbo' => Yii::t('app/modules/pages', 'Yandex turbo-pages?'),
            'in_amp' => Yii::t('app/modules/pages', 'Google AMP?'),
            'locale' => Yii::t('app/modules/pages', 'Locale'),
            'status' => Yii::t('app/modules/pages', 'Status'),
            'route' => Yii::t('app/modules/pages', 'Route'),
            'layout' => Yii::t('app/modules/pages', 'Layout'),
            'created_at' => Yii::t('app/modules/pages', 'Created at'),
            'created_by' => Yii::t('app/modules/pages', 'Created by'),
            'updated_at' => Yii::t('app/modules/pages', 'Updated at'),
            'updated_by' => Yii::t('app/modules/pages', 'Updated by'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        /**
         * If the parent of the page was specified but the language version is retained, you must obtain one
         * `id` of the main version of the page and link to it the current page
         */
        if (is_null($this->source_id) && !is_null($this->parent_id)) {
            $source = self::findOne(['parent_id' => $this->parent_id, 'source_id' => null]);
            if (isset($source->id)) {
                $this->source_id = $source->id;
            }
        }

        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (empty($this->title) && !empty($this->name))
            $this->title = $this->name;

        if (empty(trim($this->route)))
            $this->route = null;
        else
            $this->route = trim($this->route);

        if (empty(trim($this->layout)))
            $this->layout = null;
        else
            $this->layout = trim($this->layout);

        if ($this->parent_id == 0)
            $this->parent_id = null;
        else
            $this->parent_id = intval($this->parent_id);

        if (!is_null($this->parent_id))
            $this->route = $this->getRoute();

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->url = $this->getPageUrl(true, true);
    }

    /**
     * @return array
     */
    public function getStatusesList($allStatuses = false)
    {
        $list = [];
        if ($allStatuses) {
            $list = [
                '*' => Yii::t('app/modules/pages', 'All statuses')
            ];
        }

        $list = ArrayHelper::merge($list, [
            self::PAGE_STATUS_DRAFT => Yii::t('app/modules/pages', 'Draft'),
            self::PAGE_STATUS_PUBLISHED => Yii::t('app/modules/pages', 'Published'),
        ]);

        return $list;
    }

    /**
     * @param bool $allLabel
     * @param bool $rootLabel
     * @return array
     */
    public function getParentsList($allLabel = true, $rootLabel = false)
    {

        if ($this->id) {
            $subQuery = self::find()->select('id')->where(['parent_id' => $this->id]);
            $query = self::find()->alias('pages')
                ->where(['not in', 'pages.parent_id', $subQuery])
                ->andWhere(['!=', 'pages.parent_id', $this->id])
                ->orWhere(['IS', 'pages.parent_id', null])
                ->andWhere(['!=', 'pages.id', $this->id])
                ->select(['id', 'name']);

            $pages = $query->asArray()->all();
        } else {
            $pages = self::find()->select(['id', 'name'])->asArray()->all();
        }

        if ($allLabel)
            return ArrayHelper::merge([
                '*' => Yii::t('app/modules/pages', '-- All pages --')
            ], ArrayHelper::map($pages, 'id', 'name'));
        elseif ($rootLabel)
            return ArrayHelper::merge([
                0 => Yii::t('app/modules/pages', '-- Root page --')
            ], ArrayHelper::map($pages, 'id', 'name'));
        else
            return ArrayHelper::map($pages, 'id', 'name');
    }

    /**
     * Return the public route for pages URL
     * @return string
     */
    private function getRoute($route = null)
    {

        if (is_null($route)) {
            if (isset(Yii::$app->params["pages.pagesRoute"])) {
                $route = Yii::$app->params["pages.pagesRoute"];
            } else {

                if (!$module = Yii::$app->getModule('admin/pages'))
                    $module = Yii::$app->getModule('pages');

                $route = $module->pagesRoute;
            }
        }

        if ($this->source_id) {
            if ($parent = self::find()->where(['source_id' => intval($this->parent_id), 'locale' => $this->locale])->one())
                return $parent->getRoute($route) ."/". $parent->alias;
        } elseif ($this->parent_id) {
            if ($parent = self::find()->where(['id' => intval($this->parent_id)])->one())
                return $parent->getRoute($route) ."/". $parent->alias;
        }


        return $route;
    }

    /**
     * Build and return the URL for current page for frontend
     *
     * @param bool $withScheme
     * @param bool $realUrl
     * @return null|string
     */
    public function getPageUrl($absoluteUrl = true, $realUrl = false)
    {
        $this->route = $this->getRoute();
        if (isset($this->alias)) {
            if (isset(Yii::$app->translations) && class_exists('wdmg\translations\models\Languages')) {
                $translations = Yii::$app->translations->module;
                if ($config = $translations->urlManagerConfig) {

                    if (isset($config['class']))
                        unset($config['class']);

                    // Init UrlManager and configure
                    $urlManager = new \wdmg\translations\components\UrlManager($config);
                    if ($this->status == self::PAGE_STATUS_DRAFT && $realUrl) {
                        if ($absoluteUrl)
                        return $urlManager->createAbsoluteUrl(['default/index', 'route' => $this->route, 'page' => $this->alias, 'lang' => $this->locale, 'draft' => 'true']);
                            else
                        return $urlManager->createUrl(['default/index', 'route' => $this->route, 'page' => $this->alias, 'lang' => $this->locale, 'draft' => 'true']);
                    } else {
                        if ($absoluteUrl)
                            return $urlManager->createAbsoluteUrl([$this->route . '/' . $this->alias, 'lang' => $this->locale]);
                        else
                            return $urlManager->createUrl([$this->route . '/' . $this->alias, 'lang' => $this->locale]);
                    }
                }
            } else {
                if ($this->status == self::PAGE_STATUS_DRAFT && $realUrl) {
                    return \yii\helpers\Url::to(['default/index', 'route' => $this->route, 'page' => $this->alias, 'draft' => 'true'], $absoluteUrl);
                } else {
                    return \yii\helpers\Url::to($this->route . '/' . $this->alias);
                }
            }

        } else {
            return null;
        }
    }

    /**
     * Return the public routes for URL
     * @param $asArray boolean, return results as array
     * @return array or object of \yii\db\ActiveQuery
     */
    public function getRoutes($asArray = false)
    {
        if ($asArray)
            return self::find()->select(['route'])->distinct()->asArray()->all();
        else
            return self::find()->select(['route'])->distinct()->all();
    }

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        if (class_exists('\wdmg\users\models\Users'))
            return $this->hasOne(\wdmg\users\models\Users::class, ['id' => 'created_by']);
        else
            return $this->created_by;
    }

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        if (class_exists('\wdmg\users\models\Users'))
            return $this->hasOne(\wdmg\users\models\Users::class, ['id' => 'updated_by']);
        else
            return $this->updated_by;
    }

    /**
     * Returns published pages
     *
     * @param null $cond sampling conditions
     * @param bool $asArray flag if necessary to return as an array
     * @return array|ActiveRecord|null
     */
    public function getPublished($cond = null, $asArray = false) {
        if (!is_null($cond) && is_array($cond))
            $models = self::find()->where(ArrayHelper::merge($cond, ['status' => self::PAGE_STATUS_PUBLISHED]));
        elseif (!is_null($cond) && is_string($cond))
            $models = self::find()->where(ArrayHelper::merge([$cond], ['status' => self::PAGE_STATUS_PUBLISHED]));
        else
            $models = self::find()->where(['status' => self::PAGE_STATUS_PUBLISHED]);

        if ($asArray)
            return $models->asArray()->all();
        else
            return $models->all();

    }

    /**
     * Returns all pages (draft and published)
     *
     * @param null $cond sampling conditions
     * @param bool $asArray flag if necessary to return as an array
     * @return array|ActiveRecord|null
     */
    public function getAll($cond = null, $asArray = false) {
        if (!is_null($cond))
            $models = self::find()->where($cond);
        else
            $models = self::find();

        if ($asArray)
            return $models->asArray()->all();
        else
            return $models->all();

    }

    /**
     * Returns the URL to the view of the current model
     *
     * @return string
     */
    public function getUrl()
    {
        if ($this->url === null)
            $this->url = $this->getPageUrl();

        return $this->url;
    }


    /**
     * Returns a list of all language versions of current page.
     *
     * @param null $source_id
     * @param bool $asArray
     * @return array|\yii\db\ActiveQuery|ActiveRecord[]|null
     */
    public function getAllVersions($source_id = null, $asArray = false)
    {
        if (is_null($source_id))
            return null;

        $models = self::find()->andWhere(['id' => $source_id])->orWhere(['source_id' => $source_id]);

        if ($asArray)
            return $models->asArray()->all();
        else
            return $models;
    }

    /**
     * Returns a list of all languages used for current page.
     *
     * @param null $id
     * @param bool $asArray
     * @return array
     */
    public function getLanguages($id = null, $asArray = false)
    {

        if (!($models = $this->getAllVersions($id, false))) {
            $models = self::find();
        }

        $models->select('locale')->groupBy('locale');

        if ($asArray)
            $models->asArray();

        $languages = [];
        $locales = ArrayHelper::getColumn($models->all(), 'locale');
        foreach ($locales as $locale) {
            if (!is_null($locale)) {
                if (extension_loaded('intl')) {
                    $languages[] = [
                        $locale => mb_convert_case(trim(\Locale::getDisplayLanguage($locale, Yii::$app->language)), MB_CASE_TITLE, "UTF-8"),
                    ];
                } else {
                    $languages[] = [
                        $locale => $locale,
                    ];
                }
            }
        }
        return $languages;
    }

    /**
     * Returns a list of all available languages. Including taking into account already used as the language versions
     * of pages. If the `wdmg\yii2-translations` module with the list of active languages is not available,
     * the `$supportLanguages` parameter of the current module will be used.
     *
     * @param bool $allLanguages
     * @return array
     */
    public function getLanguagesList($allLanguages = false)
    {
        $list = [];
        if ($allLanguages) {
            $list = [
                '*' => Yii::t('app/modules/pages', 'All languages')
            ];
        }

        $languages = $this->getLanguages(null, false);
        if (isset(Yii::$app->translations) && class_exists('wdmg\translations\models\Languages')) {
            $locales = Yii::$app->translations->getLocales(false, false, true);
            $languages = array_diff_assoc(ArrayHelper::map($locales, 'locale', 'name'), $languages);
        } else {
            if (is_array($this->_module->supportLocales)) {
                $supportLanguages = [];
                $locales = $this->_module->supportLocales;
                foreach ($locales as $locale) {

                    if (extension_loaded('intl'))
                        $language = mb_convert_case(trim(\Locale::getDisplayLanguage($locale, Yii::$app->language)), MB_CASE_TITLE, "UTF-8");
                    else
                        $language = $locale;

                    $supportLanguages = ArrayHelper::merge($supportLanguages, [$locale => $language]);
                }

                $languages = array_diff_assoc($supportLanguages, $languages);
            }
        }

        $list = ArrayHelper::merge($list, $languages);

        return $list;
    }

}
