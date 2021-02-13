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
            'route' => $this->string(255)->null(),
            'layout' => $this->string(64)->null(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer(11)->null(),
            'updated_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_by' => $this->integer(11)->null(),
        ], $tableOptions);

        $this->createIndex('{{%idx-pages-alias}}', '{{%pages}}', ['name', 'alias']);
        $this->createIndex('{{%idx-pages-status}}', '{{%pages}}', ['status']);

        // If exist module `Users` set index and foreign key `created_by`, `updated_by` to `users.id`
        if (class_exists('\wdmg\users\models\Users')) {
            $this->createIndex('{{%idx-pages-author}}','{{%pages}}', ['created_by', 'updated_by'],false);
            $userTable = \wdmg\users\models\Users::tableName();
            $this->addForeignKey(
                'fk_pages_to_users1',
                '{{%pages}}',
                'created_by',
                $userTable,
                'id',
                'NO ACTION',
                'CASCADE'
            );
            $this->addForeignKey(
                'fk_pages_to_users2',
                '{{%pages}}',
                'updated_by',
                $userTable,
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
        $this->dropIndex('{{%idx-pages-alias}}', '{{%pages}}');
        $this->dropIndex('{{%idx-pages-status}}', '{{%pages}}');

        if (class_exists('\wdmg\users\models\Users')) {
            $this->dropIndex('{{%idx-pages-author}}', '{{%pages}}');
            $userTable = \wdmg\users\models\Users::tableName();
            if (!(Yii::$app->db->getTableSchema($userTable, true) === null)) {
                $this->dropForeignKey(
                    'fk_pages_to_users1',
                    '{{%pages}}'
                );
                $this->dropForeignKey(
                    'fk_pages_to_users2',
                    '{{%pages}}'
                );
            }
        }

        $this->truncateTable('{{%pages}}');
        $this->dropTable('{{%pages}}');
    }

}
