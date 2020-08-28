<?php


namespace vatandoost\filemanager\libs;


use vatandoost\filemanager\models\File;
use yii\db\ActiveRecord;

class BaseManager extends ActiveRecord
{
    public $relation_id;
    public $fileTypeName;
    public $files;

    use FileTrait;

    public static function tableName()
    {
        return File::tableName();
    }

    public function activeAttributes()
    {
        return ['files'];
    }

    public function scenarios()
    {
        $scens = parent::scenarios();
        $scens[self::SCENARIO_DEFAULT][] = 'files';
        return $scens;
    }


    public function rules()
    {
        return [
            [
                ['files'],
                FileValidator::className(),
                'file_type_name' => $this->fileTypeName,
                'relation_field' => 'relation_id',
                //'thumb' => [120, 100],
                'multiple' => true
            ],
        ];
    }

    public function filterQueryFiles($query, $fileType, $relation_id = null)
    {
        return $query;
    }

    public function canView($file)
    {
        return true;
    }


    public function canDelete($file)
    {
        return true;
    }

    public function beforeFileDelete($file)
    {
        return;
    }

    public function afterFileDelete($file)
    {
        return;
    }

    /*public function afterFileUpload($file)
    {
        return;
    }*/

    public function fileTypeConfig($fileType, $relationId = null)
    {
        return [
            'delete_files' => true,
            'upload_files' => true,
            'download_archive' => true,
            'rename_files' => true,
            'download_files' => true, // allow download files or just preview
        ];
    }
}
