<?php

use yii\db\Migration;

/**
 * Class m190725_005152_pages
 */
class m190725_005152_pages extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%pages}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(128)->notNull(),
            'alias' => $this->string(128)->notNull(),
            'content' => $this->text()->null(),
            'title' => $this->string(255)->null(),
            'description' => $this->string(255)->null(),
            'keywords' => $this->string(255)->null(),
            'status' => $this->tinyInteger(1)->null()->defaultValue(0),
            'route' => $this->string(32)->null(),
            'layout' => $this->string(64)->null(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer(11)->notNull()->defaultValue(0),
            'updated_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_by' => $this->integer(11)->notNull()->defaultValue(0),
        ], $tableOptions);

        $this->createIndex('{{%idx-pages-alias}}', '{{%pages}}', ['name', 'alias']);
        $this->createIndex('{{%idx-pages-status}}', '{{%pages}}', ['alias', 'status']);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->truncateTable('{{%pages}}');
        $this->dropIndex('{{%idx-pages-alias}}', '{{%pages}}');
        $this->dropIndex('{{%idx-pages-status}}', '{{%pages}}');
        $this->dropTable('{{%pages}}');
    }

}
