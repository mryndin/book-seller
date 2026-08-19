<?php
use common\helpers\ImageHelper;
use yii\helpers\Html;

/** @var common\models\Author $model */

// Проверяем существование файла через UPLOAD_ROOT (как мы делали для книг)
$uploadPath = ImageHelper::UPLOAD_ROOT . '/' . ltrim(ImageHelper::getUploadPath($model->id, 'author'), '/');
$hasImage = false;
foreach (['jpg', 'png', 'gif', 'webp'] as $ext) {
    if (file_exists("{$uploadPath}/original.{$ext}")) {
        $hasImage = true;
        break;
    }
}
?>

<div class="author-card" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; display: flex; align-items: center; background: #fff;">
    <div style="margin-right: 20px; flex-shrink: 0;">
        <?php if ($hasImage): ?>
            <img src="<?= ImageHelper::getThumbUrl($model->id, 'author') ?>"
                 alt="<?= Html::encode($model->name) ?>"
                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%; border: 2px solid #e9ecef;">
        <?php else: ?>
            <div style="width: 80px; height: 80px; background: #f8f9fa; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #e9ecef;">
                <span style="font-size: 2rem; color: #adb5bd;">👤</span>
            </div>
        <?php endif; ?>
    </div>

    <div style="flex: 1;">
        <?= Html::a(Html::encode($model->name), ['view', 'id' => $model->id], ['class' => 'h4 mb-0 text-decoration-none text-dark']) ?>
    </div>
</div>