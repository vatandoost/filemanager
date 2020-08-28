<?php


namespace vatandoost\filemanager\libs\handlers;


use FtpClient\FtpClient;
use FtpClient\FtpException;
use vatandoost\filemanager\libs\Utils;
use vatandoost\filemanager\models\File;
use vatandoost\filemanager\models\FileType;
use vatandoost\filemanager\Module;
use yii\base\BaseObject;
use yii\helpers\Url;
use yii\web\UploadedFile;
use yii;

class FtpHandler extends BaseObject implements HandlerInterface
{

    public function saveFile(UploadedFile $uploadedFile, File $file, FileType $fileType, array $validationInfo)
    {
        $module = Module::getInstance();
        $config = $module->params['ftp'];

        $tempPath = $this->getTempDir();
        $tempFileName = $tempPath . "/" . $file->file_name . '.' . $file->extension;
        //remove manually because of sometimes upload local file
        if (!$uploadedFile->saveAs($tempFileName, false)) {
            unlink($uploadedFile->tempName);
            Yii::error('error in save file');
            return false;
        }
        unlink($uploadedFile->tempName);

        if ($validationInfo['resize']) {
            Utils::resizeImage($tempFileName, $tempFileName, $validationInfo['resize']['width'], $validationInfo['resize']['height'], $validationInfo['resize']['quality']);
        }

        $has_thumb = (isset($validationInfo['thumb']) && $validationInfo['thumb']);
        if ($fileType->is_public || $has_thumb) {
            $W = $module->params['image_preview_size']['w'];
            $H = $module->params['image_preview_size']['h'];
            if ($has_thumb) {
                $W = $validationInfo['thumb'][0];
                $H = $validationInfo['thumb'][1];
            }
            $thumbPath = Utils::generateThumb($tempFileName, false, $W, $H);
            $has_thumb = !!$thumbPath;
        }

        $ftp = $this->getConnection();

        $dir = rtrim($config['ftp_' . ($fileType->is_public ? 'public' : 'private') . '_folder'], '/')
            . '/' . trim($fileType->files_path, '/');
        $thumbDir =
            rtrim($config['ftp_' . (($fileType->is_public || $fileType->has_public_thumb) ? 'public' : 'private') . '_folder'], '/')
            . '/' . trim($fileType->files_path, '/');

        if (!$ftp->isDir($dir)) {
            $ftp->mkdir($dir, true);
        }
        if ($dir != $thumbDir && !$ftp->isDir($thumbDir)) {
            $ftp->mkdir($dir, true);
        }

        $ftp->put($dir . '/' . basename($tempFileName), $tempFileName, FTP_BINARY);
        unlink($tempFileName);
        if ($has_thumb) {
            $ftp->put($thumbDir . '/' . basename($thumbPath), $thumbPath, FTP_BINARY);
            unlink($thumbPath);
        }
        return true;
    }


    public function getFileContent(File $file)
    {
        $tmp = $this->getLocalFilePath($file);
        $content = file_get_contents($tmp);
        unlink($tmp);
        return $content;
    }

    public function getLocalFilePath(File $file)
    {
        $module = Module::getInstance();
        $config = $module->params['ftp'];
        $ftp = $this->getConnection();
        $dir = rtrim($config['ftp_' . ($file->fileType->is_public ? 'public' : 'private') . '_folder'], '/') . '/' . trim($file->fileType->files_path, '/');
        $tmp = $this->getTempDir();
        $ftp->get($tmp, $dir . '/' . $file->file_name . '.' . $file->extension, FTP_BINARY);
        return $tmp;
    }

    public function deleteFile(File $file)
    {
        $module = Module::getInstance();
        $config = $module->params['ftp'];
        $ftp = $this->getConnection();
        $dir = rtrim($config['ftp_' . ($file->fileType->is_public ? 'public' : 'private') . '_folder'], '/') . '/' . trim($file->fileType->files_path, '/') . '/';
        @$ftp->remove($dir . $file->file_name . '.' . $file->extension);
        @$ftp->remove($dir . $file->file_name . '_thumb.' . $file->extension);
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

    public function getPublicBaseUrl($subDirectory = '')
    {
        $module = Module::getInstance();
        return rtrim($module->params['ftp']['ftp_base_url'], '/') . "/$subDirectory/";
    }

    private function getConnection()
    {
        $module = Module::getInstance();
        $config = $module->params['ftp'];
        if (isset($config['ftp_host']) && $config['ftp_host']) {
            Yii::info('connected to ftp');
            $ftp = new FtpClient();
            try {
                $ftp->connect($config['ftp_host'], $config['ftp_ssl'], $config['ftp_port']);
                $ftp->login($config['ftp_user'], $config['ftp_pass']);
                $ftp->pasv(true);
                return $ftp;
            } catch (FtpException $e) {
                Yii::error($e->getMessage(), 'ftp');
                return false;
            }
        } else {
            return false;
        }
    }

    private function getTempDir()
    {
        //@$tmp = tempnam('/tmp', 'fm');
        $dir = Yii::getAlias('@runtime/ftpfolder');
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        return $dir;
    }

}
