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
 * @property string $name
 * @property string $alias
 * @property string $content
 * @property string $title
 * @property string $description
 * @property string $keywords
 * @property boolean $in_sitemap
 * @property boolean $in_turbo
 * @property boolean $in_amp
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

    public $url;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pages}}';
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
            ['parent_id', 'integer'],
            [['name', 'alias', 'content'], 'required'],
            [['name', 'alias'], 'string', 'min' => 3, 'max' => 128],
            [['name', 'alias'], 'string', 'min' => 3, 'max' => 128],
            [['title', 'description', 'keywords'], 'string', 'max' => 255],
            [['status', 'in_sitemap', 'in_turbo', 'in_amp'], 'boolean'],

            ['route', 'string', 'max' => 32],
            ['route', 'match', 'pattern' => '/^[A-Za-z0-9\-\_\/]+$/', 'message' => Yii::t('app/modules/pages','It allowed only Latin alphabet, numbers and the «-», «_», «/» characters.')],

            ['layout', 'string', 'max' => 64],
            ['layout', 'match', 'pattern' => '/^[A-Za-z0-9\-\_\/\@]+$/', 'message' => Yii::t('app/modules/pages','It allowed only Latin alphabet, numbers and the «@», «-», «_», «/» characters.')],

            ['alias', 'unique', 'message' => Yii::t('app/modules/pages', 'Param attribute must be unique.')],
            ['alias', 'match', 'pattern' => '/^[A-Za-z0-9\-\_]+$/', 'message' => Yii::t('app/modules/pages','It allowed only Latin alphabet, numbers and the «-», «_» characters.')],
            [['created_at', 'updated_at'], 'safe'],
        ];

        if (class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users'])) {
            $rules[] = [['created_by', 'updated_by'], 'required'];
        }

        return $rules;
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/modules/pages', 'ID'),
            'parent_id' => Yii::t('app/modules/pages', 'Parent ID'),
            'name' => Yii::t('app/modules/pages', 'Name'),
            'alias' => Yii::t('app/modules/pages', 'Alias'),
            'content' => Yii::t('app/modules/pages', 'Content'),
            'title' => Yii::t('app/modules/pages', 'Title'),
            'description' => Yii::t('app/modules/pages', 'Description'),
            'keywords' => Yii::t('app/modules/pages', 'Keywords'),
            'in_sitemap' => Yii::t('app/modules/pages', 'In sitemap?'),
            'in_turbo' => Yii::t('app/modules/pages', 'Yandex turbo-pages?'),
            'in_amp' => Yii::t('app/modules/pages', 'Google AMP?'),
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
    public function beforeSave($insert)
    {
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
        $this->url = $this->getPageUrl();
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

        if ($this->parent_id) {
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
    public function getPageUrl($withScheme = true, $realUrl = true)
    {
        $this->route = $this->getRoute();
        if (isset($this->alias)) {
            if ($this->status == self::PAGE_STATUS_DRAFT && $realUrl)
                return \yii\helpers\Url::to(['default/index', 'route' => $this->route, 'page' => $this->alias, 'draft' => 'true'], $withScheme);
            else
                return \yii\helpers\Url::to($this->route . '/' .$this->alias, $withScheme);

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
    public function getUser()
    {
        if(class_exists('\wdmg\users\models\Users'))
            return $this->hasOne(\wdmg\users\models\Users::class, ['id' => 'created_by']);
        else
            return null;
    }

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        if(class_exists('\wdmg\users\models\Users'))
            return $this->hasMany(\wdmg\users\models\Users::class, ['id' => 'created_by']);
        else
            return null;
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
}
