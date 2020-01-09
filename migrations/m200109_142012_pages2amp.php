<?php

use yii\db\Migration;

/**
 * Class m200109_142012_pages2amp
 */
class m200109_142012_pages2amp extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%pages}}', 'in_amp', $this->boolean()->defaultValue(true));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%pages}}', 'in_amp');
    }
}
