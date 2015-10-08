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
    
    $('.doExportRecord').click(function() {
      var id = $(this).data('id');
      $.ajax({
        url: path + '/Cart/doExport',
        type:'POST',
        dataType:'html',
        data:{doExport: 'Print', cachePolicy: 'Favorite', 'ids[]': id},
        success:function(data) {
          var win = window.open();
          $(win.document.body).html(data);
          Lightbox.close();
        },
        error:function(d,e) {
          //console.log(d,e); // Error reporting
        }
      });
      return false;
    });
    
    $('.addItemToCart').click(function(){
      var vufindId = $(this).data('id');
      var source = vufindId.split("|")[0];
      var id = vufindId.split("|")[1];
      removeItemFromCart(id, source);
      addItemToCart(id, source);
      return false;
    });
  });
 
 function userAction() {
   return false;
 }
