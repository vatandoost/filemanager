<?php

namespace vatandoost\filemanager\widgets\selector;

use vatandoost\filemanager\Module;
use yii\base\ErrorException;
use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\bootstrap4\InputWidget;
use yii\web\JsExpression;

class Selector extends InputWidget
{
    public $fileTypeName = '';
    public $relationId = '';
    /**
     * you can change the remove icon name
     * @var string
     */
    public $removeBtnIcon = 'fa fa-trash-o';

    /**
     * if multiple is false the result is integer otherwise the result is string like this ["75","101"]
     * @var bool
     */
    public $multiple = false;

    private $fileManagerPathTpl = '/dialog/index?callback=filemanager_selector_callback&unique_name=%s&multiple=%s&field_id=%s';

    public function init()
    {
        $module = Module::getInstance();
        $this->fileManagerPathTpl = $module->id . $this->fileManagerPathTpl;
        if (!array_key_exists('id', $this->options)) {
            $class = explode('\\', get_class($this->model));
            $class = strtolower(end($class));
            $this->options['id'] = "$class-$this->attribute";
        }
        if (!array_key_exists('class', $this->options)) {
            $this->options['class'] = 'form-control';
        }

        $this->options = array_merge($this->options, ['readonly' => true]);
    }

    /**
     * print selector input
     * @return string|void
     * @throws ErrorException
     */
    public function run()
    {
        $uniqueName = $this->fileTypeName . ($this->relationId ? (',' . $this->relationId) : "");

        if (!$this->fileManagerPathTpl) {
            throw new ErrorException();
        }

        $value = $this->value;
        $hiddenInput = Html::hiddenInput($this->name, $this->value, [
            'data-unique_name' => $uniqueName,
            'data-multiple' => $this->multiple ? "1" : "",
        ]);
        if ($this->hasModel()) {
            $value = $this->model->{$this->attribute};
            $hiddenInput = Html::activeHiddenInput($this->model, $this->attribute, [
                'data-unique_name' => $uniqueName,
                'data-multiple' => $this->multiple ? "1" : "",
            ]);
        }
        if (empty($value)) {
            $labelText = '';
        } else {
            if (!$this->multiple) {
                $labelText = "1 " . Module::t('file(s) selected');
            } else {
                $count = count(explode(',', $value));
                $labelText = "$count " . Module::t('file(s) selected');
            }
        }
        $input = Html::textInput(
            '_file_name_' . $this->name,
            $labelText,
            array_merge($this->options, ['id' => 'label_' . $this->options['id']])
        );

        $url = sprintf($this->fileManagerPathTpl, $uniqueName, ($this->multiple ? 1 : 0), $this->options['id']);

        $selectBtn = Html::a(Module::t('choose_file'), 'javascript:;', [
            'data-src' => Url::to([$url], true),
            'data-type' => "iframe",
            'data-field-id' => $this->options['id'],
            'class' => 'iframe-btn btn btn-primary',
            'type' => 'button',
        ]);
        $removeBtn = Html::tag('button', '<span class="text-danger ' . $this->removeBtnIcon . '"></span>', [
            'class' => 'btn btn-default',
            'data-field-id' => $this->options['id'],
            'type' => 'button',
            'onclick' => new JsExpression("resetSelectorField('{$this->options['id']}', '$uniqueName')")
        ]);
        echo '
			<div class="input-group" >
				' . $input . $hiddenInput . '<span class="input-group-btn">' . $selectBtn . ' ' . $removeBtn . '</span>
			</div>
		';

        $this->registerClientScript();
    }

    private function registerClientScript()
    {

        $view = $this->getView();
        SelectorAsset::register($view);
        static $init = null;
        if (is_null($init)) {
            $init = true;
            $messages = [
                'files_selected' => Module::t('file(s) selected')
            ];
            $messages = 'file_selector_msgs=' . json_encode($messages) . ';' . PHP_EOL;
            $view->registerJs($messages . '$( document ).ready(function() { initFileSelectorPopups(); });', \yii\web\View::POS_READY);
        }
    }
}
