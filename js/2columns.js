  
  window.onload = function () {
 
  $(document).ready(function() {
    var size = $("#data > p").size();
 $(".c1 > p").each(function(index){
  if (index >= size/2){
   $(this).appendTo("#c2");
    
    $('head').append('<link rel="stylesheet" href="http://beta.szentiras.hu/css/2columns.css" type="text/css" />');

    
    
  }
 });
  });
 
}