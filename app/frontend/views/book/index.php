<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $years */

$this->title = 'Каталог книг';

$search = Yii::$app->request->get('search', '');
$year = Yii::$app->request->get('year', '');
?>

    <h1>Каталог книг</h1>

    <!-- Фильтры через ActiveForm -->
    <div class="filters" style="margin-bottom: 20px;">
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['book/index'],
            'options' => ['class' => 'form-inline']
        ]); ?>

        <input type="text" name="search" value="<?= Html::encode($search) ?>"
               class="form-control" placeholder="Поиск по названию"
               style="margin-right: 10px; width: auto; display: inline-block;">

        <select name="year" class="form-control" style="margin-right: 10px; width: auto; display: inline-block;">
            <option value="">Все годы</option>
            <?php foreach ($years as $y): ?>
                <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>>
                    <?= $y ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?= Html::submitButton('Применить', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Сбросить', ['book/index'], ['class' => 'btn btn-secondary']) ?>

        <?php ActiveForm::end(); ?>
    </div>

<?= ListView::widget([
    'dataProvider' => $dataProvider,
    'itemOptions' => ['class' => 'item'],
    'layout' => "{items}\n{pager}",
    'itemView' => function ($model, $key, $index, $widget) {
        return $this->render('_book-card', ['model' => $model]);
    },
]); ?>