<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class BookSearch extends Book
{
    public function rules()
    {
        return [
            [['id', 'year'], 'integer'],
            [['title', 'isbn'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Book::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['id' => $this->id, 'year' => $this->year]);
        $query->andFilterWhere(['like', 'title', $this->title]);
        $query->andFilterWhere(['like', 'isbn', $this->isbn]);

        return $dataProvider;
    }
}