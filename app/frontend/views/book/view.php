<?php

use common\helpers\ImageHelper;
use yii\helpers\Html;

/** @var common\models\Book $model */
$this->title = $model->title;

// Проверяем существование файла
$uploadPath = ImageHelper::UPLOAD_ROOT . '/' . ltrim(ImageHelper::getUploadPath($model->id, 'book'), '/');
$hasImage = false;
foreach (['jpg', 'png', 'gif', 'webp'] as $ext) {
    if (file_exists("{$uploadPath}/original.{$ext}")) {
        $hasImage = true;
        break;
    }
}
?>
    <h1><?= Html::encode($model->title) ?></h1>
    <div style="display: flex; margin-bottom: 30px;">
        <div style="margin-right: 30px;">
            <?php if ($hasImage): ?>
                <img src="<?= ImageHelper::getUrl($model->id, 'book') ?>"
                     alt="<?= Html::encode($model->title) ?>"
                     style="max-width: 300px; height: auto;">
            <?php endif; ?>
        </div>
        <div>
            <p><strong>Год выпуска:</strong> <?= $model->year ?></p>
            <?php if ($model->isbn): ?>
                <p><strong>ISBN:</strong> <?= Html::encode($model->isbn) ?></p>
            <?php endif; ?>
            <p><strong>Авторы:</strong>
                <?= implode(', ', array_map(function ($author) {
                    return Html::a(Html::encode($author->name), ['/author/view', 'id' => $author->id]);
                }, $model->authors)) ?>
            </p>
            <?php if ($model->description): ?>
                <p><strong>Аннотация:</strong><?= nl2br(Html::encode($model->description)) ?></p>
            <?php endif; ?>
        </div>
    </div>
<?= Html::a('← Назад к списку', ['index'], ['class' => 'btn btn-secondary']) ?>