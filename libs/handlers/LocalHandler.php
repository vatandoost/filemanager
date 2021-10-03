<?php


namespace vatandoost\filemanager\libs\handlers;


use vatandoost\filemanager\libs\Utils;
use vatandoost\filemanager\models\File;
use vatandoost\filemanager\models\FileType;
use vatandoost\filemanager\Module;
use Yii;
use yii\base\BaseObject;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\UploadedFile;

class LocalHandler extends BaseObject implements HandlerInterface
{
    public function getBasePath(FileType $fileType)
    {
        if ($fileType->is_public) {
            $basePath = '@publicFiles';
        } else {
            $basePath = '@privateFiles';
        }
        $basePath = FileHelper::normalizePath(\Yii::getAlias("$basePath/" . $fileType->files_path));
        if (!is_dir($basePath)) {
            FileHelper::createDirectory($basePath);
        }
        return $basePath;
    }

    public function getPublicBasePath($subDirectory = '')
    {
        $basePath = '@publicFiles';
        $basePath = FileHelper::normalizePath(\Yii::getAlias("$basePath/$subDirectory"));
        if (!is_dir($basePath)) {
            FileHelper::createDirectory($basePath);
        }
        return $basePath;
    }

    public function getPublicBaseUrl($subDirectory = '')
    {
        $module = Module::getInstance();
        if (!empty($module->host)) {
            return rtrim($module->host, '/') . "$module->publicFilesUrl/$subDirectory/";
        }
        return Url::to(["$module->publicFilesUrl/$subDirectory"], true) . '/';
    }

    public function saveFile(UploadedFile $uploadedFile, File $file, FileType $fileType, array $validationInfo)
    {
        $module = Module::getInstance();
        $basePath = $this->getBasePath($fileType);
        $fileName = $basePath . "/" . $file->file_name . '.' . $file->extension;
        //remove manually because of sometimes upload local file
        if (!$uploadedFile->saveAs($fileName, false)) {
            unlink($uploadedFile->tempName);
            Yii::error('error in save file');
            return false;
        }
        unlink($uploadedFile->tempName);

        if ($validationInfo['resize']) {
            Utils::resizeImage($fileName, $fileName, $validationInfo['resize']['width'], $validationInfo['resize']['height'], $validationInfo['resize']['quality']);
        }

        $has_thumb = (isset($validationInfo['thumb']) && $validationInfo['thumb']);
        if ($fileType->is_public || $has_thumb) {
            $W = $module->params['image_preview_size']['w'];
            $H = $module->params['image_preview_size']['h'];
            if ($has_thumb) {
                $W = $validationInfo['thumb'][0];
                $H = $validationInfo['thumb'][1];
            }
            $thumbDirectory = false;
            if ($fileType->has_public_thumb) {
                $thumbDirectory = $this->getPublicBasePath($fileType->files_path);
            }
            $thumbPath = Utils::generateThumb($fileName, $thumbDirectory, $W, $H);
        }

        return true;
    }

    public function getFileUrl(File $file, $thumb = false)
    {
        $base = $this->getPublicBaseUrl($file->fileType->files_path);
        if ($file->fileType->is_public) {
            $fileUrl = $base . $file->file_path . $file->file_name . ($thumb ? '_thumb' : '') . '.' . $file->extension;
        } else {
            if ($thumb && $file->fileType->has_public_thumb) {
                $fileUrl = $base . $file->file_path . $file->file_name . '_thumb.' . $file->extension;
            } else {
                $fileUrl = Url::to(['file/get', 'id' => $file->guid, 'thumb' => $thumb], true);
            }
        }
        return $fileUrl;
    }

    public function getFileContent(File $file)
    {
        $basePath = $this->getBasePath($file->fileType);
        $filePath = $basePath . DIRECTORY_SEPARATOR . $file->file_name . '.' . $file->extension;
        return file_get_contents($filePath);
    }

    public function getLocalFilePath(File $file)
    {
        $basePath = $this->getBasePath($file->fileType);
        $filePath = $basePath . DIRECTORY_SEPARATOR . $file->file_name . '.' . $file->extension;
        return $filePath;
    }


    public function deleteFile(File $file)
    {
        $basePath = $this->getBasePath($file->fileType);
        $filePath = $basePath . DIRECTORY_SEPARATOR . $file->file_name . '.' . $file->extension;
        $thumbPath = $basePath . DIRECTORY_SEPARATOR . $file->file_name . '_thumb.' . $file->extension;
        @unlink($filePath);
        @unlink($thumbPath);
        return true;
    }
}
