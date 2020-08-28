<?php

namespace vatandoost\filemanager;

use vatandoost\filemanager\models\FileType;
use yii;

/**
 * permissions module definition class
 */
class Module extends \yii\base\Module implements yii\base\BootstrapInterface
{
    public $title = 'Yii2 filemanager';
    public $params = [
        'upload_files' => true,
        'url_upload' => true,
        'default_handler' => FileType::HANDLER_TYPE_LOCAL,

        'jplayer_exts' => array("mp4", "flv", "webmv", "webma", "webm", "m4a", "m4v", "ogv", "oga", "mp3", "midi", "mid", "ogg", "wav"),
        'cad_exts' => array('dwg', 'dxf', 'hpgl', 'plt', 'spl', 'step', 'stp', 'iges', 'igs', 'sat', 'cgm', 'svg'),

        // Preview with Google Documents
        'googledoc_enabled' => true,
        'googledoc_file_exts' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'odt', 'odp', 'ods'),


        'image_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'ico'],
        'file_extensions' => ['doc', 'docx', 'rtf', 'pdf', 'xls', 'xlsx', 'txt', 'csv', 'html', 'xhtml', 'psd', 'sql', 'log', 'fla', 'xml', 'ade', 'adp', 'mdb', 'accdb', 'ppt', 'pptx', 'odt', 'ots', 'ott', 'odb', 'odg', 'otp', 'otg', 'odf', 'ods', 'odp', 'css', 'ai', 'kmz', 'dwg', 'dxf', 'hpgl', 'plt', 'spl', 'step', 'stp', 'iges', 'igs', 'sat', 'cgm', 'tiff', ''], //Files
        'video_extensions' => ['mov', 'mpeg', 'm4v', 'mp4', 'avi', 'mpg', 'wma', "flv", "webm"],
        'audio_extensions' => ['mp3', 'mpga', 'm4a', 'ac3', 'aiff', 'mid', 'ogg', 'wav'],
        'misc_extensions' => ['zip', 'rar', 'gz', 'tar', 'iso', 'dmg'],

        //size of image boxes in filemanager ui
        'image_preview_size' => [
            'w' => 120,
            'h' => 100,
        ],
        'rename_files' => true,
        'delete_files' => true,
        // ftp handler options
        'ftp' => [
            'ftp_host' => "example.com", //put the FTP host
            'ftp_user' => "example",
            'ftp_pass' => "*****",
            'ftp_public_folder' => "/public_html/filemanager",
            'ftp_private_folder' => "/filemanager/uploads",
            'ftp_base_url' => "https://example.com/filemanager",
            'ftp_ssl' => false,
            'ftp_port' => 21,
        ],

        // convert office files to pdf format
        'convert_pdf_preview' => false,
        'unoconvBaseUrl' => 'localhost',
    ];
    public $poweredBy = '<a href="http://vatandoost.com" target="_blank">vatandoost.com</a>';
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'vatandoost\filemanager\controllers';

    public $superAdminRole = 'filemanager_admin';
    public $privateFiles;
    public $publicFiles;
    public $publicFilesUrl = '/uploads';


    public function bootstrap($app)
    {
        Yii::$app->urlManager->addRules([
            [
                'pattern' => $this->id . '/file/get/<id:[^.]+>',
                'route' => $this->id . '/file/get',
            ],
        ]);

        Yii::setAlias('@publicFiles', $this->publicFiles);
        Yii::setAlias('@privateFiles', $this->privateFiles);
        Yii::setAlias('@vatandoost', dirname(__DIR__));

    }

    public function init()
    {
        parent::init();
        if (empty($this->publicFiles) || empty($this->privateFiles)) {
            throw new \yii\web\ServerErrorHttpException("Bad Configuration - public files and private files must be set!");
        }

        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        Yii::$app->i18n->translations['vatandoost.filemanager'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en',
            'basePath' => '@vatandoost/filemanager/lang',
        ];
    }

    public static function t($message, $params = [], $language = null)
    {
        return Yii::t('vatandoost.filemanager', $message, $params, $language);
    }

    public static function isAdmin()
    {
        $module = self::getInstance();
        return Yii::$app->user->can($module->superAdminRole);
    }

}
