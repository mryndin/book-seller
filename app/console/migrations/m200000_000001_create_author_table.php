<?php

use yii\db\Migration;

class m200000_000001_create_author_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%author}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Index for search and sort by name
        $this->createIndex('idx-author-name', '{{%author}}', 'name');
    }

    public function safeDown()
    {
        $this->dropTable('{{%author}}');
    }
}