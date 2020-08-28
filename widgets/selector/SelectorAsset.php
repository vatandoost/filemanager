<?php

namespace vatandoost\filemanager\widgets\selector;

use yii\web\AssetBundle;

class SelectorAsset extends AssetBundle {

	public $sourcePath = '@vatandoost/filemanager/widgets/selector/assets';

	public $js = [
		'fancybox.min.js',
		'selector.js',
	];
	public $css = [
		'fancybox.css',
	];
	public $depends = [
		'yii\web\JqueryAsset',
	];

}
