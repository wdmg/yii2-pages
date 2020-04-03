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
        if (is_null($this->getDb()->getSchema()->getTableSchema('{{%pages}}')->getColumn('parent_id'))) {
            $this->addColumn('{{%pages}}', 'parent_id', $this->integer(11)->null()->after('id'));

            // Setup foreign key to parent id
            $this->createIndex('{{%idx-pages-parent}}', '{{%pages}}', ['parent_id']);
            $this->addForeignKey(
                'fk_pages_to_parent',
                '{{%pages}}',
                'parent_id',
                '{{%pages}}',
                'id',
                'NO ACTION',
                'CASCADE'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if (!is_null($this->getDb()->getSchema()->getTableSchema('{{%pages}}')->getColumn('parent_id'))) {
            $this->dropIndex('{{%idx-pages-parent}}', '{{%pages}}');
            $this->dropColumn('{{%pages}}', 'parent_id');
            $this->dropForeignKey(
                'fk_pages_to_parent',
                '{{%pages}}'
            );
        }
    }
}
