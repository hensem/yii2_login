<?php

use yii\db\Migration;

/**
 * Class m181002_070638_authclient
 */
class m181002_070638_authclient extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181002_070638_authclient cannot be reverted.\n";

        return false;
    }

    public function up()
    {
        $this->createTable('auth', [
           'id' => 'bigint UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT',
                    'uid' => 'bigint UNSIGNED NOT NULL',
                    'source' => $this->string()->notNull(),
                    'source_id' => $this->string()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('auth');
    }
}
