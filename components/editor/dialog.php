<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See 
*  [root]/license.txt for more. This information must remain intact.
*/

require_once('../../config.php');

//////////////////////////////////////////////////////////////////
// Verify Session or Key
//////////////////////////////////////////////////////////////////

checkSession();

?>
<form onsubmit="return false;">
<?php

switch($_GET['action']){

    //////////////////////////////////////////////////////////////////
    // Find & Replace
    //////////////////////////////////////////////////////////////////
    
    case 'search':
    $type = $_GET['type'];
    ?>
    <input type="hidden" name="id" value="<?php echo($_GET['id']); ?>">

    <label>Find:</label>
    <input type="text" name="find" autofocus="autofocus" autocomplete="off">
    
    <?php if($type=='replace'){ ?>

    <label>Replace:</label>
    <input type="text" name="replace">
    
    <?php } ?>

    <button class="btn-left" onclick="editor.search('find');return false;">Find</button><?php if($type=='replace'){ ?><button class="btn-mid" onclick="editor.search('replace');return false;">Replace</button><button class="btn-mid" onclick="editor.search('replace_all');return false;">Replace ALL</button><?php } ?><button class="btn-right" onclick="modal.unload(); return false;">Cancel</button>
    <?php
    break;
    
    //////////////////////////////////////////////////////////////////
    // Settings
    //////////////////////////////////////////////////////////////////
    
    case 'settings':
    ?>
    <label>Editor Settings</label>
    
    <table class="settings">
    
    <tr>
    
        <td width="1">Theme</td>
        <td>
        
        <select class="setting" data-setting="theme">
            <option value="clouds">Clouds</option>
            <option value="clouds_midnight">Clouds - Midnight</option>
            <option value="cobalt">Cobalt</option>
            <option value="crimson_editor">Crimson Editor</option>
            <option value="dawn">Dawn</option>
            <option value="dreamweaver">Dreamweaver</option>
            <option value="eclipse">Eclipse</option>
            <option value="github">GitHub</option>
            <option value="idle_fingers">Idle Fingers</option>
            <option value="merbivore">Merbivore</option>
            <option value="merbivore_soft">Merbivore Soft</option>
            <option value="mono_industrial">Mono Industrial</option>
            <option value="monokai">Monokai</option>
            <option value="pastel_on_dark">Pastel On Dark</option>
            <option value="solarized_dark">Solarized Dark</option>
            <option value="solarized_light">Solarized Light</option>
            <option value="textmate">Textmate</option>
            <option value="tomorrow">Tomorrow</option>
            <option value="tomorrow_night">Tomorrow Night</option>
            <option value="tomorrow_night_blue">Tomorrow Night Blue</option>
            <option value="tomorrow_night_bright">Tomorrow Night Bright</option>
            <option value="tomorrow_night_eighties">Tomorrow Night Eighties</option>
            <option value="twilight">Twilight</option>
            <option value="vibrant_ink">Vibrant Ink</option>
        </select>
        
        </td>
        
    </tr>
    <tr>
    
        <td>Font&nbsp;Size</td>
        <td>
        
        <select class="setting" data-setting="font-size">
            <option value="10px">10px</option>
            <option value="11px">11px</option>
            <option value="12px">12px</option>
            <option value="13px">13px</option>
            <option value="14px">14px</option>
            <option value="15px">15px</option>
            <option value="16px">16px</option>
            <option value="17px">17px</option>
            <option value="18px">18px</option>
        </select>
        
        </td>
        
    </tr>
    <tr>
    
        <td>Highlight&nbsp;Active&nbsp;Line</td>
        <td>
        
            <select class="setting" data-setting="highlight-line">
                <option value="true">Yes</option>
                <option value="false">No</option>
            </select>
            
        </td>
        
    </tr>
    <tr>
    
        <td>Indent&nbsp;Guides</td>
        <td>
        
        <select class="setting" data-setting="indent-guides">
            <option value="true">On</option>
            <option value="false">Off</option>
        </select>
        
        </td>
        
    </tr>
    <tr>
    
        <td>Print&nbsp;Margin</td>
        <td>
        
        <select class="setting" data-setting="print-margin">
            <option value="true">Show</option>
            <option value="false">Hide</option>
        </select>
        
        </td>
        
    </tr>
    <tr>
    
        <td>Wrapping</td>
        <td>
        
        <select class="setting" data-setting="wrap-mode">
            <option value="false">No Wrapping</option>
            <option value="true">Wrap Lines</option>
        </select>
        
        </td>
        
    </tr>
    </table>
    
    <button onclick="modal.unload(); return false;">Close</button
    
    <?php
    
}

?>
</form>
<script>

var editor_settings = {

    init : function(){
        this.load_values();
        this.change_listener();
    },
    
    load_values : function(){
        $('select.setting').each(function(){
            editor.get_settings();
            switch($(this).data('setting')){
                case 'theme':
                    $(this).children('option[value="'+editor.settings.theme+'"]').prop('selected',true);
                    break;
                case 'font-size':
                    $(this).children('option[value="'+editor.settings.font_size+'"]').prop('selected',true);
                    break;
                case 'highlight-line':
                    $(this).children('option[value="'+editor.settings.highlight_line+'"]').prop('selected',true);
                    break;
                case 'indent-guides':
                    $(this).children('option[value="'+editor.settings.indent_guides+'"]').prop('selected',true);
                    break;
                case 'print-margin':
                    $(this).children('option[value="'+editor.settings.print_margin+'"]').prop('selected',true);
                    break;
                case 'wrap-mode':
                    $(this).children('option[value="'+editor.settings.wrap_mode+'"]').prop('selected',true);
                    break;
            }
        });
    },
    
    
    change_listener : function(){
        $('select.setting').change(function(){
            var setting = $(this).data('setting');
            var val = $(this).val();
            if(val===null){
                message.alert('You Must Choose A Value');
            }else{
                switch($(this).data('setting')){
                    case 'theme':
                        editor.set_theme(val);
                        break;
                    case 'font-size':
                        editor.set_font_size(val);
                        break;
                    case 'highlight-line':
                        var bool_val = (val == "true");
                        editor.set_highlight_line(bool_val);
                        break;
                    case 'indent-guides':
                        var bool_val = (val == "true");
                        editor.set_indent_guides(bool_val);
                        break;
                    case 'print-margin':
                        var bool_val = (val == "true");
                        editor.set_print_margin(bool_val);
                        break;
                    case 'wrap-mode':
                        var bool_val = (val == "true");
                        editor.set_wrap_mode(bool_val);
                        break;
                }
            }
        });
    }

};

$(function(){ 
    $('input[name="find"]').val(active.get_selected_text()); 
    editor_settings.init();   
});

</script>