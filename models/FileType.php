<?php

namespace vatandoost\filemanager\models;

use vatandoost\filemanager\libs\BaseManager;
use vatandoost\filemanager\libs\handlers\FtpHandler;
use vatandoost\filemanager\libs\handlers\HandlerInterface;
use vatandoost\filemanager\libs\handlers\LocalHandler;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "file_type".
 *
 * @property int $file_type_id
 * @property string|null $name
 * @property string|null $title
 * @property int|null $is_public
 * @property string|null $mime_types
 * @property int|null $max_size
 * @property string|null $extensions
 * @property string|null $files_path
 * @property string|null manager_class
 * @property integer $handler_type
 * @property integer $has_public_thumb
 * @property integer $has_force_relation_id
 *
 * @property File[] $files
 */
class FileType extends \yii\db\ActiveRecord
{
    const HANDLER_TYPE_LOCAL = 1;
    const HANDLER_TYPE_FTP = 2;

    /**
     * @return HandlerInterface
     */
    public function getHandler()
    {
        switch ($this->handler_type) {
            case self::HANDLER_TYPE_FTP:
                return new FtpHandler();
            case self::HANDLER_TYPE_LOCAL:
            default:
                return new LocalHandler();
        }
    }

    /**
     * @return BaseManager|ActiveRecord
     * @throws \yii\base\InvalidConfigException
     */
    public function getManager()
    {
        if (!empty($this->manager_class)) {
            $manager = Yii::createObject($this->manager_class);
        } else {
            $manager = new BaseManager();
        }
        return $manager;
    }


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%filemanager_file_type}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['max_size', 'handler_type',], 'integer'],
            [['is_public', 'has_public_thumb', 'has_force_relation_id'], 'boolean'],
            [['name', 'title', 'mime_types', 'extensions', 'files_path'], 'string', 'max' => 255],
            [['manager_class'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'file_type_id' => 'File Type ID',
            'name' => 'Name',
            'title' => 'Title',
            'is_public' => 'Is Public',
            'mime_types' => 'Mime Types',
            'max_size' => 'Max Size',
            'extensions' => 'Extensions',
            'files_path' => 'Files Path',
            'manager_class' => 'manager class',
            'handler_type' => 'type of handler class',
            'has_public_thumb' => 'public thumbnail',
            'has_force_relation_id' => 'force to has relation id to work with filemanager',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(File::className(), ['file_type_id' => 'file_type_id']);
    }
}
