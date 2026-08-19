<?php

namespace common\services;

use common\helpers\ImageHelper;
use common\models\Author;
use yii\web\UploadedFile;

class AuthorService
{
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

    public function delete(Author $author): bool
    {
        ImageHelper::deleteImage($author->id, 'author');
        return (bool)$author->delete();
    }
}