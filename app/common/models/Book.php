<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $title
 * @property int $year
 * @property string|null $description
 * @property string|null $isbn
 * @property string|null $photo
 * @property int $created_at
 * @property int $updated_at
 * @property string|null $imageFile
 * @property array $author_ids
 *
 * @property Author[] $authors
 */
class Book extends ActiveRecord
{
    /**
     * @var \yii\web\UploadedFile|null
     */
    public $imageFile;

    /**
     * @var array
     */
    public $author_ids = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%book}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'year'], 'required'],
            [['year'], 'integer', 'min' => 1000, 'max' => (int)date('Y') + 1],
            [['title', 'isbn', 'photo'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['imageFile', 'author_ids'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Название',
            'year' => 'Год выпуска',
            'description' => 'Описание',
            'isbn' => 'ISBN',
            'photo' => 'Фото',
            'imageFile' => 'Фото',
            'author_ids' => 'Авторы',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }

    /**
     * Gets query for [[Authors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthors()
    {
        return $this->hasMany(Author::class, ['id' => 'author_id'])
            ->viaTable('{{%book_author}}', ['book_id' => 'id']);
    }
}