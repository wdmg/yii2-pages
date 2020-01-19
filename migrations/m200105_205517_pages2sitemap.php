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
        $this->addColumn('{{%pages}}', 'in_sitemap', $this->boolean()->defaultValue(true)->after('layout'));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%pages}}', 'in_sitemap');
    }
}
