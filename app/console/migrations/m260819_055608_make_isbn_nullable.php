<?php

use yii\db\Migration;

class m260819_055608_make_isbn_nullable extends Migration
{
    public function safeUp()
    {
        // Убираем старый unique индекс
        $this->dropIndex('idx-book-isbn-unique', '{{%book}}');

        // Меняем колонку на nullable
        $this->alterColumn('{{%book}}', 'isbn', $this->string(20)->null());

        // Возвращаем unique индекс (для MySQL несколько NULL разрешены)
        $this->createIndex('idx-book-isbn-unique', '{{%book}}', 'isbn', true);
    }

    public function safeDown()
    {
        $this->dropIndex('idx-book-isbn-unique', '{{%book}}');
        $this->alterColumn('{{%book}}', 'isbn', $this->string(20)->notNull());
        $this->createIndex('idx-book-isbn-unique', '{{%book}}', 'isbn', true);
    }
}