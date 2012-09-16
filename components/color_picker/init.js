$(function(){ color_picker.init(); });
    

var color_picker = {
    
    init : function(){
        
        $.loadScript("components/color_picker/color_parser.js");
        $.loadScript("components/color_picker/jquery.colorpicker.js");
        
    },
    
    open : function(){
        
        modal.load(400,'components/color_picker/dialog.php');
        
    },
    
    insert : function(type){
        color = '';
        if(type=='rgb'){
            color = $('.colorpicker_rgb_r input').val()+','+$('.colorpicker_rgb_g input').val()+','+$('.colorpicker_rgb_b input').val();
            if(returnRGBWrapper===false){
                insert = (color);
            }else{
                insert = ('rgb('+color+')');
            }
        }else{
            color = $('.colorpicker_hex input').val();
            if(sellength==3 || sellength ==6){
                if(seltest){
                    insert = color;
                }else{
                    insert = '#'+color;
                }
            }else{
                insert = '#'+color;
            }
        }
        
        active.insert_text(insert);
        modal.unload();
        
    }
    
};