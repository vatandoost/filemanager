<?php

namespace vatandoost\filemanager\commands;

use vatandoost\filemanager\models\FileType;
use yii\console\Controller;
use yii\console\widgets\Table;

class FilesController extends Controller
{
    public function actionTypes()
    {
        $types = FileType::find()
            ->select([
                'file_type_id',
                'name',
                'title',
                'is_public',
                'files_path',
                'manager_class',
            ])
            ->asArray()->all();
        echo Table::widget([
            'headers' => [
                'ID',
                'Name',
                'Title',
                'public',
                'path',
                'manager',
            ],
            'rows' => $types
        ]);
    }

    public function actionCreateType()
    {
        $data = [
            'name' => '',
            'title' => '',
            'is_public' => false,
            'mime_types' => '',
            'max_size' => '',
            'extensions' => '',
            'files_path' => '',
            'manager_class' => 'manager class',
            'handler_type' => 'type of handler class',
            'has_public_thumb' => 'public thumbnail',
            'has_force_relation_id' => 'force to has relation id to work with filemanager',
        ];
        $data['name'] = $this->prompt('Name?' . PHP_EOL);
        $data['title'] = $this->prompt('Title?' . PHP_EOL);
        $data['is_public'] = $this->confirm('Is Public? ' . PHP_EOL, true);
        $data['mime_types'] = $this->prompt('Which mime types? (seperate with ,)' . PHP_EOL);
        $data['extensions'] = $this->prompt('Which file extensions? (seperate with ,)' . PHP_EOL);
        $data['manager_class'] = $this->prompt('manager_class? (example: backend\models\Post)' . PHP_EOL);
        $data['handler_type'] = $this->prompt('Handler type?  (1: Local, 2: FTP) ' . PHP_EOL);
        $data['has_public_thumb'] = $this->confirm('Has public thumbnail?' . PHP_EOL, true);
        $data['has_force_relation_id'] = $this->confirm('force to has relation id to work with filemanager' . PHP_EOL, false);
        $type = new FileType($data);
        if (!$type->validate()) {
            $this->stderr(implode(PHP_EOL, $type->firstErrors) . PHP_EOL);
            return 1;
        }
        $type->save();
        $this->stdout('new type saved successfully.' . PHP_EOL);
    }
}
