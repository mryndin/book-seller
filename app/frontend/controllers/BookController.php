<?php

namespace frontend\controllers;

use common\models\Book;
use common\models\Author;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class BookController extends Controller
{
    public function actionIndex()
    {
        $query = Book::find();
        
        // Фильтр по году
        $year = \Yii::$app->request->get('year');
        if ($year) {
            $query->andWhere(['year' => $year]);
        }
        
        // Поиск по названию
        $search = \Yii::$app->request->get('search');
        if ($search) {
            $query->andWhere(['like', 'title', $search]);
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 12],
            'sort' => ['defaultOrder' => ['year' => SORT_DESC]],
        ]);

        // Получаем список годов для фильтра
        $years = Book::find()->select('year')->distinct()->orderBy(['year' => SORT_DESC])->column();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'years' => $years,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = Book::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Страница не найдена.');
    }
}