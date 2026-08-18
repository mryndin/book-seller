<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $author_id
 * @property string|null $phone
 * @property int|null $user_id
 * @property int $created_at
 *
 * @property Author $author
 * @property User|null $user
 */
class Subscription extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%subscription}}';
    }

    public function rules()
    {
        return [
            [['author_id'], 'required'],
            [['author_id', 'user_id', 'created_at'], 'integer'],
            [['phone'], 'string', 'max' => 20],
            [['phone'], 'match', 'pattern' => '/^\+?[0-9\s\-()]{10,18}$/'],
            [['author_id'], 'exist', 'targetClass' => Author::class, 'targetAttribute' => 'id'],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'author_id' => 'Автор',
            'phone' => 'Телефон',
            'user_id' => 'Пользователь',
            'created_at' => 'Подписано',
        ];
    }

    public function getAuthor()
    {
        return $this->hasOne(Author::class, ['id' => 'author_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}