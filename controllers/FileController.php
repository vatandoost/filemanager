<?php

namespace vatandoost\filemanager\controllers;

use vatandoost\filemanager\libs\FileHelper;
use vatandoost\filemanager\libs\BaseManager;
use vatandoost\filemanager\libs\Utils;
use vatandoost\filemanager\models\File;
use vatandoost\filemanager\Module;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

class FileController extends Controller
{
    private $config = [];

    public function init()
    {
        $this->config = (Module::getInstance())->params;
        parent::init();
    }

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'upload' => ['POST'],
                    'rename' => ['PUT', 'POST'],
                    'delete' => ['POST', 'DELETE'],
                ],
            ],
        ];
    }

    public function actionGet($id, $thumb = false, $watch = false)
    {
        $response = Yii::$app->response;
        $check = preg_match('/(.*?)-([0-9.]{1,4}.*)\./', $id, $res);
        if ($check) {
            $id = rtrim($res[0], '.');
        }
        $file = File::findOne(['guid' => $id]);
        if (empty($file)) {
            if (empty($fileRecord) || !is_file($file)) {
                $file = Yii::getAlias('@vatandoost/filemanager/assets/images/no_image_available.png');
                return $response->sendFile($file, 'not_found.png');
            }
        }

        $class = $file->fileType->getManager();
        if (!Module::isAdmin() && method_exists($class, 'canView')) {
            if (!$class->canView($file)) {
                throw new ForbiddenHttpException("You don't have access to this file");
            }
        }
        $filePath = FileHelper::getLocalFilePath($file);

        if (method_exists($class, 'readFile')) {
            return $class->readFile($filePath, $file, $thumb);
        }

        if ($watch) {
            return Utils::readFile($filePath);
        } else {
            return $response->sendFile($filePath, $file->original_name . '.' . $file->extension);
        }
    }


    public function actionInfo($id, $watch = false)
    {
        $response = Yii::$app->response;
        $file = File::findOne(['file_id' => $id]);
        if (empty($file)) {
            if (empty($fileRecord) || !is_file($file)) {
                $file = Yii::getAlias('@vatandoost/filemanager/assets/images/no_image_available.png');
                return $response->sendFile($file, 'not_found.png');
            }
        }

        $class = $file->fileType->getManager();
        if (!Module::isAdmin() && method_exists($class, 'canView')) {
            if (!$class->canView($file)) {
                throw new ForbiddenHttpException("You don't have access to this file");
            }
        }
        if ($watch) {
            $filePath = FileHelper::getLocalFilePath($file);

            if (method_exists($class, 'readFile')) {
                return $class->readFile($filePath, $file, true);
            }

            return FileHelper::readFile($filePath);

        }
        $file = Yii::getAlias('@vatandoost/filemanager/assets/dialog/img/ico/' . $file->extension . '.jpg');
        return $response->sendFile($file, 'file.jpg');
    }

    public function actionDelete()
    {
        $id = Yii::$app->request->getBodyParam('id');
        FileHelper::deleteFile($id);
        return true;
    }

    public function actionRename()
    {
        $id = Yii::$app->request->getBodyParam('id');
        $name = Yii::$app->request->getBodyParam('name');
        FileHelper::renameFile($id, $name);
        return true;
    }
}
