<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property int $created_at
 * @property int $updated_at
 * @property string|null $imageFile
 *
 * @property Book[] $books
 */
class Author extends ActiveRecord
{
    public $imageFile;

    public static function tableName()
    {
        return '{{%author}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['imageFile'], 'image', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, gif, webp', 'maxSize' => 1024 * 1024 * 5],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'ФИО',
            'imageFile' => 'Фото',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }

    public function getBooks()
    {
        return $this->hasMany(Book::class, ['id' => 'book_id'])
            ->viaTable('{{%book_author}}', ['author_id' => 'id']);
    }
}