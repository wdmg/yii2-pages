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
 * @property string $name
 * @property string $alias
 * @property string $content
 * @property string $title
 * @property string $description
 * @property string $keywords
 * @property integer $status
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
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => new Expression('NOW()'),
            ],
            'sluggable' =>  [
                'class' => SluggableBehavior::className(),
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
                'class' => BlameableBehavior::className(),
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
            [['name', 'alias', 'content'], 'required'],
            [['name', 'alias'], 'string', 'min' => 3, 'max' => 128],
            [['name', 'alias'], 'string', 'min' => 3, 'max' => 128],
            [['title', 'description', 'keywords'], 'string', 'max' => 255],
            [['status'], 'boolean'],
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
            'name' => Yii::t('app/modules/pages', 'Name'),
            'alias' => Yii::t('app/modules/pages', 'Alias'),
            'content' => Yii::t('app/modules/pages', 'Content'),
            'title' => Yii::t('app/modules/pages', 'Title'),
            'description' => Yii::t('app/modules/pages', 'Description'),
            'keywords' => Yii::t('app/modules/pages', 'Keywords'),
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
        if(empty(trim($this->route)))
            $this->route = null;
        else
            $this->route = trim($this->route);

        if(empty(trim($this->layout)))
            $this->layout = null;
        else
            $this->layout = trim($this->layout);

        return parent::beforeSave($insert);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatusesList($allStatuses = false)
    {
        if($allStatuses)
            return [
                '*' => Yii::t('app/modules/pages', 'All statuses'),
                self::PAGE_STATUS_DRAFT => Yii::t('app/modules/pages', 'Draft'),
                self::PAGE_STATUS_PUBLISHED => Yii::t('app/modules/pages', 'Published'),
            ];
        else
            return [
                self::PAGE_STATUS_DRAFT => Yii::t('app/modules/pages', 'Draft'),
                self::PAGE_STATUS_PUBLISHED => Yii::t('app/modules/pages', 'Published'),
            ];
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        if (!is_null($this->route)) {
            if ($this->route == '/')
                $route = '';
            else
                $route = $this->route;
        } else {
            if (is_array(Yii::$app->controller->module->pagesRoute)) {
                $route = array_shift(Yii::$app->controller->module->pagesRoute);
            } else {
                $route = Yii::$app->controller->module->pagesRoute;
            }
        }
        return $route;
    }

    /**
     *
     * @param $withScheme boolean, absolute or relative URL
     * @return string or null
     */
    public function getPageUrl($withScheme = true)
    {
        $this->route = $this->getRoute();
        if (isset($this->alias)) {
            return \yii\helpers\Url::to($this->route . '/' .$this->alias, $withScheme);
        } else {
            return null;
        }
    }

    /**
     *
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
            return $this->hasOne(\wdmg\users\models\Users::className(), ['id' => 'created_by']);
        else
            return null;
    }

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        if(class_exists('\wdmg\users\models\Users'))
            return $this->hasMany(\wdmg\users\models\Users::className(), ['id' => 'created_by']);
        else
            return null;
    }
}
