<?php

namespace vatandoost\filemanager\libs;


use vatandoost\filemanager\models\File;
use vatandoost\filemanager\Module;
use Yii;
use yii\base\Component;
use yii\web\ForbiddenHttpException;

class FileHelper extends Component
{
    public static function getUnavailableUrl()
    {
        // TODO
        return '';
    }

    public static function getFileUrl($file_id, $thumb = false)
    {
        if (empty($file_id)) {
            return self::getUnavailableUrl();
        }
        $file = File::findOne($file_id);
        if (empty($file)) {
            return self::getUnavailableUrl();
        }
        $handler = $file->fileType->getHandler();
        return $handler->getFileUrl($file);

    }

    public static function getFileInfo($file_id)
    {
        if (empty($file_id) || ($file = File::findOne($file_id)) == null) {
            return [];
        }
        return self::getFileInfoByModel($file);
    }

    public static function getFileInfoByModel(File $file)
    {
        if (empty($file)) {
            return [];
        }
        $handler = $file->fileType->getManager();
        $fileUrl = $handler->getFileUrl($file);
        $thumbUrl = $handler->getFileUrl($file, true);

        $dimension = $file->dimension;
        if (!empty($dimension)) {
            $dimension = explode('x', $dimension);
        }
        return [
            'file_id' => $file->file_id,
            'guid' => $file->guid,
            'name' => $file->original_name,
            'size' => $file->size,
            'dimension' => $dimension,
            'mime_type' => $file->mime_type,
            'extension' => $file->extension,
            'url' => $fileUrl,
            'thumbnail' => $thumbUrl,
        ];
    }


    public static function deleteFile($file_id, $checkPermissions = true)
    {
        $model = File::findOne($file_id);
        if (!empty($model)) {
            self::deleteFileByModel($model, $checkPermissions);
        }
    }

    public static function renameFile($file_id, $new_name)
    {
        $model = File::findOne($file_id);
        if (empty($model)) {
            return false;
        }
        $module = Module::getInstance();
        $config = $module->params;

        /** @var BaseManager $class */
        $class = $model->fileType->getManager();
        if (method_exists($class, 'fileTypeConfig')) {
            $config = array_merge($config, $class->fileTypeConfig($model->fileType));
        }
        if (!$config['rename_files']) {
            throw new ForbiddenHttpException("you can not rename this file");
        }
        $model->original_name = $new_name;
        return $model->save();
    }

    public static function deleteFileByModel(File $model, $checkPermissions = true)
    {
        $class = $model->fileType->getManager();
        if ($checkPermissions) {
            if (method_exists($class, 'canDelete')) {
                if (!$class->canDelete($model)) {
                    throw new ForbiddenHttpException("You can not delete this file");
                }
            }
        }
        if (method_exists($class, 'beforeFileDelete')) {
            $class->beforeFileDelete($model);
        }
        try {
            $model->delete();
        } catch (\Exception $e) {
            return false;
        }

        $handler = $model->fileType->getHandler();
        $handler->deleteFile($model);
        if (method_exists($class, 'afterFileDelete')) {
            $class->afterFileDelete($model);
        }
        return true;
    }

    public static function getFileContent($id = null, $model = null)
    {
        if (empty($model)) {
            $model = File::findOne($id);
            if (empty($model)) {
                return '';
            }
        }
        $handler = $model->fileType->getHandler();
        return $handler->getFileContent($model);
    }

    /**
     * @param File $file
     * @param null|FileType $fileType
     */
    public static function getLocalFilePath($file, $fileType = null)
    {
        if (empty($fileType)) {
            $fileType = $file->fileType;
        }
        $handler = $fileType->getHandler();
        return $handler->getLocalFilePath($file);
    }
}
