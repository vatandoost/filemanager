bootbox.addLocale('filemanager', locale);

$('.file-box').mouseover(function () {
  $(this).find('.file-tools').fadeIn(200);
}).mouseleave(function () {
  $(this).find('.file-tools').fadeOut(200);
});
$(document).on('click', '.modalMedia', function (e) {
  const _this = $(this);
  e.preventDefault();

  const previewElement = $('#previewMedia');
  const bodyPreviewElement = $(".body-preview");
  const mediaTitleElement = $(".media-title");
  previewElement.removeData("modal");
  previewElement.modal({
    backdrop: 'static',
    keyboard: false
  });

  if (_this.hasClass('audio')) {
    bodyPreviewElement.css('height', '80px');
  } else {
    bodyPreviewElement.css('height', '345px');
  }
  mediaTitleElement.html(_this.attr('data-title'));
  $.ajax({
    url: _this.attr('data-url'),
    success: function (data) {
      bodyPreviewElement.html(data);
    }
  });
});
$(document).on('click', '.file-preview-btn', function (e) {
  const _this = jQuery(this);
  e.preventDefault();
  $.ajax({
    url: _this.attr('data-url'),
    success: function (data) {
      bootbox.dialog({
        title: _this.attr('data-title'),
        message: data,
        size: 'large'
      });
    }
  });
});
$(document).on('click', '.rename-file', function () {
  var _this = jQuery(this);
  if (_this.hasClass('disabled')) return;
  var file_container = _this.closest('.file-box');
  var fileId = file_container.attr('data-id');
  var file_title = $.trim(file_container.attr('data-name'));
  bootbox.prompt({
    title: renameTitle,
    locale: 'filemanager',
    value: file_title,
    callback: function (name) {
      if (name !== null) {
        name = $.trim(name);
        if (name != file_title) {
          renameFile(fileId, name);
        }
      }
    }
  });
});

function renameFile(fileId, fileName) {
  $.ajax({
    url: renameUrl,
    method: 'POST',
    data: {
      id: fileId,
      name: fileName
    },
    success: function (data) {
      $('.file-box[data-id=' + fileId + '] .title-box').text(fileName);
    },
    error: function () {
      bootbox.alert(serverErrorAlert);
    }
  })
}

jQuery('.delete-btn').on('click', function () {
  var _this = jQuery(this);
  if (_this.hasClass('disabled')) return;
  var file_container = _this.closest('.file-box');
  var fileId = file_container.attr('data-id');
  bootbox.confirm(confirmDeleteText, function (result) {
    if (result == true) {
      deleteFile(fileId);
    }
  });
});

function deleteFile(fileId) {
  $.ajax({
    url: deleteUrl,
    method: 'POST',
    data: {
      id: fileId
    },
    success: function (data) {
      $('.file-box[data-id=' + fileId + '] ').remove();
    },
    error: function () {
      bootbox.alert(serverErrorAlert);
    }
  })
}

$('.selection:checkbox:visible').on('change', function () {
  updateSelections();
});

$('.multiple-deselect-btn').on('click', function () {
  $('.selection:checkbox').removeAttr('checked');
  $('.selection:checkbox:checked:visible').prop('checked', false);
  updateSelections();
});

$('.multiple-select-btn').on('click', function () {
  $('.selection:checkbox:visible').prop('checked', true);
  updateSelections();
});

function updateSelections() {
  if ($('.selection:checkbox:checked:visible').length > 0) {
    $(".selection-box").show(300);
  } else {
    $(".selection-box").hide(300);
  }
  let files = [];
  $('.selection:checkbox:checked:visible').each(function () {
    files.push($(this).val());
  });
  if (uniqueName != '') {
    sessionStorage.setItem(uniqueName, JSON.stringify(files));
  }
}

function getFiles() {
  let files = [],
    selector = '.selection:checkbox:checked:visible';
  jQuery(selector).each(function () {
    var file = jQuery(this).val();
    files.push(file);
  });
  return files;
}

if (uniqueName != '') {
  var selected_files = sessionStorage.getItem(uniqueName);
  if (selected_files != null) {
    selected_files = JSON.parse(selected_files);
    jQuery.each(selected_files, function (i, v) {
      jQuery('.selection:checkbox:visible[value=' + v + ']').prop('checked', true);
    });
    updateSelections();
  }
}

function close_window() {
  if (jQuery('#popup').val() == 1) {
    window.close();
  } else {
    if (typeof parent.jQuery(".modal:has(iframe)").modal == "function") {
      parent.jQuery(".modal:has(iframe)").modal("hide");
    }
    if (typeof parent.jQuery !== "undefined" && parent.jQuery) {
      if (typeof parent.jQuery.fancybox == 'object') {
        parent.jQuery.fancybox.getInstance().close();
      } else if (typeof parent.jQuery.fancybox == 'function') {
        parent.jQuery.fancybox.close();
      }
    } else {
      if (typeof parent.$.fancybox == 'function') {
        parent.$.fancybox.close();
      }
    }
  }
}

function getParentWindow() {
  return window.parent;
}

function select(fileId, url) {
  if (typeof fileId === 'undefined') {
    updateSelections();
    var files = getFiles();
  } else {
    var files = [fileId];
  }
  var parentWindow = getParentWindow();
  if (editor === 'ckeditor') {
    var funcNum = CKEditorFuncNum;
    window.opener.CKEDITOR.tools.callFunction(funcNum, url);
    window.close();
  } else {
    if (callback === '') {
      if (typeof parentWindow.filemanager_callback == 'function') {
        parentWindow.filemanager_callback(files, field_id);
      }
    } else {
      if (typeof parentWindow[callback] == 'function') {
        parentWindow[callback](files, field_id);
      }
    }
    close_window();
  }
}