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
        if(e.keyCode == key && isCtrl) {
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
        $.ctrl('83', function(){ active.save(); });
        
        // Open in browser [CTRL+O] //////////////////////////////////
        $.ctrl('79', function(){ active.open_in_browser(); });
        
        // Find [CTRL+F] /////////////////////////////////////////////
        $.ctrl('70', function(){ editor.open_search('find'); });
        
        // Replace [CTRL+R] //////////////////////////////////////////
        $.ctrl('82', function(){ editor.open_search('replace'); });
        
        // Active List Up ////////////////////////////////////////////
        $.ctrl('38', function(){ active.move('up'); });
        
        // Active List Down //////////////////////////////////////////
        $.ctrl('40', function(){ active.move('down'); });
    
    }

};