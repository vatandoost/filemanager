<?php
/** @var \yii\web\View $this */

/** @var bool $is_multiple */

use vatandoost\filemanager\Module;
use yii\bootstrap4\Tabs;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

$style = <<<CSS
img.file_upload_icon{
    width: 50px;
}
CSS;
$this->registerCss($style);

?>
<div class="uploader">
    <div class="">
        <button class="btn btn-outline-dark m-2 " onclick="$('.uploader').fadeOut();window.location.reload()">
            <i class="fa fa-backward"></i> <?= Module::t('upload_return_to_files_list') ?>
        </button>
        <hr>
        <div class="">
            <?=
            Tabs::widget([
                'options' => [
                    'class' => 'justify-content-center'
                ],
                'items' => [
                    [
                        'label' => Module::t('upload_base'),
                        'active' => true,
                        'url' => '#baseUpload',
                        'linkOptions' => [
                            'data-toggle' => 'tab'
                        ]
                    ],
                    [
                        'label' => Module::t('upload_url'),
                        'url' => '#urlUpload',
                        'visible' => $module->params['url_upload'],
                        'linkOptions' => [
                            'data-toggle' => 'tab'
                        ]
                    ],
                ]]);
            ?>
            <div class="tab-content">
                <div class="tab-pane active container" id="baseUpload">
                    <!-- The file upload form used as target for the file upload widget -->
                    <?php
                    $uploadUrl = \yii\helpers\Url::to(['upload']);
                    $form = ActiveForm::begin([
                        'options' => [
                            'id' => 'fileupload',
                            'enctype' => 'multipart/form-data',
                        ],
                        'action' => $uploadUrl,
                        'method' => 'POST'
                    ]);
                    ?>

                    <?= Html::hiddenInput('unique_name', Yii::$app->request->getQueryParam('unique_name')) ?>
                    <div class="container2">
                        <div class="fileupload-buttons">
                            <div class="fileupload-progress">
                                <div class="progress progress-striped active" role="progressbar"
                                     aria-valuemin="0" aria-valuemax="100">
                                    <div class="bar bar-success" style="width:0%;"></div>
                                </div>
                                <div class="progress-extended"></div>
                            </div>
                            <div class="text-center">
                                <button class="btn btn-success fileinput-button">
                                    <span><?= Module::t('upload_base_add_files'); ?></span>
                                    <input type="file" name="files[]" multiple="multiple">
                                    <?= Html::input('file', 'files[]', null, ['multiple' => true]) ?>
                                </button>
                                <button type="button" onclick="submitFiles()" class="btn btn-primary start">
                                    <span><?= Module::t('upload_base_start'); ?></span>
                                </button>
                                <span class="fileupload-process"></span>
                            </div>
                        </div>
                        <div id="filesTable" style="height: 60%;overflow-y: auto">
                            <table role="presentation" class="table table-striped table-condensed small">
                                <tbody class="files"></tbody>
                            </table>
                        </div>
                        <div class="upload-help"><?= Module::t('upload_base_help'); ?></div>
                    </div>

                    <?php
                    ActiveForm::end();
                    ?>
                    <!-- The template to display files available for upload -->
                    <script id="template-upload" type="text/x-tmpl">
                    {% for (var i=0, file; file=o.files[i]; i++) { %}
                        <tr class="template-upload">
                            <td>
                                {% if (file.type.search('image') === 0) { %}
                                    <span class="preview"></span>
                                {% }else{ %}
                                    <img class="file_upload_icon" src="<?= $assetUrl ?>/img/extensions/{%= file.name.split('.').pop() %}.png"
                                     onError="this.onerror=null;this.src='<?= $assetUrl ?>/img/extensions/blank.png';"
                                     />
                                {% } %}
                            </td>
                            <td>
                                <p class="name">{%=file.relativePath%}{%=file.name%}</p>
                                <strong class="error text-danger"></strong>
                            </td>
                            <td>
                                <p class="size">Processing...</p>
                                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar bar-success" style="width:0%;"></div></div>
                            </td>
                            <td>
                                {% if (!i && !o.options.autoUpload) { %}
                                    <button class="btn btn-primary start" disabled >
                                        <i class="fa fa-upload"></i>
                                    </button>
                                {% } %}
                                {% if (!i) { %}
                                    <button class="btn btn-danger cancel" onclick="delete filesData['{%= file.name+file.lastModified %}']">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                {% } %}
                            </td>
                        </tr>
                    {% } %}

                    </script>
                    <!-- The template to display files available for download -->
                    <script id="template-download" type="text/x-tmpl">
                    {% for (var i=0, file; file=o.files[i]; i++) { %}
                        <tr class="template-download">
                            <td>
                                <span class="preview">
                                    {% if (file.error) { %}
                                    <i class="icon icon-remove"></i>
                                    {% } else { %}
                                    <i class="icon icon-ok"></i>
                                    {% } %}
                                </span>
                            </td>
                            <td>
                                <p class="name">
                                    {% if (file.url) { %}
                                        <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                                    {% } else { %}
                                        <span>{%=file.name%}</span>
                                    {% } %}
                                </p>
                                {% if (file.error) { %}
                                    <div><span class="label label-danger">Error</span> {%=file.error%}</div>
                                {% } %}
                            </td>
                            <td>
                                <span class="size">{%=o.formatFileSize(file.size)%}</span>
                            </td>
                            <td></td>
                        </tr>
                    {% } %}

                    </script>
                </div>
                <?php if ($module->params['url_upload']) { ?>
                    <div class="tab-pane container" id="urlUpload">
                        <br/>
                        <?php
                        $urlUploadModel = new \yii\base\DynamicModel(['file_address']);
                        $form = ActiveForm::begin(['action' => \yii\helpers\Url::to(['upload'])]);
                        echo $form->field($urlUploadModel, 'file_address')
                            ->textInput([
                                'name' => 'url',
                                'placeHolder' => Module::t('upload_url_address')
                            ])
                            ->label(Module::t('upload_url_address'));
                        echo Html::hiddenInput('unique_name', Yii::$app->request->getQueryParam('unique_name'));
                        echo Html::submitButton(Module::t('upload_url'), ['class' => 'btn btn-primary']);
                        ActiveForm::end();
                        ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php
$can_multiple = $is_multiple ? 'true' : 'false';
$js = <<<JS
let filesData = {};
$('#fileupload').fileupload({
  // Uncomment the following to send cross-domain cookies:
  //xhrFields: {withCredentials: true},
    url: '$uploadUrl'
});
function submitFiles(){
  if(Object.keys(filesData).length === 0){
    alert("please add files first");
    return false;
  }
  $.each(filesData,function(fileName, file) {
    file.submit();
  });
  return true;
}
$('#fileupload').bind('fileuploadadd', function (e, data) {
  if(! $can_multiple && Object.keys(filesData).length > 0){
    alert("you only can upload one file for this file type");
    return false;
  }
  const name = data.files[0].name + data.files[0].lastModified;
  filesData[name] = data;
}).on('fileuploaddone', function (e, data) {
    const name = data.files[0].name + data.files[0].lastModified;
    delete filesData[name];
})

JS;
$this->registerJs($js, $this::POS_END);

?>
