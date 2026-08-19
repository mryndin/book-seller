<?php
use common\helpers\ImageHelper;
use yii\helpers\Html;
use yii\helpers\Url;
/** @var common\models\Book $model */
?>
<div class="book-card" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
    <div style="display: flex;">
        <div style="margin-right: 20px;">
            <?php
            // Проверяем существование файла через UPLOAD_ROOT
            $uploadPath = ImageHelper::UPLOAD_ROOT . '/' . ltrim(ImageHelper::getUploadPath($model->id, 'book'), '/');
            $hasImage = false;
            foreach (['jpg', 'png', 'gif', 'webp'] as $ext) {
                if (file_exists("{$uploadPath}/original.{$ext}")) {
                    $hasImage = true;
                    break;
                }
            }
            ?>

            <?php if ($hasImage): ?>
                <img src="<?= ImageHelper::getThumbUrl($model->id, 'book') ?>"
                     alt="<?= Html::encode($model->title) ?>"
                     style="width: 150px; height: auto;">
            <?php else: ?>
                <div style="width: 150px; height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                    Нет фото
                </div>
            <?php endif; ?>
        </div>
        <div style="flex: 1;">
            <h3><?= Html::a(Html::encode($model->title), ['view', 'id' => $model->id]) ?></h3>
            <p><strong>Год:</strong> <?= $model->year ?></p>
            <?php if ($model->isbn): ?>
                <p><strong>ISBN:</strong> <?= Html::encode($model->isbn) ?></p>
            <?php endif; ?>
            <p><strong>Авторы:</strong>
                <?= implode(', ', array_map(function($author) {
                    return Html::a(Html::encode($author->name), ['/author/view', 'id' => $author->id]);
                }, $model->authors)) ?>
            </p>
            <?php if ($model->description): ?>
                <p><?= Html::encode(mb_substr($model->description, 0, 150)) ?><?= mb_strlen($model->description) > 150 ? '...' : '' ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>