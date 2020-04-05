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
     * @var string|null Storaged selected language (locale)
     */
    private $_locale;

    /**
     * @var string|null Storaged selected id of source page
     */
    private $_source_id;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = [
            'verbs' => [
                'class' => VerbFilter::class,
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
                'class' => AccessControl::class,
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
                'class' => AccessControl::class,
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

    public function beforeAction($action)
    {
        $this->_locale = Yii::$app->request->get('locale', null);
        $this->_source_id = Yii::$app->request->get('source_id', null);
        return parent::beforeAction($action);
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
        $model->scenario = $model::PAGE_SCENARIO_CREATE;

        $model->status = $model::PAGE_STATUS_DRAFT;
        $model->route = null;
        $model->layout = null;

        // No language is set for this model, we will use the current user language
        if (is_null($model->locale)) {
            if (is_null($this->_locale)) {
                $model->locale = Yii::$app->language;
                if (!Yii::$app->request->isPost) {
                    $languages = $model->getLanguagesList(false);
                    Yii::$app->getSession()->setFlash(
                        'danger',
                        Yii::t(
                            'app/modules/pages',
                            'No display language has been set for this page. When saving, the current user language will be selected: {language}',
                            [
                                'language' => (isset($languages[Yii::$app->language])) ? $languages[Yii::$app->language] : Yii::$app->language
                            ]
                        )
                    );
                }
            } else {
                $model->locale = $this->_locale;
            }
        }

        if (!is_null($this->_source_id)) {
            $model->source_id = $this->_source_id;

            if ($perent = Pages::findOne(['source_id' => $this->_source_id, 'locale' => $this->_locale]) !== null)
                $model->perent_id = $perent->id;
        }

        if (Yii::$app->request->isAjax) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate())
                    $success = true;
                else
                    $success = false;

                return $this->asJson(['success' => $success, 'alias' => $model->alias, 'errors' => $model->errors]);
            }
        } else {

            /*if (Yii::$app->request->isPost && !$model->validate()) {
                var_dump($model->errors);
                die();
            }*/

            if ($model->load(Yii::$app->request->post())) {

                if ($model->save()) {
                    // Log activity
                    $this->module->logActivity(
                        'New page `' . $model->name . '` with ID `' . $model->id . '` has been successfully added.',
                        $this->uniqueId . ":" . $this->action->id,
                        'success',
                        1
                    );

                    Yii::$app->getSession()->setFlash(
                        'success',
                        Yii::t('app/modules/pages', 'Page has been successfully added!')
                    );
                } else {
                    // Log activity
                    $this->module->logActivity(
                        'An error occurred while add the new page: ' . $model->name,
                        $this->uniqueId . ":" . $this->action->id,
                        'danger',
                        1
                    );

                    Yii::$app->getSession()->setFlash(
                        'danger',
                        Yii::t('app/modules/pages', 'An error occurred while add the page.')
                    );
                }

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

        // No language is set for this model, we will use the current user language
        if (is_null($model->locale)) {
            $model->locale = Yii::$app->language;
            if (!Yii::$app->request->isPost) {
                $languages = $model->getLanguagesList(false);
                Yii::$app->getSession()->setFlash(
                    'danger',
                    Yii::t(
                        'app/modules/pages',
                        'No display language has been set for this page. When saving, the current user language will be selected: {language}',
                        [
                            'language' => (isset($languages[Yii::$app->language])) ? $languages[Yii::$app->language] : Yii::$app->language
                        ]
                    )
                );
            }
        }

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

                if (Yii::$app->request->isPost && !$model->validate()) {
                    var_dump($model->errors);
                    die();
                }

                if($model->save()) {

                    // Set 301-redirect from old URL to new
                    if (isset(Yii::$app->redirects) && ($oldPageUrl !== $newPageUrl) && ($model->status == $model::PAGE_STATUS_PUBLISHED)) {
                        // @TODO: remove old redirects
                        Yii::$app->redirects->set('pages', $oldPageUrl, $newPageUrl, 301);
                    }

                    // Log activity
                    $this->module->logActivity(
                        'Page `' . $model->name . '` with ID `' . $model->id . '` has been successfully updated.',
                        $this->uniqueId . ":" . $this->action->id,
                        'success',
                        1
                    );

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
                    // Log activity
                    $this->module->logActivity(
                        'An error occurred while update the page `' . $model->name . '` with ID `' . $model->id . '`.',
                        $this->uniqueId . ":" . $this->action->id,
                        'danger',
                        1
                    );

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

            // Log activity
            $this->module->logActivity(
                'Page `' . $model->name . '` with ID `' . $model->id . '` has been successfully deleted.',
                $this->uniqueId . ":" . $this->action->id,
                'success',
                1
            );

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
            // Log activity
            $this->module->logActivity(
                'An error occurred while deleting the page `' . $model->name . '` with ID `' . $model->id . '`.',
                $this->uniqueId . ":" . $this->action->id,
                'danger',
                1
            );

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
     * If the private variable $this->_locale contains the locale, the language version is returned.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return Pages the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {

        if (is_null($this->_locale) && ($model = Pages::findOne($id)) !== null) {
            return $model;
        } else {
            if (($model = Pages::findOne(['source_id' => $id, 'locale' => $this->_locale])) !== null)
                return $model;
        }

        throw new NotFoundHttpException(Yii::t('app/modules/pages', 'The requested page does not exist.'));
    }

    /**
     * @return string|null
     */
    public function getLocale() {
        return $this->_locale;
    }

    /**
     * @return string|null
     */
    public function getSourceId() {
        return $this->_source_id;
    }
}
