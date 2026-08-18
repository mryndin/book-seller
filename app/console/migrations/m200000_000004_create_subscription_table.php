<?php

use yii\db\Migration;

class m200000_000004_create_subscription_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%subscription}}', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'phone' => $this->string(20),
            'user_id' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
        ]);

        // Index for FK
        $this->createIndex('idx-subscription-author_id', '{{%subscription}}', 'author_id');
        $this->createIndex('idx-subscription-user_id', '{{%subscription}}', 'user_id');

        // Index for SMS trigger (SELECT phone WHERE author_id IN (...))
        $this->createIndex('idx-subscription-author_id-phone', '{{%subscription}}', ['author_id', 'phone']);

        // Unique composite index: one user cannot subscribe twice to same author
        $this->createIndex(
            'idx-subscription-user_author-unique',
            '{{%subscription}}',
            ['author_id', 'user_id'],
            true
        );

        // Unique composite index: one phone cannot subscribe twice to same author
        $this->createIndex(
            'idx-subscription-phone_author-unique',
            '{{%subscription}}',
            ['author_id', 'phone'],
            true
        );

        $this->addForeignKey(
            'fk-subscription-author_id',
            '{{%subscription}}',
            'author_id',
            '{{%author}}',
            'id',
            'CASCADE'
        );

        // Добавляем FK к user только если таблица существует
        $tableSchema = $this->db->schema->getTableSchema('{{%user}}');
        if ($tableSchema !== null) {
            $this->addForeignKey(
                'fk-subscription-user_id',
                '{{%subscription}}',
                'user_id',
                '{{%user}}',
                'id',
                'CASCADE'
            );
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%subscription}}');
    }
}