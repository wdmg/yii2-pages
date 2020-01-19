<?php

use yii\db\Migration;

/**
 * Class m200114_220922_pages_parents
 */
class m200114_220922_pages_parents extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%pages}}', 'parent_id', $this->integer(11)->null()->after('id'));
        $this->createIndex('{{%idx-pages-parent}}', '{{%pages}}', ['parent_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('{{%idx-pages-parent}}', '{{%pages}}');
        $this->dropColumn('{{%pages}}', 'parent_id');
    }
}
