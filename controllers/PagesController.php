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
        $model->route = null;
        $model->layout = null;

        if (Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate())
                    $success = true;
                else
                    $success = false;

                return $this->asJson(['success' => $success, 'alias' => $model->alias, 'errors' => $model->errors]);
            }
        } else {
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
        }

        return $this->render('create', [
            'module' => $this->module,
            'model' => $model
        ]);

    }



    /**
     * Updates an existing Page model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Get current URL before save this page
        $oldPageUrl = $model->getPageUrl(false);

        if (Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate())
                    $success = true;
                else
                    $success = false;

                return $this->asJson(['success' => $success, 'alias' => $model->alias, 'errors' => $model->errors]);
            }
        } else {
            if ($model->load(Yii::$app->request->post())) {

                // Get new URL for saved page
                $newPageUrl = $model->getPageUrl(false);

                if($model->save()) {

                    // Set 301-redirect from old URL to new
                    if (($oldPageUrl !== $newPageUrl) && ($model->status == $model::PAGE_STATUS_PUBLISHED)) {
                        // @TODO: remove old redirects
                        Yii::$app->redirects->set('pages', $oldPageUrl, $newPageUrl, 301);
                    }

                    Yii::$app->getSession()->setFlash(
                        'success',
                        Yii::t(
                            'app/modules/pages',
                            'OK! Page `{name}` successfully updated.',
                            [
                                'name' => $model->name
                            ]
                        )
                    );
                } else {
                    Yii::$app->getSession()->setFlash(
                        'danger',
                        Yii::t(
                            'app/modules/pages',
                            'An error occurred while update a page `{name}`.',
                            [
                                'name' => $model->name
                            ]
                        )
                    );
                }
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'module' => $this->module,
            'model' => $model
        ]);
    }

    /**
     * Displays a single Page model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'module' => $this->module,
            'model' => $model
        ]);
    }

    /**
     * Deletes an existing Pages model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);
        if($model->delete()) {

            // @TODO: remove redirects of deleted pages

            Yii::$app->getSession()->setFlash(
                'success',
                Yii::t(
                    'app/modules/pages',
                    'OK! Page `{name}` successfully deleted.',
                    [
                        'name' => $model->name
                    ]
                )
            );
        } else {
            Yii::$app->getSession()->setFlash(
                'danger',
                Yii::t(
                    'app/modules/pages',
                    'An error occurred while deleting a page `{name}`.',
                    [
                        'name' => $model->name
                    ]
                )
            );
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Pages model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Settings the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Pages::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app/modules/pages', 'The requested page does not exist.'));
    }
}
