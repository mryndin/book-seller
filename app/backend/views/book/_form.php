<?php

use kartik\select2\Select2;
use common\helpers\ImageHelper;
use common\models\Author;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use Yii;

/** @var yii\web\View $this */
/** @var common\models\Book $model */
/** @var yii\widgets\ActiveForm $form */

$authors = ArrayHelper::map(Author::find()->orderBy('name')->all(), 'id', 'name');
$selectedAuthors = $model->isNewRecord ? [] : ArrayHelper::map($model->authors, 'id', 'id');

// Проверяем, есть ли текущая картинка
$currentImageUrl = null;
if (!$model->isNewRecord) {
    $uploadPath = ltrim(ImageHelper::getUploadPath($model->id, 'book'), '/');
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

<div class="book-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'year')->textInput(['type' => 'number']) ?>

    <?= $form->field($model, 'isbn')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'author_ids')->widget(Select2::class, [
        'data' => $authors,
        'options' => [
            'placeholder' => 'Выберите авторов...',
            'multiple' => true,
            'value' => $selectedAuthors,
        ],
        'pluginOptions' => ['allowClear' => true],
    ])->label('Авторы') ?>

    <div class="form-group field-book-imagefile">
        <label class="control-label" for="book-imagefile">Фото</label>

        <?php if ($currentImageUrl): ?>
            <div style="margin-bottom: 10px;">
                <img src="<?= $currentImageUrl ?>" style="max-width: 200px; border: 1px solid #ddd; padding: 5px;">
                <p class="help-block">Текущее фото. Выберите новый файл, чтобы заменить его.</p>
            </div>
        <?php endif; ?>

        <?= Html::activeFileInput($model, 'imageFile', [
            'id' => 'book-imagefile',
            'class' => 'form-control',
            'accept' => 'image/*'
        ]) ?>

        <img id="book-preview" src="#" style="max-width: 200px; display: none; margin-top: 10px; border: 1px solid #ddd;">
    </div>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script>
    document.getElementById('book-imagefile').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('book-preview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });
</script>