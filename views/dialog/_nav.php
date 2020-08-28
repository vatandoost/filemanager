<?php

use vatandoost\filemanager\Module;
use \vatandoost\filemanager\libs\DialogViewHelper;


$currentSort = Yii::$app->request->getQueryParam('sort');
?>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#"><?= (Module::getInstance())->title ?></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
            </ul>
            <div class="btn-group  my-2 my-sm-0 mr-2">
                <button class="btn btn-outline-info dropdown-toggle" type="button" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-sort-amount-asc"></i>
                    <?= Module::t('sort_by') ?>
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="javascript:sortBy('original_name')">
                        <i class="fa fa-sort-alpha-<?= $currentSort == 'original_name' ? 'asc' : ($currentSort == '-original_name' ? 'desc' : '') ?>"></i>
                        <?= Module::t('name') ?>
                    </a>
                    <a class="dropdown-item" href="javascript:sortBy('extension')">
                        <i class="fa fa-sort-alpha-<?= $currentSort == 'extension' ? 'asc' : ($currentSort == '-extension' ? 'desc' : '') ?>"></i>
                        <?= Module::t('extension') ?>
                    </a>
                    <a class="dropdown-item" href="javascript:sortBy('size')">
                        <i class="fa fa-sort-amount-<?= $currentSort == 'size' ? 'asc' : ($currentSort == '-size' ? 'desc' : '') ?>"></i>
                        <?= Module::t('size') ?>
                    </a>
                    <a class="dropdown-item" href="javascript:sortBy('created_at')">
                        <i class="fa fa-sort-alpha-<?= $currentSort == 'created_at' ? 'asc' : ($currentSort == '-created_at' ? 'desc' : '') ?>"></i>
                        <?= Module::t('created_at') ?>
                    </a>
                </div>
            </div>
            <form class="form-inline mr-sm-2 my-2" onsubmit="searchFiles();return false;"
                  action="" method="get">
                <div class="input-group ">
                    <input type="text" name="search" class="form-control" id="navbarSearchInput"
                           value="<?= \Yii::$app->request->getQueryParam('search', '') ?>"
                           placeholder="<?= Module::t('search') ?>" autocomplete="off"
                           aria-label="<?= Module::t('search') ?>" aria-describedby="button-addon2">
                    <div class="input-group-append">
                        <button class="btn btn-outline-success  my-sm-0" id="navbarSearchBtn"
                                onclick="searchFiles();"
                                type="button"><?= Module::t('search') ?></button>
                    </div>
                </div>
            </form>
            <button class="btn btn-outline-primary my-2 my-sm-0" type="submit" onclick="$('.uploader').fadeIn();">
                <?= Module::t('upload') ?>
            </button>
        </div>
    </nav>
    <nav class="navbar navbar-expand-sm navbar-light bg-light border-top ">
        <?= $this->title ?> /
        <?= count($files) . ' ' . Module::t('file(s)') ?> /
        <?= Module::t('total size') ?>
        <?= \vatandoost\filemanager\libs\Utils::convertSize(array_sum(array_column($files, 'size'))) ?>

        <div class=" ml-auto mx-1">
            <a class="btn btn-outline-dark" href="<?= DialogViewHelper::getUrl() ?>">
                <i class="fa fa-refresh"></i>
            </a>
        </div>
        <div class=" selection-box " style="display: none">
            <!--<button class="btn btn-outline-danger my-sm-0" data-original-title="<?php /*= Module::t('delete selected') */ ?>">
                <i class="fa fa-trash-o"></i>
            </button>-->
            <button class="btn btn-outline-warning my-sm-0 multiple-deselect-btn"
                    data-original-title="<?= Module::t('deselect all') ?>">
                <i class="fa fa-minus-square-o"></i>
            </button>
            <button class="btn btn-outline-success my-sm-0 multiple-select-btn"
                    data-original-title="<?= Module::t('select all') ?>">
                <i class="fa fa-check-square-o"></i>
            </button>
            <button class="btn btn-outline-primary my-sm-0" onclick="select()">
                <i class="fa fa-check"></i>
                <?= Module::t('select') ?>
            </button>
        </div>
    </nav>
<?php
$searchUrl = DialogViewHelper::getUrl(['search']);
$sortUrl = DialogViewHelper::getUrl(['sort']);

$js = <<<JS
function searchFiles(){
  document.location.href= '$searchUrl&search=' + encodeURI($('#navbarSearchInput').val());
}
function sortBy(column){
  if(column == '$currentSort'){
    column = '-'+column;
  }
  document.location.href= '$sortUrl&sort=' + column;
}

JS;
$this->registerJs($js, \yii\web\View::POS_HEAD);
