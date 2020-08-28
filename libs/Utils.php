<?php

namespace vatandoost\filemanager\libs;


use Exception;
use vatandoost\filemanager\Module;
use Yii;
use yii\base\BaseObject;

class Utils extends BaseObject
{
    /**
     * add file detail to PHP $_FILES global variable
     * @param $postFieldName
     * @param $fileName
     * @param $filePath
     */
    public static function loadFileFromLocalPath($postFieldName, $fileName, $filePath)
    {
        $_FILES[$postFieldName] = array(
            'name' => array($fileName),
            'tmp_name' => array($filePath),
            'size' => array(filesize($filePath)),
            'type' => [@mime_content_type($filePath) ?: null],
            'error' => [0]
        );
    }

    public static function getGuid()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535));
    }

    /**
     * @param $src
     * @param bool|false $dirName
     * @param bool|false $width
     * @param bool|false $height
     * @return bool
     */
    public static function generateThumb($src, $dirName = false, $width = false, $height = false)
    {
        if (!$height && !$width)
            return false;
        $fileInfo = pathinfo($src);
        $ext = strtolower($fileInfo['extension']);
        if (empty($dirName)) {
            $dirName = $fileInfo['dirname'];
        }
        $dest = $dirName . DIRECTORY_SEPARATOR . $fileInfo['filename'] . "_thumb.$ext";

        if (!in_array($ext, ['gif', 'jpg', 'png', 'jpeg'])) {
            return false;
        }
        if (!self::resizeImage($src, $dest, $width, $height, 75)) {
            return false;
        }
        return $dest;
    }

    public static function resizeImage($SrcImage, $DestImage, $MaxWidth, $MaxHeight, $Quality)
    {
        try {
            list($iWidth, $iHeight, $type) = getimagesize($SrcImage);
            $ImageScale = min($MaxWidth / $iWidth, $MaxHeight / $iHeight);
            $NewWidth = ceil($ImageScale * $iWidth);
            $NewHeight = ceil($ImageScale * $iHeight);
            $NewCanves = imagecreatetruecolor($NewWidth, $NewHeight);
            $mimeType = strtolower(image_type_to_mime_type($type));
            switch ($mimeType) {
                case 'image/jpeg':
                    $NewImage = imagecreatefromjpeg($SrcImage);
                    break;
                case 'image/png':
                    $NewImage = imagecreatefrompng($SrcImage);
                    imagealphablending($NewCanves, false);
                    imagesavealpha($NewCanves, true);
                    break;
                case 'image/gif':
                    $NewImage = imagecreatefromgif($SrcImage);
                    break;
                default:
                    return false;
            }

            // Resize Image
            if (imagecopyresampled($NewCanves, $NewImage, 0, 0, 0, 0, $NewWidth, $NewHeight, $iWidth, $iHeight)) {
                // copy file
                switch ($mimeType) {
                    case 'image/jpeg':
                        imagejpeg($NewCanves, $DestImage, $Quality);
                        break;
                    case 'image/png':
                        imagepng($NewCanves, $DestImage, 0);
                        break;
                    case 'image/gif':
                        imagegif($NewCanves, $DestImage, $Quality);
                        break;
                }
                imagedestroy($NewCanves);
            }
        } catch (Exception $e) {
            Yii::error($e->getMessage());
            return false;
        }
        return true;
    }


    public static function setHeaders($filePath)
    {
        $type = mime_content_type($filePath);
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $type);
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
    }

    public static function readFile($filePath)
    {
        self::setHeaders($filePath);
        readfile($filePath);
        exit;
    }

    public static function convertSize($size)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $u = 0;
        while ((round($size / 1024) > 0) && ($u < 4)) {
            $size = $size / 1024;
            $u++;
        }

        return (number_format($size, 0) . " " . Module::t($units[$u]));
    }
}
