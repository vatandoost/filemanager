<?php

namespace vatandoost\filemanager\libs;

use vatandoost\filemanager\Module;
use yii\helpers\Url;
use vatandoost\filemanager\models\File;
use vatandoost\filemanager\models\FileType;
use yii\web\UploadedFile;
use yii;

trait FileTrait
{
    /**
     * get all rules of FileValidator
     * @param null|string $attribute
     * @return FileValidator[]
     */
    public function getActiveFileValidators($attribute = null)
    {
        $validators = [];
        /* @var $this \yii\base\Model */
        foreach ($this->getActiveValidators($attribute) as $validator) {
            if ($validator instanceof FileValidator) {
                $validators[] = $validator;
            }
        }
        return $validators;
    }

    /**
     * return all of the file attributes in a model with detail indexed by attribute name
     * @return array
     */
    public function getActiveFileAttributes()
    {
        $fileAttributes = [];
        foreach ($this->getActiveFileValidators() as $fileValidator) {
            if (is_array($fileValidator->attributes)) {
                foreach ($fileValidator->attributes as $attribute) {
                    $fileAttributes[$attribute] = [
                        'file_type_name' => $fileValidator->file_type_name,
                        'target_field' => $fileValidator->target_field,
                        'relation_field' => $fileValidator->relation_field,
                        'thumb' => $fileValidator->thumb,
                        'multiple' => $fileValidator->multiple,
                        'resize' => $fileValidator->resize,
                    ];
                }
            } else {
                $fileAttributes[$fileValidator->attributes] = [
                    'file_type_name' => $fileValidator->file_type_name,
                    'target_field' => $fileValidator->target_field,
                    'relation_field' => $fileValidator->relation_field,
                    'thumb' => $fileValidator->thumb,
                    'multiple' => $fileValidator->multiple,
                    'resize' => $fileValidator->resize,
                ];
            }
        }

        return $fileAttributes;
    }

    /**
     * @param null $file_type_name
     * @return yii\db\Query
     */
    public static function filesQuery($file_type_name = null)
    {
        if (empty($file_type_name)) {
            $file_type_name = strtolower(strtr(self::className(), '\\', '_'));
        }
        /** @var FileType $fileType */
        $fileType = FileType::find()->andWhere(["name" => $file_type_name])->one();
        $handler = $fileType->getHandler();

        $tbf = File::tableName();
        $query = (new yii\db\Query())
            ->from($tbf)
            ->select(["$tbf.*", 'is_public' => new yii\db\Expression($fileType->is_public)])
            ->where([$tbf . '.file_type_id' => $fileType->file_type_id]);

        $publicUrl = rtrim($handler->getPublicBaseUrl($fileType->files_path), '/') . '/';
        $thumbUrl = Url::to(['file/get', 'thumb' => 1, 'id' => '']);
        $privateUrl = Url::to(['file/get', 'id' => '']);

        if (Yii::$app->db->driverName == 'mysql') {
            if ($fileType->is_public) {
                $query->addSelect([
                    'url' => (new yii\db\Expression('CONCAT("' . $publicUrl . '" , ' . $tbf . '.file_name, ".", ' . $tbf . '.extension)')),
                    'thumb' => (new yii\db\Expression('CONCAT("' . $publicUrl . '" , ' . $tbf . '.file_name, "_thumb.", ' . $tbf . '.extension)')),
                ]);
            } else {
                $query->addSelect([
                    'url' => (new yii\db\Expression('CONCAT("' . $privateUrl . '" , ' . $tbf . '.guid)')),
                ]);
                if ($fileType->has_public_thumb) {
                    $query->addSelect([
                        'thumb' => (new yii\db\Expression('CONCAT("' . $publicUrl . '" , ' . $tbf . '.file_name, "_thumb.", ' . $tbf . '.extension)')),
                    ]);
                } else {
                    $query->addSelect([
                        'thumb' => (new yii\db\Expression('CONCAT("' . $thumbUrl . '" , ' . $tbf . '.guid)')),
                    ]);
                }
            }
        } elseif (Yii::$app->db->driverName == 'pgsql') {
            if ($fileType->is_public) {
                $query->addSelect([
                    'url' => (new yii\db\Expression("('" . $publicUrl . '\' || ' . $tbf . '.file_name || \'.\' || ' . $tbf . '.extension)')),
                    'thumb' => (new yii\db\Expression("('" . $publicUrl . '\' || ' . $tbf . '.file_name || \'_thumb.\' || ' . $tbf . '.extension)')),
                ]);
            } else {
                $query->addSelect([
                    'url' => (new yii\db\Expression('(\'' . $privateUrl . '\' || ' . $tbf . '.guid)')),
                ]);
                if ($fileType->has_public_thumb) {
                    $query->addSelect([
                        'thumb' => (new yii\db\Expression("('" . $publicUrl . '\' || ' . $tbf . '.file_name || \'_thumb.\' || ' . $tbf . '.extension)')),
                    ]);
                } else {
                    $query->addSelect([
                        'thumb' => (new yii\db\Expression('(\'' . $thumbUrl . '\' || ' . $tbf . '.guid)')),
                    ]);
                }
            }
        }
        return $query;
    }

    public function validateFiles($attributeNames = null, $custom_loaded = false)
    {
        /* @var $this self|\yii\base\Model */
        $attributeInfos = $this->getActiveFileAttributes();
        /** @var array $fileAttributes name of attributes */
        $fileAttributes = array_keys($attributeInfos);

        if ($attributeNames !== null) {
            $fileAttributes = array_intersect($fileAttributes, (array)$attributeNames);
        }
        foreach ($fileAttributes as $attribute) {
            if (!$custom_loaded) {
                if ($attributeInfos[$attribute]['multiple']) {
                    $files = UploadedFile::getInstances($this, $attribute);
                } else {
                    $files = UploadedFile::getInstance($this, $attribute);
                }
                $this->$attribute = $files;
            }
            $this->validate($fileAttributes);
        }
        return !$this->hasErrors();
    }

    public function loadFromLocal($filePath, $attribute)
    {
        Utils::loadFileFromLocalPath('file', basename($filePath), $filePath);
        $this->$attribute = yii\web\UploadedFile::getInstanceByName('file');
        // should be save file with custom loaded flag
    }

    public function saveFiles($attributes = null, $saveModel = true, $custom_loaded = false)
    {
        /* @var $this self|\yii\base\Model */
        if (!$this->validateFiles($attributes, $custom_loaded)) {
            Yii::error($this->getFirstErrors());
            throw new yii\web\UnprocessableEntityHttpException(implode(',', $this->getFirstErrors()));
        }


        $savedFiles = [];
        $fileAttributes = $this->getActiveFileAttributes();
        foreach ($fileAttributes as $attribute => $info) {
            if (!empty($attributes) && !in_array($attribute, $attributes)) {
                continue;
            }

            $fileType = $this->getFileType($info['file_type_name']);
            $handler = $fileType->getHandler();

            // get uploaded files
            if (!$custom_loaded) {
                if ($info['multiple']) {
                    $files = UploadedFile::getInstances($this, $attribute);
                } else {
                    $files = [UploadedFile::getInstance($this, $attribute)];
                }
            } else {
                if ($info['multiple']) {
                    $files = $this->$attribute;
                } else {
                    $files = [$this->$attribute];
                }
            }
            /** @var UploadedFile[] $files */
            foreach ($files as $file) {
                if (empty($file) || $file->error != 0) {
                    continue;
                }
                $transaction = Yii::$app->db->beginTransaction();
                $fileModel = $this->createFileModel($file, $info, $fileType->file_type_id);
                if (!$fileModel) {
                    $transaction->rollBack();
                    throw new yii\web\ServerErrorHttpException(implode(',', $fileModel->getFirstErrors()));
                }

                $old_file_id = $this->updateFileTargetFieldAndGetOldValue($info, $fileModel->file_id, $saveModel);

                if (!$handler->saveFile($file, $fileModel, $fileType, $info)) {
                    $transaction->rollBack();
                    throw new yii\web\ServerErrorHttpException(Module::t('error in save file'));
                }
//remove if there is not multiple file
                if (!$info['multiple'] && !$this->isNewRecord) {
                    if (!empty($old_file_id)) {
                        FileHelper::deleteFile($old_file_id);
                    } elseif (!empty($fileModel->relation_id)) {
                        $forDeleteFiles = File::find()
                            ->where(['!=', 'file_id', $fileModel->file_id])
                            ->andWhere(['relation_id' => $fileModel->relation_id])
                            ->andWhere(['file_type_id' => $fileModel->file_type_id])
                            ->all();
                        foreach ($forDeleteFiles as $model) {
                            FileHelper::deleteFileByModel($model);
                        }
                    }
                }

                $savedFiles[] = $fileModel;
                $transaction->commit();
                continue;
            }
        }

        return $savedFiles;
    }

    private function getFileType($file_type_name)
    {
        $module = Module::getInstance();
        $typeName = $file_type_name;
        if (empty($typeName)) {
            $typeName = strtolower(strtr($this::className(), '\\', '_'));
        }
        $fileType = FileType::find()->andWhere(["name" => $typeName])->one();


        if (empty($fileType)) {
            $fileType = new FileType();
            $fileType->name = $typeName;
            $file_type_name_array = explode('_', $fileType->name);
            $fileType->title = array_pop($file_type_name_array); // get model class name
            $fileType->is_public = 1;
            $fileType->manager_class = $this::className();
            $fileType->handler_type = $module->params['default_handler'];
            $fileType->files_path = $fileType->title;
            if (!$fileType->save()) {
                Yii::error(implode(' - ', $fileType->getFirstErrors()));
                throw new yii\web\UnprocessableEntityHttpException(implode(',', $fileType->getFirstErrors()));
            }
        }
        return $fileType;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param array $validationInfo
     * @param $file_type_id
     * @return File|false
     */
    private function createFileModel($uploadedFile, $validationInfo, $file_type_id)
    {
        $fileModel = new File();
        $fileModel->loadDefaultValues();
        $fileModel->file_type_id = $file_type_id;
        $fileModel->extension = $uploadedFile->extension;
        $fileModel->guid = Utils::getGuid();
        $fileModel->mime_type = $uploadedFile->type;
        $fileModel->size = $uploadedFile->size;
        $fileModel->file_name = $fileModel->guid;
        $fileModel->original_name = basename($uploadedFile->name, ".$uploadedFile->extension");
        $fileModel->created_at = $fileModel->updated_at = time();
        $fileModel->uploaded_by = (string)@Yii::$app->user->id;
        $fileModel->status = File::STATUS_OK;
        if ($imageInfo = @getimagesize($uploadedFile->tempName)) {
            $fileModel->dimension = $imageInfo[0] . 'x' . $imageInfo[1];
        }
        $fileModel->file_path = "";
        $relation_field = $validationInfo['relation_field'];
        if (!empty($relation_field) && isset($this->$relation_field)) {
            $fileModel->relation_id = (string)$this->$relation_field;
        }
        if ($fileModel->save()) {
            return $fileModel;
        } else {
            unlink($uploadedFile->tempName);
            Yii::error(Module::t('error in save file'));
            Yii::error($fileModel->getFirstErrors());
            return false;
        }
    }

    private function updateFileTargetFieldAndGetOldValue($validationInfo, $fileId, $saveModel)
    {
        $target_field = $validationInfo['target_field'];
        $old_file_id = null;
        if (!empty($target_field) && $this->hasAttribute($target_field)) {
            $old_file_id = $this->$target_field;
            $this->$target_field = $fileId;
            if (!$this->isNewRecord && $saveModel) {
                $this->save(false);
            }
        }
        return $old_file_id;
    }

    public function getFileUrl($file_id, $thumb = false)
    {
        return (new FileHelper())->getFileUrl($file_id, $thumb);
    }

    public function getFiles($attribute, $onlyModels = false)
    {
        $attributes = $this->getActiveFileAttributes();
        $relationField = $attributes[$attribute]['relation_field'];
        $targetField = $attributes[$attribute]['target_field'];
        if (empty($relationField) && empty($targetField)) {
            return [];
        }
        $typeName = $attributes[$attribute]['file_type_name'];
        if (empty($typeName)) {
            $typeName = strtolower(strtr($this::className(), '\\', '_'));
        }
        /** @var FileType $fileType */
        $fileType = FileType::find()->andWhere(["name" => $typeName])->one();
        if (empty($fileType)) {
            return [];
        }
        $query = File::find()
            ->where(['file_type_id' => $fileType->file_type_id]);
        if (!empty($targetField)) {
            $query->andFilterWhere(['file_id' => $this->$targetField]);
        }
        if (!empty($relationField)) {
            $query->andFilterWhere(['relation_id' => $this->$relationField]);
        }
        $files = $query->all();
        if ($onlyModels) {
            return $files;
        }
        $infos = [];
        foreach ($files as $file) {
            $infos[] = FileHelper::getFileInfoByModel($file);
        }
        return $infos;
    }
}
