<?php

use yii\db\Migration;

class m260819_052102_add_phone_to_user_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'phone', $this->string(20)->after('email'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'phone');
    }
}