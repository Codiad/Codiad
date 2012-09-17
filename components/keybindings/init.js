/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See 
*  [root]/license.txt for more. This information must remain intact.
*/

//////////////////////////////////////////////////////////////////////
// CTRL Key Bind
//////////////////////////////////////////////////////////////////////

$.ctrl = function(key, callback, args) {
    var isCtrl = false;
    $(document).keydown(function(e) {
        if(!args) args=[];
        if(e.ctrlKey) isCtrl = true;
        if(e.keyCode == key.charCodeAt(0) && isCtrl) {
            callback.apply(this, args);
            return false;
        }
    }).keyup(function(e) {
        if(e.ctrlKey) isCtrl = false;
    });        
};

$(function(){ keybindings.init(); });

//////////////////////////////////////////////////////////////////////
// Bindings
//////////////////////////////////////////////////////////////////////

var keybindings = {

    init : function(){
    
        // Close Modals //////////////////////////////////////////////
        $(document).keyup(function(e){ if(e.keyCode == 27){ modal.unload(); } });
   
        // Save [CTRL+S] /////////////////////////////////////////////
        $.ctrl('S', function(){ active.save(); });
        
        // Open in browser [CTRL+O] //////////////////////////////////
        $.ctrl('O', function(){ active.open_in_browser(); });
        
        // Find [CTRL+F] /////////////////////////////////////////////
        $.ctrl('f', function(){ editor.open_search('find'); });
        
        // Replace [CTRL+R] /////////////////////////////////////////////
        $.ctrl('f', function(){ editor.open_search('replace'); });
    
    }

}