/*global path*/

function checkSaveStatuses() {
  var data = $.map($('.result,.record'), function(i) {
    if($(i).find('.hiddenId').length == 0 || $(i).find('.hiddenSource').length == 0) {
      return false;
    }
    return {'id':$(i).find('.hiddenId').val(), 'source':$(i).find('.hiddenSource')[0].value};
  });
  if (data.length) {
    var ids = [];
    var srcs = [];
    for (var i = 0; i < data.length; i++) {
      ids[i] = data[i].id;
      srcs[i] = data[i].source;
    }
    $.ajax({
      dataType: 'json',
      url: path + '/AJAX/JSON?method=getSaveStatuses',
      data: {id:ids, 'source':srcs},
      success: function(response) {
        if(response.status == 'OK') {
          $.each(response.data, function(i, result) {
            var $container = $('#result'+result.record_number).find('.favActionAdd');
            //$container.removeClass('fa-star');
            //$container.addClass('fa-star-o');
            
            var $favActionDel = $('#result'+result.record_number).find('.favActionDel');
            $favActionDel.removeClass('hidden');
          });
        }
      }
    });
  }
}

$(document).ready(function() {
  checkSaveStatuses();
});
