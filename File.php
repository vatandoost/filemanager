<?php

namespace vatandoost\filemanager\models;

use Yii;

/**
 * This is the model class for table "file".
 *
 * @property int $file_id
 * @property string|null $guid
 * @property int|null $file_type_id
 * @property int|null $size
 * @property string|null $extension
 * @property string|null $mime_type
 * @property string|null $file_path
 * @property string|null $file_name
 * @property string|null $original_name
 * @property int|null $relation_id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $status
 * @property int|null $uploaded_by
 * @property string|null $dimension
 *
 * @property FileType $fileType
 */
class File extends \yii\db\ActiveRecord
{
    const STATUS_OK = 1;
    const STATUS_DELETED = 0;
	public $thumb;
	public $id;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%filemanager_file}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file_type_id', 'size', 'created_at', 'updated_at', 'status'], 'integer'],
            [['guid', 'extension', 'mime_type', 'file_path', 'file_name', 'original_name', 'dimension'], 'string', 'max' => 255],
            [['relation_id', 'uploaded_by'], 'string', 'max' => 50],
            [['file_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => FileType::className(), 'targetAttribute' => ['file_type_id' => 'file_type_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'file_id' => 'File ID',
            'guid' => 'Guid',
            'file_type_id' => 'File Type ID',
            'size' => 'Size',
            'extension' => 'Extension',
            'mime_type' => 'Mime Type',
            'file_path' => 'File Path',
            'file_name' => 'File Name',
            'original_name' => 'Original Name',
            'relation_id' => 'Relation ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'status' => 'Status',
            'uploaded_by' => 'Uploaded By',
            'dimension' => 'Dimension',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFileType()
    {
        return $this->hasOne(FileType::className(), ['file_type_id' => 'file_type_id']);
    }


}
