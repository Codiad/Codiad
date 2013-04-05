<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See 
*  [root]/license.txt for more. This information must remain intact.
*/

require_once('../../common.php');

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

    <label><?php i18n("Find:"); ?></label>
    <input type="text" name="find" autofocus="autofocus" autocomplete="off">
    
    <?php if($type=='replace'){ ?>

    <label><?php i18n("Replace:"); ?></label>
    <input type="text" name="replace">
    
    <?php } ?>

    <button class="btn-left" onclick="codiad.editor.search('find');return false;"><?php i18n("Find"); ?></button>
    <?php if($type=='replace'){ ?>
        <button class="btn-mid" onclick="codiad.editor.search('replace');return false;"><?php i18n("Replace"); ?></button>
        <button class="btn-mid" onclick="codiad.editor.search('replaceAll');return false;"><?php i18n("Replace ALL"); ?></button>
    <?php } ?>
    <button class="btn-right" onclick="codiad.modal.unload(); return false;"><?php i18n("Cancel"); ?></button>
    <?php
    break;
    
    //////////////////////////////////////////////////////////////////
    // Settings
    //////////////////////////////////////////////////////////////////
    
    case 'settings':
    ?>
    <label><?php i18n("Editor Settings"); ?></label>
    
    <table class="settings">
    
    <tr>
    
        <td width="1"><?php i18n("Theme"); ?></td>
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
    
        <td><?php i18n("Font Size"); ?></td>
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
    
        <td><?php i18n("Highlight Active Line"); ?></td>
        <td>
        
            <select class="setting" data-setting="highlight-line">
                <option value="true"><?php i18n("Yes"); ?></option>
                <option value="false"><?php i18n("No"); ?></option>
            </select>
            
        </td>
        
    </tr>
    <tr>
    
        <td><?php i18n("Indent Guides"); ?></td>
        <td>
        
        <select class="setting" data-setting="indent-guides">
            <option value="true"><?php i18n("On"); ?></option>
            <option value="false"><?php i18n("Off"); ?></option>
        </select>
        
        </td>
        
    </tr>
    <tr>
    
        <td><?php i18n("Print Margin"); ?></td>
        <td>
        
        <select class="setting" data-setting="print-margin">
            <option value="true"><?php i18n("Show"); ?></option>
            <option value="false"><?php i18n("Hide"); ?></option>
        </select>
        
        </td>
        
    </tr>
    <tr>
    
        <td><?php i18n("Wrap Lines"); ?></td>
        <td>
        
        <select class="setting" data-setting="wrap-mode">
            <option value="false"><?php i18n("No wrap"); ?></option>
            <option value="true"><?php i18n("Wrap Lines"); ?></option>
        </select>
        
        </td>
        
    </tr>
    <tr>
    
        <td><?php i18n("Right Sidebar Trigger"); ?></td>
        <td>
        
        <select class="setting" data-setting="right-sidebar-trigger">
            <option value="false"><?php i18n("Hover"); ?></option>
            <option value="true"><?php i18n("Click"); ?></option>
        </select>
        
        </td>
        
    </tr>
    </table>
    
    <button onclick="codiad.modal.unload(); return false;"><?php i18n("Close"); ?></button>
    
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
            codiad.editor.getSettings();
            switch($(this).data('setting')){
                case 'theme':
                    $(this).children('option[value="'+codiad.editor.settings.theme+'"]').prop('selected',true);
                    break;
                case 'font-size':
                    $(this).children('option[value="'+codiad.editor.settings.fontSize+'"]').prop('selected',true);
                    break;
                case 'highlight-line':
                    $(this).children('option[value="'+codiad.editor.settings.highlightLine+'"]').prop('selected',true);
                    break;
                case 'indent-guides':
                    $(this).children('option[value="'+codiad.editor.settings.indentGuides+'"]').prop('selected',true);
                    break;
                case 'print-margin':
                    $(this).children('option[value="'+codiad.editor.settings.printMargin+'"]').prop('selected',true);
                    break;
                case 'wrap-mode':
                    $(this).children('option[value="'+codiad.editor.settings.wrapMode+'"]').prop('selected',true);
                    break;
                case 'right-sidebar-trigger':
                    $(this).children('option[value="'+codiad.editor.settings.rightSidebarTrigger+'"]').prop('selected',true);
                    break;
            }
        });
    },
    
    
    change_listener : function(){
        $('select.setting').change(function(){
            var setting = $(this).data('setting');
            var val = $(this).val();
            if(val===null){
                codiad.message.alert(i18n("You Must Choose A Value"));
            }else{
                switch($(this).data('setting')){
                    case 'theme':
                        codiad.editor.setTheme(val);
                        break;
                    case 'font-size':
                        codiad.editor.setFontSize(val);
                        break;
                    case 'highlight-line':
                        var bool_val = (val == "true");
                        codiad.editor.setHighlightLine(bool_val);
                        break;
                    case 'indent-guides':
                        var bool_val = (val == "true");
                        codiad.editor.setIndentGuides(bool_val);
                        break;
                    case 'print-margin':
                        var bool_val = (val == "true");
                        codiad.editor.setPrintMargin(bool_val);
                        break;
                    case 'wrap-mode':
                        var bool_val = (val == "true");
                        codiad.editor.setWrapMode(bool_val);
                        break;
                    case 'right-sidebar-trigger':
                        var bool_val = (val == "true");
                        codiad.editor.setRightSidebarTrigger(bool_val);
                        break;
                }
            }
        });
    }

};

$(function(){
    <?php if($_GET['action']=='search'){ ?>
    $('input[name="find"]').val(codiad.active.getSelectedText());
    <?php } ?>
    editor_settings.init();   
});

</script>