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
        if (is_null($this->getDb()->getSchema()->getTableSchema('{{%pages}}')->getColumn('in_amp')))
            $this->addColumn('{{%pages}}', 'in_amp', $this->boolean()->defaultValue(true)->after('layout'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if ($this->getDb()->getSchema()->getTableSchema('{{%pages}}')->getColumn('in_amp'))
            $this->dropColumn('{{%pages}}', 'in_amp');
    }
}
