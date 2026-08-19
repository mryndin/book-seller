<?php

use common\helpers\ImageHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use Yii;

/** @var yii\web\View $this */
/** @var common\models\Author $model */
/** @var yii\widgets\ActiveForm $form */

// Проверяем, есть ли текущая картинка
$currentImageUrl = null;
if (!$model->isNewRecord) {
    $uploadPath = ltrim(ImageHelper::getUploadPath($model->id, 'author'), '/');
    $uploadRoot = ImageHelper::UPLOAD_ROOT;
    $extensions = ['jpg', 'png', 'gif', 'webp'];

    foreach ($extensions as $ext) {
        if (file_exists("{$uploadRoot}/{$uploadPath}/original.{$ext}")) {
            $currentImageUrl = Yii::$app->request->hostInfo . "/uploads/{$uploadPath}/original.{$ext}";
            break;
        }
    }
}
?>

<div class="author-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <div class="form-group field-author-imagefile">
        <label class="control-label" for="author-imagefile">Фото</label>

        <?php if ($currentImageUrl): ?>
            <div style="margin-bottom: 10px;">
                <img src="<?= $currentImageUrl ?>" style="max-width: 200px; border: 1px solid #ddd; padding: 5px;">
                <p class="help-block">Текущее фото. Выберите новый файл, чтобы заменить его.</p>
            </div>
        <?php endif; ?>

        <!-- Стандартный инпут Yii2 -->
        <?= Html::activeFileInput($model, 'imageFile', [
            'id' => 'author-imagefile',
            'class' => 'form-control',
            'accept' => 'image/*'
        ]) ?>

        <!-- Сюда JS выведет превью нового файла -->
        <img id="author-preview" src="#" style="max-width: 200px; display: none; margin-top: 10px; border: 1px solid #ddd;">
    </div>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script>
    document.getElementById('author-imagefile').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('author-preview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });
</script>