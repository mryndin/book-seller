<?php
use common\helpers\ImageHelper;
use yii\helpers\Html;
use yii\widgets\ListView;

/** @var common\models\Author $model */
/** @var yii\data\ActiveDataProvider $booksProvider */

$this->title = $model->name;

// Проверяем существование файла через UPLOAD_ROOT
$uploadPath = ImageHelper::UPLOAD_ROOT . '/' . ltrim(ImageHelper::getUploadPath($model->id, 'author'), '/');
$hasImage = false;
foreach (['jpg', 'png', 'gif', 'webp'] as $ext) {
    if (file_exists("{$uploadPath}/original.{$ext}")) {
        $hasImage = true;
        break;
    }
}
?>

    <h1><?= Html::encode($model->name) ?></h1>

    <div style="margin-bottom: 30px;">
        <?php if ($hasImage): ?>
            <img src="<?= ImageHelper::getUrl($model->id, 'author') ?>"
                 alt="<?= Html::encode($model->name) ?>"
                 style="max-width: 200px; height: auto; margin-bottom: 15px; border-radius: 50%; border: 3px solid #e9ecef;">
        <?php endif; ?>

        <!-- Кнопка подписки -->
        <?php if (Yii::$app->user->isGuest): ?>
            <!-- Гость - показываем форму с телефоном -->
            <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <h3>Подписаться на новые книги</h3>
                <?php $form = \yii\widgets\ActiveForm::begin(['method' => 'post', 'action' => ['subscribe', 'id' => $model->id]]); ?>
                <input type="text" name="phone" placeholder="+7 (999) 123-45-67" class="form-control" style="margin-bottom: 10px;" required>
                <button type="submit" class="btn btn-success">Подписаться</button>
                <?php \yii\widgets\ActiveForm::end(); ?>
            </div>
        <?php else: ?>
            <!-- Авторизованный пользователь -->
            <?php
            $subscribed = \common\models\Subscription::find()
                ->where(['author_id' => $model->id, 'user_id' => Yii::$app->user->id])
                ->exists();

            $user = Yii::$app->user->identity;
            ?>

            <?php if ($subscribed): ?>
                <span class="btn btn-success disabled">✔ Вы уже подписаны</span>
            <?php else: ?>
                <?php $form = \yii\widgets\ActiveForm::begin(['method' => 'post', 'action' => ['subscribe', 'id' => $model->id]]); ?>

                <?php if (empty($user->phone)): ?>
                    <!-- Если телефон не указан в профиле, просим ввести -->
                    <p class="text-muted small">Укажите телефон для получения уведомлений:</p>
                    <input type="text" name="phone" placeholder="+7 (999) 123-45-67" class="form-control mb-2" required>
                <?php else: ?>
                    <!-- Если телефон есть, показываем его и скрываем инпут -->
                    <p class="text-muted small">Уведомления придут на: <strong><?= Html::encode($user->phone) ?></strong></p>
                    <input type="hidden" name="phone" value="<?= Html::encode($user->phone) ?>">
                <?php endif; ?>

                <button type="submit" class="btn btn-success">Подписаться на автора</button>
                <?php \yii\widgets\ActiveForm::end(); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <h2>Книги автора</h2>
<?= ListView::widget([
    'dataProvider' => $booksProvider,
    'itemOptions' => ['class' => 'item'],
    'layout' => "{items}\n{pager}",
    'itemView' => function ($model, $key, $index, $widget) {
        return $this->render('/book/_book-card', ['model' => $model]);
    },
]); ?>