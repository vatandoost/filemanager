<?php


namespace vatandoost\filemanager\libs;


use vatandoost\filemanager\models\FileType;

class FileValidator extends \yii\validators\FileValidator
{
    public $target_field = null;
    public $relation_field = null;

    public $file_type_name = null;

    /** @var bool|array [200,200] */
    public $thumb = false;

    public $multiple = false;
    public $resize = false;

    public function init()
    {
        parent::init();
        if ($this->multiple && $this->maxFiles == 1) {
            $this->maxFiles = 10;
        }
    }

    public function validateAttribute($model, $attribute)
    {
        if (empty($this->file_type_name)) {
            $this->file_type_name = strtolower(strtr($model::className(), '\\', '_'));
        }
        /** @var FileType $fileType */
        $fileType = FileType::find()->andWhere(["name" => $this->file_type_name])->one();
        if (!empty($fileType)) {
            if (empty($this->maxSize) && !empty($fileType->maxSize)) {
                $this->maxSize = $fileType->max_size;
            }
            if (empty($this->extensions) && !empty($fileType->extensions)) {
                $this->extensions = explode(',', $fileType->extensions);
            }
            if (empty($this->mimeTypes) && !empty($fileType->mime_types)) {
                $this->mimeTypes = explode(',', $fileType->mime_types);
            }
        }
        parent::validateAttribute($model, $attribute);
    }


}
