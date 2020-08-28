<?php


namespace vatandoost\filemanager\libs\handlers;


use vatandoost\filemanager\models\File;
use vatandoost\filemanager\models\FileType;
use yii\web\UploadedFile;

interface HandlerInterface
{
    public function getPublicBaseUrl($subDirectory = '');

    public function getFileUrl(File $file, $thumb = false);

    public function saveFile(UploadedFile $uploadedFile, File $file, FileType $fileType, array $validationInfo);

    public function getFileContent(File $file);

    public function getLocalFilePath(File $file);

    public function deleteFile(File $file);
}
