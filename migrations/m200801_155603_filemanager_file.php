<?php

use yii\db\Migration;


/**
 * migration for table filemanager_file
 * @author masoudvatandoost1@gmail.com
 */
class m200801_155603_filemanager_file extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%filemanager_file}}', [
            'file_id' => $this->primaryKey()->notNull()->append(' AUTO_INCREMENT'),
            'file_type_id' => $this->integer()->notNull(),
            'guid' => $this->string(255)->unique()->notNull(),
            'size' => $this->integer(),
            'extension' => $this->string(255),
            'mime_type' => $this->string(255),
            'file_path' => $this->string(255),
            'file_name' => $this->string(255),
            'original_name' => $this->string(255),
            'relation_id' => $this->string(50),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'status' => $this->boolean(),
            'uploaded_by' => $this->string(50),
            'dimension' => $this->string(255),
        ]);
        $this->addForeignKey(
            'fk-file_type_id',
            '{{%filemanager_file}}',
            'file_type_id',
            '{{%filemanager_file_type}}',
            'file_type_id',
            'NO ACTION',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-file_type_id', '{{%filemanager_file}}');
        $this->dropTable('{{%filemanager_file}}');
    }
}
