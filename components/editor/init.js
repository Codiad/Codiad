/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See 
*  [root]/license.txt for more. This information must remain intact.
*/

editor_instance = {}; // Instances array
editor_modes = {}; // Loaded modes
editor_count = 0; // Counter for incrementing instances

var editor = {
    
    //////////////////////////////////////////////////////////////////
    // Open new editor instance
    //////////////////////////////////////////////////////////////////

    open : function(path,content){
    
        if(this.get_id(path)==null){
    
            // Hide all other editors
            $('.editor').hide();
        
            editor_count++;
            
            $('#editor-region').append('<div class="editor" id="editor'+editor_count+'" data-id="'+editor_count+'" data-path="'+path+'"></div>');
    
            editor_instance[editor_count] = ace.edit('editor'+editor_count);
            
            var ext = filemanager.get_extension(path);
            var mode = this.select_mode(ext);
            
            this.set_mode(mode,editor_instance[editor_count]);
            this.set_theme('twilight',editor_instance[editor_count]);
            this.set_content(content,editor_instance[editor_count]);
            this.set_print_margin(false,editor_instance[editor_count]);
            this.set_highlight_line(true,editor_instance[editor_count]);
            this.set_indent_guides(true,editor_instance[editor_count]);
            this.change_listener(editor_instance[editor_count],editor_count);
            
            // Add to active list
            active.add(path);
            
        }else{
            active.focus(path);
        }
                
    },
    
    //////////////////////////////////////////////////////////////////
    // Get ID of editor from path
    //////////////////////////////////////////////////////////////////
    
    get_id : function(path){
        if($('.editor[data-path="'+path+'"]').length){
            return $('.editor[data-path="'+path+'"]').attr('data-id');
        }else{
            return null;
        }
    },
    
    //////////////////////////////////////////////////////////////////
    // Select mode from extension
    //////////////////////////////////////////////////////////////////
    
    select_mode : function(e){
        switch(e){
            case 'html': case 'htm': case 'tpl':
                return 'html'; break;
            case 'js':
                return 'javascript'; break;
            case 'css':
                return 'css'; break;
            case 'scss': case 'sass':
                return 'scss'; break;
            case 'less':
                return 'less'; break;
            case 'php': case 'php5':
                return 'php'; break;
            case 'json':
                return 'json'; break;
            case 'xml':
                return 'xml'; break;
            case 'sql':
                return 'sql'; break;
            case 'md':
                return 'markdown'; break;
            default:
                return 'text';
        }
    },
    
    //////////////////////////////////////////////////////////////////
    // Set editor mode/language
    //////////////////////////////////////////////////////////////////
    
    set_mode : function(m,i){
        if(!editor_modes[m]){ // Check if mode is already loaded
            $.loadScript("components/editor/ace-editor/mode-"+m+".js", function(){
                editor_modes[m] = true; // Mark to not load again
                var EditorMode = require("ace/mode/"+m).Mode;
                i.getSession().setMode(new EditorMode());
            },true);
        }else{
            var EditorMode = require("ace/mode/"+m).Mode;
            i.getSession().setMode(new EditorMode());
        }
    },
    
    //////////////////////////////////////////////////////////////////
    // Set editor theme
    //////////////////////////////////////////////////////////////////
    
    set_theme : function(t,i){
        i.setTheme("ace/theme/"+t);
    },
    
    //////////////////////////////////////////////////////////////////
    // Set content of editor
    //////////////////////////////////////////////////////////////////
    
    set_content : function(c,i){
        i.getSession().setValue(c);
    },
    
    //////////////////////////////////////////////////////////////////
    // Highlight active line
    //////////////////////////////////////////////////////////////////
    
    set_highlight_line : function(h,i){
        i.setHighlightActiveLine(h)
    },
    
    //////////////////////////////////////////////////////////////////
    // Show/Hide print margin indicator
    //////////////////////////////////////////////////////////////////
    
    set_print_margin : function(p,i){
        i.setShowPrintMargin(p);
    },
    
    //////////////////////////////////////////////////////////////////
    // Show/Hide indent guides
    //////////////////////////////////////////////////////////////////
    
    set_indent_guides : function(g,i){
        i.setDisplayIndentGuides(g);
    },
    
    //////////////////////////////////////////////////////////////////
    // Code Folding
    //////////////////////////////////////////////////////////////////
    
    set_code_folding : function(f,i){
        i.setFoldStyle(f);
    },
    
    //////////////////////////////////////////////////////////////////
    // Get content from editor by ID
    //////////////////////////////////////////////////////////////////
    
    get_content : function(id){
        return editor_instance[id].getSession().getValue();
    },
    
    //////////////////////////////////////////////////////////////////
    // Resize
    //////////////////////////////////////////////////////////////////
    
    resize : function(id){
        editor_instance[id].resize();
    },
    
    //////////////////////////////////////////////////////////////////
    // Change Listener
    //////////////////////////////////////////////////////////////////
    
    change_listener : function(i){
        i.on('change',function(){
            active.mark_changed();
        });
    },
    
    //////////////////////////////////////////////////////////////////
    // Get Selected Text
    //////////////////////////////////////////////////////////////////
    
    get_selected_text : function(id){
        return editor_instance[id].getCopyText();
    },
    
    //////////////////////////////////////////////////////////////////
    // Insert text
    //////////////////////////////////////////////////////////////////
    
    insert_text : function(id,val){
        editor_instance[id].insert(val);
    }

}