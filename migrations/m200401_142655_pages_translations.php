<?php

use yii\db\Migration;

/**
 * Class m200401_142655_pages_translations
 */
class m200401_142655_pages_translations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $defaultLocale = null;
        if (isset(Yii::$app->sourceLanguage))
            $defaultLocale = Yii::$app->sourceLanguage;

        if (is_null($this->getDb()->getSchema()->getTableSchema('{{%pages}}')->getColumn('source_id'))) {
            $this->addColumn('{{%pages}}', 'source_id', $this->integer(11)->null()->after('parent_id'));

            // Setup foreign key to source id
            $this->createIndex('{{%idx-pages-source}}', '{{%pages}}', ['source_id']);
            $this->addForeignKey(
                'fk_pages_to_source',
                '{{%pages}}',
                'source_id',
                '{{%pages}}',
                'id',
                'NO ACTION',
                'CASCADE'
            );

        }
        if (is_null($this->getDb()->getSchema()->getTableSchema('{{%pages}}')->getColumn('locale'))) {
            $this->addColumn('{{%pages}}', 'locale', $this->string(10)->defaultValue($defaultLocale)->after('status'));
            $this->createIndex('{{%idx-pages-locale}}', '{{%pages}}', ['locale']);

            // If module `Translations` exist setup foreign key `locale` to `trans_langs.locale`
            if (class_exists('\wdmg\translations\models\Languages')) {
                $langsTable = \wdmg\translations\models\Languages::tableName();
                $this->addForeignKey(
                    'fk_pages_to_langs',
                    '{{%pages}}',
                    'locale',
                    $langsTable,
                    'locale',
                    'NO ACTION',
                    'CASCADE'
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if (!is_null($this->getDb()->getSchema()->getTableSchema('{{%pages}}')->getColumn('source_id'))) {
            $this->dropIndex('{{%idx-pages-source}}', '{{%pages}}');
            $this->dropColumn('{{%pages}}', 'source_id');
            $this->dropForeignKey(
                'fk_pages_to_source',
                '{{%pages}}'
            );
        }
        if (!is_null($this->getDb()->getSchema()->getTableSchema('{{%pages}}')->getColumn('locale'))) {
            $this->dropIndex('{{%idx-pages-locale}}', '{{%pages}}');
            $this->dropColumn('{{%pages}}', 'locale');

            if (class_exists('\wdmg\translations\models\Languages')) {
                $langsTable = \wdmg\translations\models\Languages::tableName();
                if (!(Yii::$app->db->getTableSchema($langsTable, true) === null)) {
                    $this->dropForeignKey(
                        'fk_pages_to_langs',
                        '{{%pages}}'
                    );
                }
            }
        }
    }
}
