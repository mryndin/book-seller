<?php

namespace common\services;

use common\helpers\ImageHelper;
use common\models\Author;
use common\models\Book;
use common\models\Subscription;
use Yii;
use yii\db\Transaction;
use yii\web\UploadedFile;

/**
 * Service class for managing books.
 */
class BookService
{
    /**
     * Creates a new book with the provided data.
     *
     * @param array $data The data for creating the book.
     * @return Book The created book instance.
     * @throws \Exception If an error occurs during the transaction.
     */
    public function create(array $data): Book
    {
        $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);
        try {
            $book = new Book();
            $book->title = $data['title'] ?? '';
            $book->year = $data['year'] ?? date('Y');
            $book->description = $data['description'] ?? null;
            $book->isbn = !empty($data['isbn']) ? $data['isbn'] : null;

            if ($book->save()) {
                if (isset($data['imageFile']) && $data['imageFile'] instanceof UploadedFile) {
                    ImageHelper::saveImage($data['imageFile'], $book->id, 'book');
                }

                $this->syncAuthors($book, $data['author_ids'] ?? []);

                // Триггер SMS при создании книги через RabbitMQ
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

    /**
     * Updates an existing book with the provided data.
     *
     * @param Book $book The book instance to update.
     * @param array $data The data for updating the book.
     * @return Book The updated book instance.
     * @throws \Exception If an error occurs during the transaction.
     */
    public function update(Book $book, array $data): Book
    {
        $transaction = Yii::$app->db->beginTransaction(Transaction::SERIALIZABLE);
        try {
            $book->title = $data['title'] ?? $book->title;
            $book->year = $data['year'] ?? $book->year;
            $book->description = $data['description'] ?? $book->description;
            $book->isbn = !empty($data['isbn']) ? $data['isbn'] : null;

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

    /**
     * Deletes a book and its associated image.
     *
     * @param Book $book The book instance to delete.
     * @return bool True if the deletion was successful, false otherwise.
     */
    public function delete(Book $book): bool
    {
        ImageHelper::deleteImage($book->id, 'book');
        return (bool)$book->delete();
    }

    /**
     * Synchronizes the authors associated with a book.
     *
     * @param Book $book The book instance.
     * @param array $authorIds The array of author IDs to associate with the book.
     */
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

    /**
     * Notifies subscribers about a new book via SMS.
     *
     * @param Book $book The book instance for which to notify subscribers.
     */
    private function notifySubscribers(Book $book): void
    {
        $authorIds = array_column($book->authors, 'id');
        if (empty($authorIds)) {
            return;
        }

        $phones = Subscription::find()
            ->select('phone')
            ->where(['author_id' => $authorIds])
            ->andWhere(['not', ['phone' => null]])
            ->column();

        if (empty($phones)) {
            return;
        }

        try {
            $producer = new SmsProducer();
            $producer->notifyAboutNewBook($book->id, $authorIds, $phones);
        } catch (\Exception $e) {
            // Логируем, но не ломаем создание книги
            Yii::error("RabbitMQ error: " . $e->getMessage(), 'rabbitmq');
        }
    }
}