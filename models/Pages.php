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
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => new Expression('NOW()'),
            ],
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
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
            ['alias', 'unique', 'message' => Yii::t('app/modules/pages', 'Param attribute must be unique.')],
            ['alias', 'match', 'pattern' => '/^[A-Za-z0-9\-]+$/', 'message' => Yii::t('app/modules/pages','It allowed only Latin alphabet, numbers and the «-» character')],
            [['created_at', 'updated_at'], 'safe'],
        ];

        if(class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users'])) {
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
            'created_at' => Yii::t('app/modules/pages', 'Created at'),
            'created_by' => Yii::t('app/modules/pages', 'Created by'),
            'updated_at' => Yii::t('app/modules/pages', 'Updated at'),
            'updated_by' => Yii::t('app/modules/pages', 'Updated by'),
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatusModes($allStatuses = false)
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
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        if(class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users']))
            return $this->hasOne(\wdmg\users\models\Users::className(), ['id' => 'created_by']);
        else
            return null;
    }
}
