<?php

use common\helpers\ImageHelper;
use common\models\Author;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var Author $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Авторы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="author-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => ['confirm' => 'Удалить автора?', 'method' => 'post'],
        ]) ?>
    </p>

    <?php if (file_exists(\Yii::getAlias('@frontend/web/' . ImageHelper::getUploadPath($model->id, 'author') . '/original.jpg'))): ?>
        <img src="<?= ImageHelper::getUrl($model->id, 'author') ?>" style="max-width: 300px;">
    <?php endif; ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>
</div>