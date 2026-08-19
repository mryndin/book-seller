<?php
declare(strict_types=1);
/** @var yii\web\View $this */
/** @var array $topAuthors */
/** @var int $currentYear */

use yii\helpers\Html;

$this->title = 'ТОП-10 авторов года';
?>

<div class="site-index py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h2 class="h4 mb-0">🏆 ТОП-10 авторов <?= $currentYear ?> года</h2>
            <p class="mb-0 small opacity-75">Рейтинг составлен по количеству опубликованных книг</p>
        </div>

        <div class="card-body p-0">
            <?php if (empty($topAuthors)): ?>
                <div class="p-5 text-center text-muted">
                    <h4 class="mb-3">📚 Пока тихо...</h4>
                    <p class="mb-4">В этом году не было опубликовано ни одной книги.</p>
                    <?= Html::a('Посмотреть весь каталог', ['/book/index'], ['class' => 'btn btn-outline-primary']) ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                        <tr>
                            <th scope="col" class="text-center" style="width: 100px;">Место</th>
                            <th scope="col">Автор</th>
                            <th scope="col" class="text-center" style="width: 180px;">Книг в <?= $currentYear ?> г.</th>
                            <th scope="col" class="text-center" style="width: 130px;">Действие</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($topAuthors as $index => $author): ?>
                            <?php
                            $rank = $index + 1;
                            // Цвета для призовых мест
                            $badgeClass = match($rank) {
                                1 => 'bg-warning text-dark', // Золото
                                2 => 'bg-secondary text-white', // Серебро
                                3 => 'bg-danger text-white', // Бронза
                                default => 'bg-light text-dark border',
                            };
                            ?>
                            <tr>
                                <td class="text-center">
                                    <span class="badge rounded-pill <?= $badgeClass ?> fs-6 px-3 py-2"><?= $rank ?></span>
                                </td>
                                <td>
                                    <strong class="fs-5"><?= Html::encode($author['name']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary fs-6 px-3 py-2"><?= $author['book_count'] ?></span>
                                </td>
                                <td class="text-center">
                                    <?= Html::a('Профиль', ['/author/view', 'id' => $author['id']], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="card-footer bg-light text-center py-3">
            <?= Html::a('Перейти к каталогу книг', ['/book/index'], ['class' => 'btn btn-primary me-2']) ?>
            <?= Html::a('Список всех авторов', ['/author/index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
</div>