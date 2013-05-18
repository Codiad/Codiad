function openModal(){
  $('#modal').css({'top':(window.scrollY+50)+'px'});
  $('#modal,#modal-overlay').fadeIn(300);
};

function closeModal(){
  $('#modal,#modal-overlay').fadeOut(300);
}

$(function(){
   
   $.getJSON('https://api.github.com/repos/Codiad/Codiad/tags', function(data){
      $('.version').text(data[0].name); 
   });
   
   
   if(!localStorage.getItem('banner')) {
       setTimeout(function(){
               $('#demo_drop-in').animate({'margin-top':'-70px'},1000);
               localStorage.setItem('banner', 'true');
       },2000);
   } else {
       $('#demo_drop-in').hover(function(){
           hovint = setTimeout(function(){
               $('#demo_drop-in').animate({'margin-top':'-70px'},500);
           },250);
       },function(){
           clearTimeout(hovint);
           $(this).animate({'margin-top':'-170px'},500);
       });
   }
    
});