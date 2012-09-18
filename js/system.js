/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See 
*  [root]/license.txt for more. This information must remain intact.
*/

//////////////////////////////////////////////////////////////////////
// loadScript instead of getScript (checks and balances and shit...)
//////////////////////////////////////////////////////////////////////

jQuery.loadScript = function (url, arg1, arg2) {
  var cache = true, callback = null;
  //arg1 and arg2 can be interchangable
  if ($.isFunction(arg1)){
    callback = arg1;
    cache = arg2 || cache;
  } else {
    cache = arg1 || cache;
    callback = arg2 || callback;
  }
               
  var load = true;
  //check all existing script tags in the page for the url
  jQuery('script[type="text/javascript"]')
    .each(function () { return load = (url != $(this).attr('src')); });
  if (load){
    //didn't find it in the page, so load it
    jQuery.ajax({ type: 'GET', url: url, success: callback, dataType: 'script', cache: cache });
  } else {
    //already loaded so just call the callback
    if (jQuery.isFunction(callback)) { callback.call(this); };
  };
};

//////////////////////////////////////////////////////////////////////
// Init
//////////////////////////////////////////////////////////////////////

$(function(){ 
    // Sliding sidebars
    sidebars.init();
    $(window).on('load resize',function(){
        if($("#sb-left").css('left')!==0 && !sidebars.user_lock){ margin_l = 10; reduction=20; }
        else{ margin_l = 300; reduction = 310; }
        $('#editor-region').css({'margin-left':margin_l+'px','width':($('body').outerWidth()-reduction)+'px','height':($('body').outerHeight()-25)+'px'});
    });
});

//////////////////////////////////////////////////////////////////////
// Parse JSEND Formatted Returns
//////////////////////////////////////////////////////////////////////

var jsend = {

    parse : function(d){ // (Data)
        var obj = $.parseJSON(d);
        if(obj.status=='error'){
            message.error(obj.message);
            return 'error';
        }else{
            return obj.data;
        }
    }

};

//////////////////////////////////////////////////////////////////////
// Modal
//////////////////////////////////////////////////////////////////////

var modal = {

    load : function(w,u){ // (Width, URL)
        $('#modal').css({'top':'15%','left':'50%','width':w+'px','margin-left':'-'+Math.ceil(w/2)+'px'}).draggable({ handle: '#drag-handle' });
        $('#modal-content').html('<div id="modal-loading"></div>');
        $('#modal-content').load(u);
        $('#modal, #modal-overlay').fadeIn(200);
        sidebars.modal_lock = true;
    },
        
    hide_overlay : function(){
        $('#modal-overlay').hide();  
    },
    
    unload : function(){
        $('#modal-content form').die('submit'); // Prevent form bubbling
        $('#modal, #modal-overlay').fadeOut(200);
        $('#modal-content').html('');
        sidebars.modal_lock = false;
        if(!sidebars.user_lock){ // Slide sidebar back
            $('#sb-left').animate({'left':'-290px'},300,'easeOutQuart');
            $('#editor-region').animate({'margin-left':'10px','width':($('body').outerWidth()-20)+'px'},300,'easeOutQuart');
        }
    }

};

//////////////////////////////////////////////////////////////////////
// User Alerts / Messages
//////////////////////////////////////////////////////////////////////

autoclose = null;

var message = {

    success : function(m){ // (Message)
        $('#message').removeClass('error').addClass('success').html(m);
        this.show();
    },
    
    error : function(m){ // (Message)
        $('#message').removeClass('success').addClass('error').html(m);
        this.show();
    },
    
    show : function(){
        clearTimeout(autoclose);
        $('#message').fadeIn(300); 
        autoclose = setTimeout(function(){ message.hide(); },2000); 
    },
    
    hide : function(){ $('#message').fadeOut(300); }
};

//////////////////////////////////////////////////////////////////////
// Workspace Resize
//////////////////////////////////////////////////////////////////////

var sidebars = {
    
    user_lock : true,
    modal_lock : false,

    init : function(){
        
        $('#lock-left-sidebar').on('click',function(){
            if(sidebars.user_lock){
                sidebars.user_lock = false;
                $('#lock-left-sidebar').html('V');
            }else{
                sidebars.user_lock = true;
                $('#lock-left-sidebar').html('U');
            }        
        });
        
        // Left Column Slider
         $("#sb-left").hover(function() {
            var timeout_r = $(this).data("timeout_r");
            if(timeout_r){ clearTimeout(timeout_r); }
            $('#editor-region').animate({'margin-left':'300px','width':($('body').outerWidth()-310)+'px'},300,'easeOutQuart');
            $(this).animate({'left':'0px'},300,'easeOutQuart');
         },function() {
            $(this).data("timeout_r", setTimeout($.proxy(function() {
                if(!sidebars.user_lock && !sidebars.modal_lock){ // Check locks
                    $(this).animate({'left':'-290px'},300,'easeOutQuart');
                    $('#editor-region').animate({'margin-left':'10px','width':($('body').outerWidth()-20)+'px'},300,'easeOutQuart');
                }
            },this), 500));
         }); 
         
         // Right Column Slider
         $("#sb-right").hover(function() {
            var timeout_r = $(this).data("timeout_r");
            if(timeout_r){ clearTimeout(timeout_r); }
            $('#editor-region').animate({'margin-right':'200px'},300,'easeOutQuart');
            $(this).animate({'right':'0px'},300,'easeOutQuart');
         },function() {
            $(this).data("timeout_r", setTimeout($.proxy(function() {
                $(this).animate({'right':'-190px'},300,'easeOutQuart');
                $('#editor-region').animate({'margin-right':'10px'},300,'easeOutQuart');
            },this), 500));
         });    
    }
    
};