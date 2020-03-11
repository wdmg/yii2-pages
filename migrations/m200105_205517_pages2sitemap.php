<?php

use yii\db\Migration;

/**
 * Class m200105_205517_pages2sitemap
 */
class m200105_205517_pages2sitemap extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if (is_null($this->getDb()->getSchema()->getTableSchema('{{%pages}}')->getColumn('in_sitemap')))
            $this->addColumn('{{%pages}}', 'in_sitemap', $this->boolean()->defaultValue(true)->after('layout'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if ($this->getDb()->getSchema()->getTableSchema('{{%pages}}')->getColumn('in_sitemap'))
            $this->dropColumn('{{%pages}}', 'in_sitemap');
    }
}