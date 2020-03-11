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
        if (is_null($this->getDb()->getSchema()->getTableSchema('{{%pages}}')->getColumn('in_turbo')))
            $this->addColumn('{{%pages}}', 'in_turbo', $this->boolean()->defaultValue(true)->after('layout'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if ($this->getDb()->getSchema()->getTableSchema('{{%pages}}')->getColumn('in_turbo'))
            $this->dropColumn('{{%pages}}', 'in_turbo');
    }
}