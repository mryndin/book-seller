<?php

namespace backend\controllers;

use common\models\Author;
use common\services\AuthorService;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;

class AuthorController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST']],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new \common\models\AuthorSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    public function actionCreate()
    {
        $model = new Author();
        $service = new AuthorService();

        if ($this->request->isPost) {
            $data = $this->request->post('Author', []);
            $data['imageFile'] = UploadedFile::getInstance($model, 'imageFile');

            $model = $service->create($data);

            if (!$model->hasErrors()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $service = new AuthorService();

        if ($this->request->isPost) {
            $data = $this->request->post('Author', []);
            $uploadedFile = UploadedFile::getInstance($model, 'imageFile');

            $data['imageFile'] = $uploadedFile;
            $model = $service->update($model, $data);

            if (!$model->hasErrors()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $service = new AuthorService();
        $service->delete($this->findModel($id));

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Author::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Страница не найдена.');
    }
}