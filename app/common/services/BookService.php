<?php

namespace common\services;

use common\helpers\ImageHelper;
use common\models\Author;
use common\models\Book;
use common\models\Subscription;
use Yii;
use yii\db\Transaction;
use yii\web\UploadedFile;

class BookService
{
    public function create(array $data): Book
    {
        $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);
        try {
            $book = new Book();
            $book->title = $data['title'] ?? '';
            $book->year = $data['year'] ?? date('Y');
            $book->description = $data['description'] ?? null;
            $book->isbn = $data['isbn'] ?? null;

            if ($book->save()) {
                if (isset($data['imageFile']) && $data['imageFile'] instanceof UploadedFile) {
                    ImageHelper::saveImage($data['imageFile'], $book->id, 'book');
                }

                $this->syncAuthors($book, $data['author_ids'] ?? []);

                // Триггер SMS при создании книги
                $this->notifySubscribers($book);

                $transaction->commit();
                return $book;
            }
            $transaction->rollBack();
            return $book;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function update(Book $book, array $data): Book
    {
        $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);
        try {
            $book->title = $data['title'] ?? $book->title;
            $book->year = $data['year'] ?? $book->year;
            $book->description = $data['description'] ?? $book->description;
            $book->isbn = $data['isbn'] ?? $book->isbn;

            if ($book->save()) {
                if (isset($data['imageFile']) && $data['imageFile'] instanceof UploadedFile) {
                    ImageHelper::saveImage($data['imageFile'], $book->id, 'book');
                }

                $this->syncAuthors($book, $data['author_ids'] ?? []);

                $transaction->commit();
                return $book;
            }
            $transaction->rollBack();
            return $book;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function delete(Book $book): bool
    {
        ImageHelper::deleteImage($book->id, 'book');
        return (bool)$book->delete();
    }

    private function syncAuthors(Book $book, array $authorIds): void
    {
        $book->unlinkAll('authors', true);
        foreach ($authorIds as $authorId) {
            $author = Author::findOne($authorId);
            if ($author) {
                $book->link('authors', $author);
            }
        }
    }

    private function notifySubscribers(Book $book): void
    {
        $authorIds = array_column($book->authors, 'id');
        if (empty($authorIds)) return;

        $phones = Subscription::find()
            ->select('phone')
            ->where(['author_id' => $authorIds])
            ->andWhere(['not', ['phone' => null]])
            ->column();

        if (!empty($phones)) {
            // TODO: Отправка в RabbitMQ
            Yii::info("SMS trigger: " . count($phones) . " phones for book '{$book->title}'", 'sms');
        }
    }
}