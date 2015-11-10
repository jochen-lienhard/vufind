$(document).ready(function(){
  var cartItems = getCartItems();
  
  
  if (cartItems.length > 0) {
    $('#modal').attr('data-backdrop',"static");
    $('#modal').attr('data-keyboard',"false");
    $('#modal .close').addClass('hidden');
    
    Lightbox.get('Cart','PostLogin');
  }
});