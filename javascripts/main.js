function openModal(){
  $('#modal').css({'top':(window.scrollY+50)+'px'});
  $('#modal,#modal-overlay').fadeIn(300);
};

function closeModal(){
  $('#modal,#modal-overlay').fadeOut(300);
}
