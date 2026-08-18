<?php

use yii\db\Migration;

class m200000_000002_create_book_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%book}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'year' => $this->integer()->notNull(),
            'description' => $this->text(),
            'isbn' => $this->string(20),
            'photo' => $this->string(255),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Index for TOP-10 report (WHERE year = ?)
        $this->createIndex('idx-book-year', '{{%book}}', 'year');
        
        // Index for search and sort by title
        $this->createIndex('idx-book-title', '{{%book}}', 'title');
        
        // Unique index for ISBN
        $this->createIndex('idx-book-isbn-unique', '{{%book}}', 'isbn', true);
    }

    public function safeDown()
    {
        $this->dropTable('{{%book}}');
    }
}