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
    
    $('.cartAction').click(function(){
      var vufindId = $(this).data('id');
      var source = vufindId.split("|")[0];
      var id = vufindId.split("|")[1];
      
      var fullCartItems = getFullCartItems();
      if (fullCartItems.indexOf(source+'|'+id) == -1) {
        addItemToCart(id, source);
      } else  {
        removeItemFromCart(id, source);
      }
      updateCartStatus(this);
      
      return false;
    });
    
    updateCartStatus();
});

function updateCartStatus(selector) {
  if (typeof(selector) == "undefined") {
    selector = $('.cartAction');
  } 
  
  $(selector).each(function(idx, val) {
    var vufindId = $(val).data('id');
    var fullCartItems = getFullCartItems();
    if (fullCartItems.indexOf(vufindId) == -1) {
      $(val).removeClass('fa-star');
      $(val).addClass('fa-star-o');
    } else {
      $(val).removeClass('fa-star-o');
      $(val).addClass('fa-star');
    }
  });
  return false;
} 

function userAction() {
  return false;
}

function myhelp(a,b) {
      jQuery("#tippHeader").text(a);
      jQuery("#tippBody").html(b);
      jQuery("#searchTipps").show();
}

function rdsEditList(listId) {
  Lightbox.get('MyResearch', 'EditList', {id:listId}); 
}
