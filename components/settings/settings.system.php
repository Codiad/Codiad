<?php 
    require_once('../../common.php');
?>
<label><span class="icon-doc-text big-icon"></span><?php i18n("System Settings"); ?></label>
<hr>
<label></label>
<table class="settings">
    <tr>
    
        <td><?php i18n("Right Sidebar Trigger"); ?></td>
        <td>
            <select class="setting" data-setting="codiad.editor.rightSidebarTrigger">
                <option value="false"><?php i18n("Hover") ?></option>
                <option value="true"><?php i18n("Click") ?></option>
            </select>
        </td>

    </tr>
    
    <tr>
    
        <td><?php i18n("Filemanager Trigger"); ?></td>
        <td>
            <select class="setting" data-setting="codiad.editor.filemanager-trigger">
                <option value="false"><?php i18n("Double Click") ?></option>
                <option value="true"><?php i18n("Single Click") ?></option>
            </select>
        </td>

    </tr>
    
    <tr>
    
        <td><?php i18n("Sync system settings on all devices"); ?></td>
        <td>
            <select class="setting" data-setting="codiad.settings.system.sync">
                <option value="true"><?php i18n("Yes") ?></option>
                <option value="false"><?php i18n("No") ?></option>
            </select>
        </td>

    </tr>

    <tr>
    
        <td><?php i18n("Sync plugin settings on all devices"); ?></td>
        <td>
            <select class="setting" data-setting="codiad.settings.plugin.sync">
                <option value="true"><?php i18n("Yes") ?></option>
                <option value="false"><?php i18n("No") ?></option>
            </select>
        </td>

    </tr>
</table>
<script>
    $(function(){
        $('select.setting').each(function(){
            codiad.editor.getSettings();
            switch($(this).data('setting')){
                case 'codiad.settings.system.sync':
                    $(this).children('option[value="'+localStorage.getItem('codiad.settings.system.sync')+'"]').prop('selected',true);
                case 'codiad.settings.plugin.sync':
                    $(this).children('option[value="'+localStorage.getItem('codiad.settings.plugin.sync')+'"]').prop('selected',true);
                case 'codiad.editor.rightSidebarTrigger':
                    $(this).children('option[value="'+codiad.editor.settings.rightSidebarTrigger+'"]').prop('selected',true);
                    break;
                case 'codiad.editor.filemanager-trigger':
                    $(this).children('option[value="'+codiad.editor.settings.fileManagerTrigger+'"]').prop('selected',true);
                    break;
            }
        });
    });
</script>