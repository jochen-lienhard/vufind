 $(document).ready(function(){
    var ajaxSearch = $(".resultCountAjax");

    $.each(ajaxSearch, function (i, val) {
      var lookfor = $(val).data('lookfor');
      var searchClassId = $(val).data('searchclassid');
      $.ajax({
        method: "GET",
        url: path + '/AJAX/JSON?method=getResultDetails',
        data: {lookfor: lookfor, searchClassId: searchClassId},
        dataType: "json"
      }).always(function(resultDetails) {
        if (resultDetails.status == 'ERROR') {
          $(val).replaceWith('(<span class="error">!</span>)');
        } else {
          $(val).replaceWith(resultDetails.data.resultCount)
        }
      });

    });
    
  });