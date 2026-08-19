<?php

use common\helpers\ImageHelper;
use common\models\Book;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var Book $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Книги', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="book-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => ['confirm' => 'Удалить книгу?', 'method' => 'post'],
        ]) ?>
    </p>

    <?php if (file_exists(\Yii::getAlias('@frontend/web/' . ImageHelper::getUploadPath($model->id, 'book') . '/original.jpg'))): ?>
        <img src="<?= ImageHelper::getUrl($model->id, 'book') ?>" style="max-width: 300px;">
    <?php endif; ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title',
            'year',
            'isbn',
            'description:ntext',
            [
                'label' => 'Авторы',
                'value' => implode(', ', array_map(fn($a) => $a->name, $model->authors)),
            ],
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>
</div>