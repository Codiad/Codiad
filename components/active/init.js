/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See 
*  [root]/license.txt for more. This information must remain intact.
*/

$(function(){ active.init(); });

var active = {
    
    controller : 'components/active/controller.php',

    init : function(){
        // Focus
        $('#active-files a').live('click',function(){
            active.focus($(this).attr('data-path'));
        });
        // Remove
        $('#active-files a>span').live('click',function(e){
            e.stopPropagation();
            active.remove($(this).parent('a').attr('data-path'));
        });
        // Sortable
        $('#active-files').sortable({ 
            placeholder: 'active-sort-placeholder',
            tolerance: 'intersect',
            start: function(e, ui){
                ui.placeholder.height(ui.item.height());
            }
        });
        // Open saved-state active files on load
        $.get(active.controller+'?action=list',function(data){
            var list_response = jsend.parse(data);
            if(list_response!=null){
                $.each(list_response, function(index, value) { 
                    filemanager.open_file(value);
                });
                // Run resize command to fix render issues
                active.resize();
            }
        });
        // Run resize on window resize
        $(window).on('resize',function(){ active.resize(); });
    },
    
    //////////////////////////////////////////////////////////////////
    // Get active editor ID
    //////////////////////////////////////////////////////////////////
    
    get_id : function(){
        if($('.editor.active')){
            return $('.editor.active').attr('data-id');
        }else{
            return null;
        }
    },
    
    //////////////////////////////////////////////////////////////////
    // Get active editor path
    //////////////////////////////////////////////////////////////////
    
    get_path : function(){
        if($('.editor.active')){
            return $('.editor.active').attr('data-path');
        }else{
            return null;
        }
    },
    
    //////////////////////////////////////////////////////////////////
    // Check if opened by another user
    //////////////////////////////////////////////////////////////////
    
    check : function(path){
        $.get(active.controller+'?action=check&path='+path,function(data){
            var check_response = jsend.parse(data);
        });
    },

    //////////////////////////////////////////////////////////////////
    // Add newly opened file to list
    //////////////////////////////////////////////////////////////////

    add : function(path){
        $('#active-files').append('<li><a data-path="'+path+'"><span></span><div>'+path+'</div></a></li>');
        $.get(active.controller+'?action=add&path='+path);
        this.focus(path);
    },
    
    //////////////////////////////////////////////////////////////////
    // Focus on opened file
    //////////////////////////////////////////////////////////////////
    
    focus : function(path){
        if(editor.get_id(path)!=null){
            var id = editor.get_id(path);
            $('.editor').removeClass('active').hide();
            $('#editor'+id).addClass('active').show();
            editor.resize(id);
        }
        $('#active-files a').removeClass('active');
        $('#active-files a[data-path="'+path+'"]').addClass('active');
        active.check(path);
    },
    
    //////////////////////////////////////////////////////////////////
    // Mark changed
    //////////////////////////////////////////////////////////////////
    
    mark_changed : function(id){
        var path = this.get_path();
        $('#active-files a[data-path="'+path+'"]').addClass('changed');
    },
    
    //////////////////////////////////////////////////////////////////
    // Save active editor
    //////////////////////////////////////////////////////////////////
    
    save : function(){
        var path = this.get_path();
        var id = this.get_id();
        if(path && id){
            var content = editor.get_content(id);
            filemanager.save_file(path,content);
        }else{
            message.error('No Open Files to Save');
        }
        $('#active-files a[data-path="'+path+'"]').removeClass('changed');
    },
    
    //////////////////////////////////////////////////////////////////
    // Remove file
    //////////////////////////////////////////////////////////////////
    
    remove : function(path){
        if(editor.get_id(path)!=null){
            if($('#active-files a[data-path="'+path+'"]').hasClass('changed')){
                close_file = confirm('Close file without saving changes?');
            }else{
                close_file = true;
            }
            if(close_file){
                $('#editor'+editor.get_id(path)).remove();
                $('#active-files a[data-path="'+path+'"]').remove();
                $.get(active.controller+'?action=remove&path='+path);
            }
        }
    },
    
    //////////////////////////////////////////////////////////////////
    // Process rename
    //////////////////////////////////////////////////////////////////
    
    rename : function(old_path,new_path){
        $('#active-files a').each(function(){
            cur_path = $(this).attr('data-path');
            new_path = cur_path.replace(old_path,new_path);
            // Active file object
            $(this).attr('data-path',new_path).children('div').html(new_path);
            // Associated editor
            $('.editor[data-path="'+cur_path+'"]').attr('data-path',new_path);
        });
    },
    
    //////////////////////////////////////////////////////////////////
    // Resize
    //////////////////////////////////////////////////////////////////
    
    resize : function(){
        $('#active-files a').each(function(){
            cur_path = $(this).attr('data-path');
            var id = editor.get_id(cur_path);
            editor.resize(id);
        });
    },
    
    //////////////////////////////////////////////////////////////////
    // Open in Browser
    //////////////////////////////////////////////////////////////////
    
    open_in_browser : function(){
        var path = this.get_path();
        if(path){
            filemanager.open_in_browser(path);
        }else{
            message.error('No Open Files');
        }
    }

};