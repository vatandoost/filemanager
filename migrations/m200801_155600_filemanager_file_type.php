<?php

use yii\db\Migration;

/**
 * Class m200801_155732_filemanager_file_type
 */
class m200801_155600_filemanager_file_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%filemanager_file_type}}', [
            'file_type_id' => $this->primaryKey()->notNull()->append(' AUTO_INCREMENT'),
            'name' => $this->string(255)->unique(),
            'title' => $this->string(255),
            'is_public' => $this->boolean()->notNull(),
            'mime_types' => $this->string(255),
            'max_size' => $this->integer(),
            'extensions' => $this->string(255),
            'files_path' => $this->string(255),
            'manager_class' => $this->string(500),
            'handler_type' => $this->tinyInteger(2)->defaultValue(1),
            'has_public_thumb' => $this->boolean()->notNull(),
            'has_force_relation_id' => $this->boolean()->notNull()
                ->comment("force to send a relation Id in Dialog to see and upload files "),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%filemanager_file_type}}');
    }
}
