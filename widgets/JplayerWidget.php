<?php


namespace vatandoost\filemanager\views\widgets;


use vatandoost\filemanager\libs\DialogViewHelper;
use yii\base\Widget;

class JplayerWidget extends Widget
{
    public $fileInfo;
    public $title;

    public function run()
    {
        $preview_file = $this->fileInfo['url'];
        if (DialogViewHelper::isAudio($this->fileInfo)) {
            $script = <<<JS
$(document).ready(function () {
    $("#jquery_jplayer_1").jPlayer({
      ready: function () {
        $(this).jPlayer("setMedia", {
          title: "$this->title",
          mp3: "$preview_file",
          m4a: "$preview_file",
          oga: "$preview_file",
          wav: " $preview_file"
        });
      },
      swfPath: "js",
      solution: "html,flash",
      supplied: "mp3, m4a, midi, mid, oga,webma, ogg, wav",
      smoothPlayBar: true,
      keyEnabled: false
    });
  });
JS;
        } elseif (DialogViewHelper::isVideo($this->fileInfo)) {
            $script = <<<JS
$(document) . ready(function () {

    $("#jquery_jplayer_1") . jPlayer({
      ready: function ()
        {
            $(this) . jPlayer("setMedia", {
          title: "$this->title",
          m4v: "$preview_file",
          ogv: "$preview_file",
          flv: "$preview_file"
        });
      },
      swfPath: "js",
      solution: "html,flash",
      supplied: "mp4, m4v, ogv, flv, webmv, webm",
      smoothPlayBar: true,
      keyEnabled: false
    });
});
JS;
        }

        $html = <<<HTML
<div id="jp_container_1" class="jp-video" style="margin:0 auto;direction: ltr">
    <div class="jp-type-single">
        <div id="jquery_jplayer_1" class="jp-jplayer"></div>
        <div class="jp-gui">
            <div class="jp-video-play">
                <a href="javascript:;" class="jp-video-play-icon" tabindex="1">play</a>
            </div>
            <div class="jp-interface">
                <div class="jp-progress">
                    <div class="jp-seek-bar">
                        <div class="jp-play-bar"></div>
                    </div>
                </div>
                <div class="jp-current-time"></div>
                <div class="jp-duration"></div>
                <div class="jp-controls-holder">
                    <ul class="jp-controls">
                        <li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
                        <li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
                        <li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
                        <li><a href="javascript:;" class="jp-mute" tabindex="1"
                               title="mute">mute</a></li>
                        <li><a href="javascript:;" class="jp-unmute" tabindex="1"
                               title="unmute">unmute</a>
                        </li>
                        <li><a href="javascript:;" class="jp-volume-max" tabindex="1"
                               title="max volume">max volume</a></li>
                    </ul>
                    <div class="jp-volume-bar">
                        <div class="jp-volume-bar-value"></div>
                    </div>
                    <ul class="jp-toggles">
                        <li><a href="javascript:;" class="jp-full-screen" tabindex="1"
                               title="full screen">full screen</a></li>
                        <li><a href="javascript:;" class="jp-restore-screen" tabindex="1"
                               title="restore screen">restore screen</a></li>
                        <li><a href="javascript:;" class="jp-repeat" tabindex="1"
                               title="repeat">repeat</a>
                        </li>
                        <li><a href="javascript:;" class="jp-repeat-off" tabindex="1"
                               title="repeat off">repeat off</a></li>
                    </ul>
                </div>
                <div class="jp-title" style="display:none;">
                    <ul>
                        <li></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="jp-no-solution">
            <span>Update Required</span>
            To play the media you will need to either update your browser to a recent version or
            update your <a href="https://get.adobe.com/flashplayer/" target="_blank">Flash
                plugin</a>.
        </div>
    </div>
</div>
<script type="application/javascript">
    $script
</script>
HTML;
        return $html;
    }
}
