function resetSelectorField(fieldId, uniqueName) {
  $('#' + fieldId).val('');
  $('#label_' + fieldId).val('');
  sessionStorage.removeItem(uniqueName);
}

function initFileSelectorPopups() {
  $('.iframe-btn').fancybox({
    toolbar: false,
    smallBtn: true,
    iframe: {
      preload: false
    },
    beforeShow: function () {
      var selectorBtn = $(this.opts.$orig);
      var input = $('#' + $(selectorBtn).attr('data-field-id'));
      var uniqueName = input.attr('data-unique_name');
      var val = input.val();
      if (!input.data('multiple') && !!val) {
        val = JSON.stringify([val]);
      }
      val = !val ? JSON.stringify([]) : val;
      this.src += ('&files=' + encodeURIComponent(val));
      sessionStorage.setItem(uniqueName, val);
      console.log(selectorBtn, uniqueName, val);
    }
  });

}


function filemanager_selector_callback(files, fieldId) {
  if (!fieldId) {
    return;
  }
  var target = jQuery('#' + fieldId);
  var label = jQuery('#label_' + fieldId);
  label.val(files.length + ' ' + file_selector_msgs.files_selected);
  if (target.data('multiple')) {
    target.val(JSON.stringify(files)).trigger('change');
  } else {
    var file = files.pop();
    target.val(file).trigger('change');
  }

}

