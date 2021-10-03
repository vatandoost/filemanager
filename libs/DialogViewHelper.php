<?php


namespace vatandoost\filemanager\libs;

use vatandoost\filemanager\Module;
use yii;

class DialogViewHelper
{
    public static $assetUrl = "";

    public static function getPreviewIcon($file)
    {
        if (self::hasThumb($file)) {
            return $file['thumb'];
        }
        $fileIcon = self::$assetUrl . "/img/extensions/blank.png";
        $extensionsPath = Yii::getAlias('@vatandoost/filemanager/assets/dialog/img/extensions');
        $extPath = $extensionsPath . DIRECTORY_SEPARATOR . $file['extension'] . '.png';
        if (file_exists($extPath)) {
            $fileIcon = self::$assetUrl . "/img/extensions/$file[extension].png";
        }
        return $fileIcon;
    }

    public static function hasThumb($file)
    {
        if (!self::isImage($file))
            return false;
        if (!$file['is_public'] && !$file['has_public_thumb']) {
            return false;
        }
        return true;
    }

    public static function isImage($file)
    {
        $module = Module::getInstance();
        return in_array(strtolower($file['extension']), $module->params['image_extensions']);
    }

    public static function isVideo($file)
    {
        $module = Module::getInstance();
        return in_array(strtolower($file['extension']), $module->params['video_extensions']);
    }

    public static function isAudio($file)
    {
        $module = Module::getInstance();
        return in_array(strtolower($file['extension']), $module->params['audio_extensions']);
    }

    public static function isPdf($file)
    {
        return strtolower($file['extension']) === 'pdf';
    }

    public static function isOfficeDoc($file)
    {
        $module = Module::getInstance();
        return in_array(strtolower($file['extension']), $module->params['googledoc_file_exts']);
    }

    public static function isPlayable($file)
    {
        $module = Module::getInstance();
        return in_array(strtolower($file['extension']), $module->params['jplayer_exts']);
    }

    public static function getColor($file)
    {
        if (self::isVideo($file)) {
            return 'orange';
        } elseif (self::isAudio($file)) {
            return 'purple';
        } elseif (self::isImage($file)) {
            return 'LightSlateGrey';
        } elseif (self::isPdf($file)) {
            return 'GreenYellow';
        } elseif (self::isOfficeDoc($file)) {
            return 'LightSalmon';
        } else {
            return 'skyblue';
        }
    }

    public static function getUrl($exceptParams = [])
    {
        $get = Yii::$app->request->getQueryParams();
        $url = [
            'dialog/index',
            'unique_name' => $get['unique_name'],
        ];
        if (!empty($get['search']) && !in_array('search', $exceptParams)) {
            $url['search'] = $get['search'];
        }
        if (!empty($get['sort']) && !in_array('sort', $exceptParams)) {
            $url['sort'] = $get['sort'];
        }
        if (!empty($get['callback']) && !in_array('callback', $exceptParams)) {
            $url['callback'] = $get['callback'];
        }
        if (!empty($get['field_id']) && !in_array('field_id', $exceptParams)) {
            $url['field_id'] = $get['field_id'];
        }
        if (!empty($get['multiple']) && !in_array('multiple', $exceptParams)) {
            $url['multiple'] = $get['multiple'];
        }
        if (!empty($get['selector']) && !in_array('selector', $exceptParams)) {
            $url['selector'] = $get['selector'];
        }
        return yii\helpers\Url::to($url);
    }
}
