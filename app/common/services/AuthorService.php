<?php

namespace common\services;

use common\helpers\ImageHelper;
use common\models\Author;
use yii\web\UploadedFile;

/**
 * Service class for managing authors.
 */
class AuthorService
{
    /**
     * Creates a new author.
     *
     * @param array $data The data for the new author.
     * @return Author The created author instance.
     */
    public function create(array $data): Author
    {
        $author = new Author();
        $author->name = $data['name'];
        
        if ($author->save()) {
            if (isset($data['imageFile']) && $data['imageFile'] instanceof UploadedFile) {
                ImageHelper::saveImage($data['imageFile'], $author->id, 'author');
            }
            return $author;
        }
        return $author;
    }

    /**
     * Updates an existing author.
     *
     * @param Author $author The author instance to update.
     * @param array $data The new data for the author.
     * @return Author The updated author instance.
     */
    public function update(Author $author, array $data): Author
    {
        $author->name = $data['name'];
        
        if ($author->save()) {
            if (isset($data['imageFile']) && $data['imageFile'] instanceof UploadedFile) {
                if (!ImageHelper::saveImage($data['imageFile'], $author->id, 'author')) {
                    $author->addError('imageFile', 'Не удалось сохранить изображение.');
                }
            }
            return $author;
        }
        return $author;
    }

    /**
     * Deletes an author and its associated image.
     *
     * @param Author $author The author instance to delete.
     * @return bool True if the deletion was successful, false otherwise.
     */
    public function delete(Author $author): bool
    {
        ImageHelper::deleteImage($author->id, 'author');
        return (bool)$author->delete();
    }
}