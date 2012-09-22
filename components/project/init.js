/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See 
*  [root]/license.txt for more. This information must remain intact.
*/

$(function(){ project.init(); });

var project = {

    controller : 'components/project/controller.php',
    dialog : 'components/project/dialog.php',

    init : function(){
        this.load_current();
    },
    
    //////////////////////////////////////////////////////////////////
    // Get Current Project
    //////////////////////////////////////////////////////////////////
    
    load_current : function(){
        $.get(project.controller+'?action=get_current',function(data){
            var project_info = jsend.parse(data);
            if(project_info!='error'){
                $('#file-manager').html('').append('<ul><li><a id="project-root" data-type="root" class="directory" data-path="/'+project_info.path+'">'+project_info.name+'</a></li></ul>');
                filemanager.index('/'+project_info.path);
                user.project(project_info.path);
                message.success('Project Loaded');
            }
        });    
    },
    
    //////////////////////////////////////////////////////////////////
    // Open Project
    //////////////////////////////////////////////////////////////////
    
    open : function(path){
        $.get(project.controller+'?action=open&path='+path,function(data){
            var project_info = jsend.parse(data);
            if(project_info!='error'){
                project.load_current();
                modal.unload();
                user.project(path);
            }
        });
    },
    
    //////////////////////////////////////////////////////////////////
    // Open the project manager dialog
    //////////////////////////////////////////////////////////////////
    
    list : function(){
        $('#modal-content form').die('submit'); // Prevent form bubbling
        modal.load(500,project.dialog+'?action=list');
    },
    
    //////////////////////////////////////////////////////////////////
    // Create Project
    //////////////////////////////////////////////////////////////////
    
    create : function(){
        modal.load(500,project.dialog+'?action=create');
        $('#modal-content form').live('submit',function(e){
            e.preventDefault();
            var project_name = $('#modal-content form input[name="project_name"]').val();
            $.get(project.controller+'?action=create&project_name='+project_name,function(data){
                create_response = jsend.parse(data);
                if(create_response!='error'){
                    project.open(create_response.path);
                    modal.unload();
                }
            });
        });
    },
    
    //////////////////////////////////////////////////////////////////
    // Delete Project
    //////////////////////////////////////////////////////////////////
    
    delete : function(name,path){
        modal.load(500,project.dialog+'?action=delete&name='+escape(name)+'&path='+escape(path));
        $('#modal-content form').live('submit',function(e){
            e.preventDefault();
            var project_path = $('#modal-content form input[name="project_path"]').val();
            $.get(project.controller+'?action=delete&project_path='+project_path,function(data){
                delete_response = jsend.parse(data);
                if(delete_response!='error'){
                    message.success('Project Deleted');
                    $.get(filemanager.controller+'?action=delete&path='+project_path);
                    project.list();
                    // Remove any active files that may be open
                    $('#active-files a').each(function(){
                        var cur_path = $(this).attr('data-path');
                        if(cur_path.indexOf(project_path)==0){
                            active.remove(cur_path);
                        }
                    });
                }
            });
        });
    },
    
    //////////////////////////////////////////////////////////////////
    // Get Current (Path)
    //////////////////////////////////////////////////////////////////
    
    get_current : function(){
        $.get(project.controller+'?action=current',function(data){
            current_response = jsend.parse(data);
            if(current_response!='error'){
                return current_response;
            }
        });
    }

};
