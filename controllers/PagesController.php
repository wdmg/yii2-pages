<?php

namespace wdmg\pages\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use wdmg\pages\models\Pages;
use wdmg\pages\models\PagesSearch;

/**
 * PagesController implements the CRUD actions for Pages model.
 */
class PagesController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['get'],
                    'view' => ['get'],
                    'delete' => ['post'],
                    'create' => ['get', 'post'],
                    'update' => ['get', 'post'],
                    'export' => ['get'],
                    'import' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'roles' => ['admin'],
                        'allow' => true
                    ],
                ],
            ],
        ];

        // If auth manager not configured use default access control
        if(!Yii::$app->authManager) {
            $behaviors['access'] = [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'roles' => ['@'],
                        'allow' => true
                    ],
                ]
            ];
        }

        return $behaviors;
    }

    /**
     * Lists of all Pages models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PagesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'module' => $this->module
        ]);
    }


    /**
     * Creates a new Page model.
     * If creation is successful, the browser will be redirected to the list of pages.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Pages();
        $model->status = $model::PAGE_STATUS_DRAFT;

        if ($model->load(Yii::$app->request->post())) {

            if($model->save())
                Yii::$app->getSession()->setFlash(
                    'success',
                    Yii::t('app/modules/pages', 'Page has been successfully addedet!')
                );
            else
                Yii::$app->getSession()->setFlash(
                    'danger',
                    Yii::t('app/modules/pages', 'An error occurred while add the page.')
                );

            return $this->redirect(['pages/index']);
        }

        return $this->render('create', [
            'model' => $model
        ]);

    }
}
