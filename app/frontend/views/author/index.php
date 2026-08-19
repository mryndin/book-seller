<?php

use yii\helpers\Html;
use yii\widgets\ListView;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Авторы';

$search = Yii::$app->request->get('search', '');
?>

    <h1>Авторы</h1>

    <div style="margin-bottom: 20px;">
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['author/index'], // Yii сам сгенерирует правильный URL
            'options' => ['class' => 'form-inline']
        ]); ?>

        <input type="text" name="search" value="<?= Html::encode($search) ?>"
               class="form-control" placeholder="Поиск по имени"
               style="margin-right: 10px; width: auto; display: inline-block;">

        <?= Html::submitButton('Найти', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Сбросить', ['author/index'], ['class' => 'btn btn-secondary']) ?>

        <?php ActiveForm::end(); ?>
    </div>

<?= ListView::widget([
    'dataProvider' => $dataProvider,
    'itemOptions' => ['class' => 'item'],
    'layout' => "{items}\n{pager}",
    'itemView' => function ($model, $key, $index, $widget) {
        return $this->render('_author-card', ['model' => $model]);
    },
]); ?>