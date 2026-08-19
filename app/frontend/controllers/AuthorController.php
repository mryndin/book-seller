<?php

namespace frontend\controllers;

use common\models\Author;
use common\models\Subscription;
use common\models\Book;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Yii;

class AuthorController extends Controller
{
    public function actionIndex()
    {
        $query = Author::find();
        
        // Поиск по имени
        $search = Yii::$app->request->get('search');
        if ($search) {
            $query->andWhere(['like', 'name', $search]);
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 12],
            'sort' => ['defaultOrder' => ['name' => SORT_ASC]],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        // Книги этого автора
        $booksProvider = new ActiveDataProvider([
            'query' => $model->getBooks(),
            'pagination' => ['pageSize' => 12],
        ]);

        return $this->render('view', [
            'model' => $model,
            'booksProvider' => $booksProvider,
        ]);
    }

    public function actionSubscribe($id)
    {
        $author = $this->findModel($id);
        
        if (Yii::$app->request->isPost) {
            $phone = Yii::$app->request->post('phone');
            $userId = Yii::$app->user->id;
            
            // Для гостей обязателен телефон
            if (!$userId && empty($phone)) {
                Yii::$app->session->setFlash('error', 'Укажите телефон для подписки');
                return $this->redirect(['view', 'id' => $id]);
            }
            
            // Проверяем, не подписан ли уже
            $existing = Subscription::find()
                ->where(['author_id' => $id])
                ->andWhere($userId ? ['user_id' => $userId] : ['phone' => $phone])
                ->one();
                
            if ($existing) {
                Yii::$app->session->setFlash('info', 'Вы уже подписаны на этого автора');
            } else {
                $subscription = new Subscription();
                $subscription->author_id = $id;
                $subscription->user_id = $userId;
                $subscription->phone = $phone ?: null;
                $subscription->created_at = time();
                
                if ($subscription->save()) {
                    Yii::$app->session->setFlash('success', 'Вы подписались на автора ' . $author->name);
                } else {
                    Yii::$app->session->setFlash('error', 'Ошибка при подписке');
                }
            }
            
            return $this->redirect(['view', 'id' => $id]);
        }
        
        return $this->redirect(['view', 'id' => $id]);
    }

    protected function findModel($id)
    {
        if (($model = Author::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Страница не найдена.');
    }
}