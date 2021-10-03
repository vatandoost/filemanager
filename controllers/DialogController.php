<?php


namespace vatandoost\filemanager\controllers;

use vatandoost\filemanager\libs\BaseManager;
use vatandoost\filemanager\libs\FileHelper;
use vatandoost\filemanager\libs\FileValidator;
use vatandoost\filemanager\libs\Utils;
use vatandoost\filemanager\models\File;
use vatandoost\filemanager\models\FileType;
use vatandoost\filemanager\Module;
use vatandoost\filemanager\libs\DialogViewHelper;
use vatandoost\filemanager\views\widgets\JplayerWidget;
use yii\web\Controller;
use yii;

class DialogController extends Controller
{
    public $layout = '@vatandoost/filemanager/views/layouts/main';

    public function actionIndex()
    {
        $unique_name = Yii::$app->request->getQueryParam('unique_name');
        $unique_name = explode(',', $unique_name);
        $file_type_name = $unique_name[0];
        $relation_id = @$unique_name[1] ?: null;

        $fileType = FileType::findOne(['name' => $file_type_name]);
        if (empty($fileType)) {
            throw new yii\web\ServerErrorHttpException("this file type does not exist");
        }
        $manager = $fileType->getManager();
        $this->view->title = $fileType->title;

        if (empty($relation_id) && $fileType->has_force_relation_id) {
            throw new yii\web\ForbiddenHttpException("relation id is required");
        }


        $tbf = File::tableName();
        $filesQuery = $manager
            ->filesQuery($file_type_name)
            ->andFilterWhere(['relation_id' => $relation_id]);

        $searchQuery = Yii::$app->request->getQueryParam('search');
        $filesQuery->andFilterWhere(['like', 'original_name', $searchQuery]);

        if ($sort = Yii::$app->request->getQueryParam('sort', false)) {
            $filesQuery->orderBy([$tbf . '.' . ltrim($sort, '-') => (strpos($sort, '-') === 0) ? SORT_ASC : SORT_DESC]);
        }
        $files = $filesQuery->all();
        return $this->render('index', [
            'files' => $files,
            'is_multiple' => $this->checkMultiple($manager, $fileType)
        ]);
    }

    private function checkMultiple($manager, $fileType)
    {
        if (empty($relation_id) && !$fileType->has_force_relation_id) {
            return true;
        }
        /** @var FileValidator[] $validators */
        $validators = $manager->getActiveFileValidators();
        $is_class_name_type = strtolower(strtr($manager::className(), '\\', '_')) == $fileType->name;
        $is_multiple = true;
        foreach ($validators as $validator) {
            if (!(empty($validator->file_type_name) && $is_class_name_type) && $validator->file_type_name != $fileType->name) {
                continue;
            }
            $is_multiple = $validator->multiple;
            $target_field = $validator->target_field;
            break;
        }
        return empty($target_field) && $is_multiple;
    }

    public function actionUpload()
    {
        $params = Yii::$app->controller->module->params;
        Yii::$app->response->format = 'json';
        if (Yii::$app->request->isGet) {
            return [];
        }
        $unique_name = Yii::$app->request->getBodyParam('unique_name');
        $unique_name = explode(',', $unique_name);
        $file_type_name = $unique_name[0];
        $relation_id = @$unique_name[1] ?: null;
        $fileType = FileType::findOne(['name' => $file_type_name]);
        if (empty($fileType)) {
            throw new yii\web\ServerErrorHttpException("this file type does not exist");
        }
        $manager = $fileType->getManager();
        if (property_exists($manager, 'fileTypeName')) {
            $manager->fileTypeName = $fileType->name;
        }

        if (method_exists($manager, 'fileTypeConfig')) {
            $params = array_merge($params, $manager->fileTypeConfig($fileType, $relation_id));
        }
        if (!$params['upload_files']) {
            throw new yii\web\ForbiddenHttpException("upload file is disable");
        }

        $this->checkActiveUrlAndDownloadFromUrl($params);

        /** @var FileValidator[] $validators */
        $validators = $manager->getActiveFileValidators();
        $is_class_name_type = strtolower(strtr(get_class($manager), '\\', '_')) == $file_type_name;
        $is_default_manager = get_class($manager) == BaseManager::class;
        $attribute = null;
        $is_multiple = true;
        foreach ($validators as $validator) {
            if (
                !(empty($validator->file_type_name) && $is_class_name_type) &&
                !(empty($validator->file_type_name) && $is_default_manager) &&
                $validator->file_type_name != $file_type_name
            ) {
                continue;
            }
            $attribute = is_array($validator->attributes) ? $validator->attributes[0] : $validator->attributes;
            $is_multiple = $validator->multiple;
            $relation_field = $validator->relation_field;
            break;
        }

        if (!empty($relation_field) && !empty($relation_id) && !($manager instanceof BaseManager)) {
            $manager = $manager::findOne([$relation_field => $relation_id]);
            if (empty($manager)) {
                throw new yii\web\NotFoundHttpException("related model does not exist");
            }
        }
        if (!empty($relation_field) && property_exists($manager, $relation_field)) {
            $manager->$relation_field = $relation_id;
        }

        if (empty($attribute)) {
            throw new yii\web\NotFoundHttpException('attribute does not exist');
        }
        if ($is_multiple) {
            //var_dump( yii\web\UploadedFile::getInstancesByName('files'));die;
            $manager->$attribute = yii\web\UploadedFile::getInstancesByName('files');
        } else {
            $_FILES['files'] = [
                'name' => $_FILES['files']['name'][0],
                'tmp_name' => $_FILES['files']['tmp_name'][0],
                'size' => $_FILES['files']['size'][0],
                'type' => $_FILES['files']['type'][0],
                'error' => $_FILES['files']['error'][0]
            ];
            $manager->$attribute = yii\web\UploadedFile::getInstanceByName('files');
        }

        if ($uploadedFiles = $manager->saveFiles([$attribute], true, true)) {
            $files = [];
            foreach ($uploadedFiles as $uploadedFile) {
                $fileInfo = FileHelper::getFileInfoByModel($uploadedFile);
                $files[] = [
                    'file_id' => $uploadedFile->file_id,
                    'name' => yii\helpers\Html::encode($uploadedFile->original_name),
                    'size' => $fileInfo['size'],
                    'url' => $fileInfo['url'],
                    'thumbnailUrl' => $fileInfo['thumbnail'],
                    'deleteUrl' => yii\helpers\Url::to(['file/delete', 'file_id' . $uploadedFile->file_id]),
                    'deleteType' => 'POST',
                ];
            }
            if (!Yii::$app->request->isAjax) {
                $this->redirect(Yii::$app->request->referrer);
            }
            return [
                'files' => $files,
            ];
        }
        if (!Yii::$app->request->isAjax) {
            throw new yii\web\ServerErrorHttpException("error in upload file");
        }
        return [];
    }

    private function checkActiveUrlAndDownloadFromUrl($params)
    {
        $post = Yii::$app->request->post();
        if (isset($post['url']) && strlen($post['url']) < 2000 && $params['url_upload']) {
            $url = $post['url'];
            $model = new yii\base\DynamicModel(['url']);
            $model->addRule('url', 'url');
            $model->load($post);

            if ($model->validate('url')) {
                $temp = Yii::getAlias('@runtime') . '/' . uniqid('urlfile_') . '.tmp';

                $ch = curl_init($url);
                $fp = fopen($temp, 'w+');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_exec($ch);
                if (curl_errno($ch)) {
                    curl_close($ch);
                    throw new \Exception('Invalid URL');
                }
                curl_close($ch);
                fclose($fp);
                Utils::loadFileFromLocalPath('files', basename($post['url']), $temp);
                /*$_FILES['files'] = array(
                    'name' => array(basename($post['url'])),
                    'tmp_name' => array($temp),
                    'size' => array(filesize($temp)),
                    'type' => null,
                    'error' => 0
                );*/
            } else {
                throw new yii\web\ServerErrorHttpException('invalid address');
            }
        }
    }

    public function actionPreview($action)
    {
        $get = Yii::$app->request->get();
        switch ($action) {
            case 'media_preview':
                if (isset($get['file'])) {
                    $get['file'] = (int)$get['file'];
                }
                if (isset($get['title'])) {
                    $get['title'] = yii\helpers\Html::encode(strip_tags($get['title']));
                }
                $info = FileHelper::getFileInfo($get['file']);
                $content = JplayerWidget::widget([
                    'fileInfo' => $info,
                    'title' => $get['title']
                ]);
                return $content;

                break;
            case 'cad_preview':

                $url_file = FileHelper::getFileUrl($get['file']);
                $cad_url = urlencode($url_file);
                $cad_html = "<iframe src=\"//sharecad.org/cadframe/load?url=" . $cad_url . "\" class=\"preview-iframe\" scrolling=\"no\"></iframe>";
                $ret = $cad_html;
                return $ret;
                break;
            case 'pdf_preview':

                $url_file = FileHelper::getFileUrl($get['file']);

                $html = "<iframe src=\"" . $url_file . "\" class=\"preview-iframe\" scrolling=\"no\"></iframe>";
                return $html;
                break;
            case 'convert_pdf_preview':

                $file = FileHelper::getFileInfo($get['file']);
                $guid = $file['guid'];
                $url_file = yii\helpers\Url::to(['dialog/pdf_preview', 'id' => $guid]);
                $html = "<iframe src=\"" . $url_file . "\" class=\"preview-iframe\" scrolling=\"no\"></iframe>";
                return $html;
                break;
            case 'get_file': // preview or edit
                $sub_action = $get['sub_action'];
                $preview_mode = $get["preview_mode"];
                $config = Module::getInstance()->params;
                $info = FileHelper::getFileInfo($get['file']);

                if ($preview_mode == 'google') {
                    $is_allowed = $config['googledoc_enabled'];
                    $allowed_file_exts = $config['googledoc_file_exts'];
                }

                if (!isset($allowed_file_exts) || !is_array($allowed_file_exts)) {
                    $allowed_file_exts = array();
                }

                if (!isset($info['extension'])) {
                    $info['extension'] = '';
                }
                if (
                    !in_array($info['extension'], $allowed_file_exts)
                    || !isset($is_allowed)
                    || $is_allowed === false
                ) {
                    throw new yii\web\MethodNotAllowedHttpException(sprintf(Module::t('File_Open_Edit_Not_Allowed'), ($sub_action == 'preview' ? strtolower(Module::t('Open')) : strtolower(Module::t('Edit')))));
                    return;
                }
                if ($sub_action == 'preview') {
                    if ($preview_mode == 'text') {
                        // get and sanities
                        $data = FileHelper::getFileContent($get['file']);
                        $data = htmlspecialchars(htmlspecialchars_decode($data));
                        $ret = '';

                        $ret .= '<script src="https://rawgit.com/google/code-prettify/master/loader/run_prettify.js?autoload=true&skin=sunburst"></script>';
                        $ret .= '<?prettify lang=' . $info['extension'] . ' linenums=true?><pre class="prettyprint"><code class="language-' . $info['extension'] . '">' . $data . '</code></pre>';
                    } elseif ($preview_mode == 'google') {
                        $url_file = FileHelper::getFileUrl($get['file']);

                        $googledoc_url = urlencode($url_file);
                        $ret = "<iframe src=\"https://docs.google.com/viewer?url=" . $googledoc_url . "&embedded=true\" class=\"preview-iframe\" ></iframe>";
                    }
                } else {
                    $data = stripslashes(htmlspecialchars(FileHelper::getFileContent($get['file'])));
                    if (in_array($info['extension'], array('html', 'html'))) {
                        $ret = '<script src="https://cdn.ckeditor.com/ckeditor5/12.1.0/classic/ckeditor.js"></script><textarea id="textfile_edit_area" style="width:100%;height:300px;">' . $data . '</textarea><script>setTimeout(function(){ ClassicEditor.create( document.querySelector( "#textfile_edit_area" )).catch( function(error){ console.error( error ); } );  }, 500);</script>';
                    } else {
                        $ret = '<textarea id="textfile_edit_area" style="width:100%;height:300px;">' . $data . '</textarea>';
                    }
                }

                return $ret;
                break;
            default:
                throw new yii\web\MethodNotAllowedHttpException(Module::t('no action passed'));
        }
    }

    public function actionPdf_preview($id)
    {
        $config = (Module::getInstance())->params;
        $response = Yii::$app->response;
        $file = File::findOne(['guid' => $id]);
        if (empty($file)) {
            if (empty($fileRecord) || !is_file($file)) {
                $file = Yii::getAlias('@vatandoost/filemanager/assets/images/no_image_available.png');
                return $response->sendFile($file, 'not_found.png');
            }
        }
        if (!$file->fileType->is_public) {

            $class = $file->fileType->getManager();

            if (!Module::isAdmin() && method_exists($class, 'canView')) {
                if (!$class->canView($file)) {
                    throw new yii\web\ForbiddenHttpException("You don't have access to read this file");
                }
            }
        }
        if (!class_exists('GuzzleHttp\Client')) {
            throw new yii\web\NotFoundHttpException("unable to send request ! guzzle http client class required");
        }
        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', $config['unoconvBaseUrl'] . '/unoconv/pdf', [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => FileHelper::getFileContent(null, $file),
                    'filename' => $file->original_name . '.' . $file->extension
                ],
            ]
        ]);
        @$pdf = tempnam('/tmp', 'pre');
        file_put_contents($pdf, $res->getBody()->getContents());
        header("Content-type:application/pdf");
        header("Content-Disposition:inline;filename=" . $file->original_name . '.pdf');
        readfile($pdf);
        exit;
    }
}
