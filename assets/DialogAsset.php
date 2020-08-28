<?php


namespace vatandoost\filemanager\assets;

use yii\web\AssetBundle;

class DialogAsset extends AssetBundle
{
    public $sourcePath = "@vatandoost/filemanager/assets/dialog";

    public $css = [
        'css/jquery.fileupload.css',
        'css/jquery.fileupload-ui.css',
        'css/jplayer.blue.monday.min.css',
        'css/lightbox.min.css',
        'css/filemanager.css',
    ];
    public $js = [
        'js/jquery-ui.min.js',
        'js/tmpl.min.js',
        'js/load-image.all.min.js',
        'js/canvas-to-blob.min.js',
        'js/jquery.fileupload.js',
        'js/jquery.fileupload-process.js',
        'js/jquery.fileupload-image.js',
        'js/jquery.fileupload-audio.js',
        'js/jquery.fileupload-video.js',
        'js/jquery.fileupload-validate.js',
        'js/jquery.fileupload-ui.js',
        'js/lightbox.min.js',
        'js/jquery.jplayer.min.js',
        'js/bootbox.all.min.js',
        'js/filemanager.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
