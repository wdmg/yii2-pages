<?php

use yii\db\Migration;

/**
 * Class m200109_141905_pages2turbo
 */
class m200109_141905_pages2turbo extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%pages}}', 'in_turbo', $this->boolean()->defaultValue(true));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%pages}}', 'in_turbo');
    }
}
