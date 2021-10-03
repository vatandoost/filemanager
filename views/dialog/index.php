<?php
/** @var \yii\web\View $this */

/** @var \yii\data\ActiveDataProvider $dataProvider */

/** @var bool $is_multiple */

use vatandoost\filemanager\Module;
use vatandoost\filemanager\assets\DialogAsset;
use vatandoost\filemanager\assets\FontsAwesomeAsset;
use \yii\bootstrap4\BootstrapAsset;
use vatandoost\filemanager\libs\DialogViewHelper;
use yii\helpers\Url;

BootstrapAsset::register($this);
FontsAwesomeAsset::register($this);
$asset = DialogAsset::register($this);
$assetUrl = $asset->baseUrl;
DialogViewHelper::$assetUrl = $assetUrl;
$module = Module::getInstance();
$config = $module->params;


$get = Yii::$app->request->getQueryParams();
if (isset($get['editor'])) {
    $editor = strip_tags($get['editor']);
} else {
    $editor = @$get['type'] == 0 ? null : 'tinymce';
}

$disable_selector = Yii::$app->request->get('selector', 'on') == 'off';
$is_multiple = $is_multiple && (!isset($get['multiple']) || $get['multiple'] === '1');

$iconFileHeight = $module->params['image_preview_size']['h'] - 20;
$style = <<<CSS
.image-box {
    background-image: url("$assetUrl/img/trans.jpg");
    width: {$module->params['image_preview_size']['w']}px;
    height: {$module->params['image_preview_size']['h']}px;
}

.image-box img {
    max-width: {$module->params['image_preview_size']['w']}px;
    max-height: {$module->params['image_preview_size']['h']}px;
}

.image-box.icon-file img {
    height: {$iconFileHeight}px;
}

.file-box .title-box {
    max-width: {$module->params['image_preview_size']['w']}px;
}

.file-box .file-tools {
    max-width: {$module->params['image_preview_size']['w']}px;
}
CSS;
$this->registerCss($style);
$unique_name = Yii::$app->request->getQueryParam('unique_name');
$callback = Yii::$app->request->getQueryParam('callback', 'filemanager_callback');
$CKEditorFuncNum = Yii::$app->request->getQueryParam('CKEditorFuncNum', '');
$field_id = Yii::$app->request->getQueryParam('field_id', '');

$renameTitle = Module::t('rename');
$cancelBtnTitle = Module::t('cancel');
$confirmBtnTitle = Module::t('confirm');
$confirmDeleteText = Module::t('confirm_del');
$okBtnTitle = Module::t('ok');
$serverErrorAlert = Module::t('server_error_alert');

$renameUrl = Url::to(['file/rename']);
$deleteUrl = Url::to(['file/delete']);

$js = <<<JS
const uniqueName = '$unique_name';
const callback = '$callback';
const field_id = '$field_id';
const renameTitle = '$renameTitle';
const renameUrl = '$renameUrl';
const deleteUrl = '$deleteUrl';
const serverErrorAlert = '$serverErrorAlert';
const confirmDeleteText = '$confirmDeleteText';
const CKEditorFuncNum = '$CKEditorFuncNum';
const editor = '$editor';

var locale = {
    OK: '$okBtnTitle',
    CANCEL: '$cancelBtnTitle',
    CONFIRM: '$confirmBtnTitle'
};
JS;
$this->registerJs($js, $this::POS_HEAD);
?>
<!-- CSS adjustments for browsers with JavaScript disabled -->
<noscript>
    <link rel="stylesheet" href="<?= $assetUrl ?>/css/jquery.fileupload-noscript.css">
</noscript>
<noscript>
    <link rel="stylesheet" href="<?= $assetUrl ?>/css/jquery.fileupload-ui-noscript.css">
</noscript>
<?= $this->render('_upload', [
    'module' => $module,
    'assetUrl' => $assetUrl,
    'is_multiple' => $is_multiple,
]) ?>
<div>
    <?= $this->render('_nav', [
        'module' => $module,
        'files' => $files,
    ]) ?>
    <div class="d-flex flex-wrap container-fluid">
        <?php
        /** @var \vatandoost\filemanager\models\File[] $models */

        foreach ($files as $file) :
            $fileIcon = DialogViewHelper::getPreviewIcon($file);
            $imageIconFileClass = DialogViewHelper::hasThumb($file) ? "" : "icon-file"
            ?>
            <div class="file-box"
                 data-id="<?= $file['file_id']; ?>"
                 data-url="<?= $file['url']; ?>"
                 data-name="<?= $file['original_name']; ?>"
            >
                <?php if (!$disable_selector): ?>
                    <?php if ($is_multiple): ?>
                        <div class="custom-control custom-checkbox selector">
                            <input type="checkbox" class="custom-control-input selection"
                                   name="selection[]"
                                   value="<?= $file['file_id']; ?>"
                                   id="select<?= $file['file_id'] ?>">
                            <label class="custom-control-label" for="select<?= $file['file_id'] ?>"></label>
                        </div>
                    <?php else: ?>
                        <button class="btn selector bg-white" onclick="select('<?= $file['file_id'] ?>','<?= $file['url'] ?>')">
                            <i class="fa fa-check-square-o text-success"></i>
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
                <div class="image-box <?= $imageIconFileClass ?>"
                     style="background-color: <?= DialogViewHelper::getColor($file) ?>">
                    <img src="<?= $fileIcon ?>" alt="test file">
                </div>
                <div class="title-box p-1">
                    <?= $file['original_name'] ?>
                </div>
                <div class="file-tools p-1">
                    <?php if (DialogViewHelper::isImage($file)) {
                        ?>
                        <a class="tip-right preview"
                           title="<?= Module::t('preview') ?>"
                           data-title="<?= $file['original_name'] ?>"
                           data-lightbox="<?= $file['url'] ?>"
                           href="<?= $file['url'] ?>">
                            <i class="fa fa-eye"></i>
                        </a>
                    <?php } elseif (DialogViewHelper::isPlayable($file)) { ?>
                        <a class="tip-right modalMedia <?php if (DialogViewHelper::isAudio($file)) {
                            echo "audio";
                        } else {
                            echo "video";
                        } ?>"
                           title="<?= Module::t('preview') ?>"
                           data-title="<?= $file['original_name'] ?>"
                           data-url="<?= Url::to(['dialog/preview']) ?>?action=media_preview&title=<?= $file['original_name'] ?>&file=<?= $file['file_id'] ?>"
                           href="javascript:void('');"><i class=" fa fa-eye"></i></a>
                    <?php } elseif (in_array($file['extension'], ['pdf', 'PDF'])) { ?>
                        <a class="tip-right file-preview-btn" title="<?= Module::t('preview') ?>"
                           data-title="<?= $file['original_name'] ?>"
                           data-url="<?= Url::to(['dialog/preview']) ?>?action=pdf_preview&title=<?= $file['original_name'] ?>&file=<?= $file['file_id'] ?>"
                           href="javascript:void('');"><i class=" fa fa-eye"></i></a>
                    <?php } elseif (in_array($file['extension'], $config['cad_exts'])) { ?>
                        <a class="tip-right file-preview-btn" title="<?= Module::t('preview') ?>"
                           data-title="<?= $file['original_name'] ?>"
                           data-url="<?= Url::to(['dialog/preview']) ?>?action=cad_preview&title=<?= $file['original_name'] ?>&file=<?= $file['file_id'] ?>"
                           href="javascript:void('');"><i class=" fa fa-eye"></i></a>
                    <?php } elseif ($config['googledoc_enabled'] && in_array($file['extension'], $config['googledoc_file_exts'])) { ?>
                        <a class="tip-right file-preview-btn" title="<?= Module::t('preview') ?>"
                           data-title="<?= $file['original_name'] ?>"
                           data-url="<?= Url::to(['dialog/preview']) ?>?action=get_file&sub_action=preview&preview_mode=google&title=<?= $file['original_name'] ?>&file=<?= $file['file_id'] ?>"
                           href="docs.google.com;"><i class=" fa fa-eye"></i></a>
                    <?php } elseif ($config['convert_pdf_preview'] && in_array($file['extension'], $config['googledoc_file_exts'])) { ?>
                        <a class="tip-right file-preview-btn" title="<?= Module::t('preview') ?>"
                           data-title="<?= $file['original_name'] ?>"
                           data-url="<?= Url::to(['dialog/preview']) ?>?action=convert_pdf_preview&title=<?= $file['name']; ?>&file=<?= $file['file_id'] ?>"
                           href="javascript:void('');"><i class=" fa fa-eye"></i></a>
                    <?php } else { ?>
                        <a class="preview disabled"><i class="fa fa-eye text-muted"></i></a>
                    <?php } ?>
                    <a href="javascript:void('')"
                       class="tip-left rename-file <?php if (!$config['rename_files']) echo "disabled"; ?>"
                       title="<?= Module::t('rename') ?>">
                        <i class="fa fa-pencil-square-o <?php if (!$config['rename_files']) echo 'text-muted'; ?>"></i>
                    </a>

                    <a href="javascript:void('')"
                       class="tip-left delete-btn <?php if (!$config['delete_files']) echo "disabled"; ?>"
                       title="<?= Module::t('erase') ?>">
                        <i class="fa fa-trash-o <?php if (!$config['delete_files']) echo 'text-muted'; ?>"></i>
                    </a>
                    <a href="<?= $file['url'] ?>" target="_blank" download>
                        <span class="fa fa-download"></span>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <br>
    <footer>
        <span>Powered By <?= Module::getInstance()->poweredBy ?></span> -
        <span class="small">
                file icons made by <a href="https://www.flaticon.com/authors/freepik" title="Freepik">Freepik</a>
                from <a href="https://www.flaticon.com/" title="Flaticon">www.flaticon.com</a>
            </span>
    </footer>
</div>
<!-- player div start -->
<div class="modal hide" id="previewMedia">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title media-title"><?= Module::t('preview'); ?></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row-fluid body-preview">
                </div>
            </div>
        </div>
    </div>
</div>

