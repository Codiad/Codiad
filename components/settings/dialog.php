<?php

    /*
    *  Copyright (c) Codiad & Andr3as, distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */

    require_once('../../common.php');

    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////

    checkSession();

    if (!isset($_GET['action'])) {
        $_GET['action'] = "settings";
    }

    switch($_GET['action']) {
        case "settings":
?>
            <div class="settings-view">
                <div class="config-menu">
                    <label><?php i18n("Settings"); ?></label>
                    <div class="panels-components">
                        <ul>
                            <li name="editor-settings" data-file="components/settings/settings.editor.php" data-name="editor" class="active">
                                <a><span class="icon-home bigger-icon"></span><?php i18n("Editor"); ?></a>
                            </li>
                            <li name="system-settings" data-file="components/settings/settings.system.php" data-name="system">
                                <a><span class="icon-doc-text bigger-icon"></span><?php i18n("System"); ?></a>
                            </li>
                            <?php
                                if (COMMON::checkAccess()) {
                                    ?>
                                    <li name="extension-settings" data-file="components/fileext_textmode/dialog.php?action=fileextension_textmode_form" data-name="fileext_textmode">
                                        <a><span class="icon-pencil bigger-icon"></span><?php i18n("Extensions"); ?></a>
                                    </li>
                                    <?php
                                }
                            ?>
                        </ul>
                    </div>
                    <hr>
                    <div class="panels-plugins">
                        <?php
                            $plugins = Common::readDirectory(PLUGINS);
                            
                            foreach($plugins as $plugin){
                                if(file_exists(PLUGINS . "/" . $plugin . "/plugin.json")){
                                    $datas = json_decode(file_get_contents(PLUGINS . "/" . $plugin . "/plugin.json"), true);
                                    foreach($datas as $data) {
                                        if (isset($data['config'])) {
                                            foreach($data['config'] as $config) {
                                                if(isset($config['file']) && isset($config['icon']) && isset($config['title'])) {
                                                    echo('<li data-file="plugins/' . $plugin . '/' .$config['file'].'" data-name="'. $data['name'] .'">
                                                        <a><span class="' . $config['icon'] . ' bigger-icon"></span>' . $config['title'] . '</a></li>');
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        ?>
                    </div>
                </div>
                <div class="panels">
                    <div class="panel active" data-file="components/settings/settings.editor.php">
                        <?php include('settings.editor.php'); ?>
                    </div>
                </div>
            </div>
            <button class="btn-right" onclick="save(); return false;"><?php i18n("Save"); ?></button>
            <button class="btn-right" onclick="codiad.modal.unload(); return false;"><?php i18n("Close"); ?></button>
            <script>
                $('.settings-view .config-menu li').click(function(){
                    codiad.settings._showTab($(this).attr('data-file'));
                });
            
                function save() {
                    $('.setting').each(function(){
                        var setting = $(this).data('setting');
                        var val     = $(this).val();
                        if(val===null){
                            codiad.message.alert(i18n("You Must Choose A Value"));
                            return;
                        }else{
                            switch(setting){
                                case 'codiad.editor.theme':
                                    codiad.editor.setTheme(val);
                                    break;
                                case 'codiad.editor.fontSize':
                                    codiad.editor.setFontSize(val);
                                    break;
                                case 'codiad.editor.highlightLine':
                                    var bool_val = (val == "true");
                                    codiad.editor.setHighlightLine(bool_val);
                                    break;
                                case 'codiad.editor.indentGuides':
                                    var bool_val = (val == "true");
                                    codiad.editor.setIndentGuides(bool_val);
                                    break;
                                case 'codiad.editor.printMargin':
                                    var bool_val = (val == "true");
                                    codiad.editor.setPrintMargin(bool_val);
                                    break;
                                case 'codiad.editor.printMarginColumn':
                                    var int_val = (!isNaN(parseFloat(val)) && isFinite(val))
                                        ? parseInt(val, 10)
                                        : 80;
                                    codiad.editor.setPrintMarginColumn(int_val);
                                    break;
                                case 'codiad.editor.wrapMode':
                                    var bool_val = (val == "true");
                                    codiad.editor.setWrapMode(bool_val);
                                    break;
                                case 'codiad.editor.rightSidebarTrigger':
                                    var bool_val = (val == "true");
                                    codiad.editor.setRightSidebarTrigger(bool_val);
                                    break;
                                case 'codiad.editor.fileManagerTrigger':
                                    var bool_val = (val == "true");
                                    codiad.editor.setFileManagerTrigger(bool_val);
                                    break;    
                                case 'codiad.editor.persistentModal':
                                    var bool_val = (val == "true");
                                    codiad.editor.setPersistentModal(bool_val);
                                    break;      
                                case "codiad.editor.softTabs":
                                    var bool_val = (val == "true");
                                    codiad.editor.setSoftTabs(bool_val);
                                break;
                                case "codiad.editor.tabSize":
                                    codiad.editor.setTabSize(val);
                                break;
                            }
                        }
                        localStorage.setItem(setting, val);
                    });
                    /* Notify listeners */
                    amplify.publish('settings.dialog.save',{});
                    codiad.modal.unload();
                    codiad.settings.save();
                }
            </script>
<?php
            break;
        case "iframe":
?>
            <script>
                /*
                 *  Storage Event:
                 *  Note: Event fires only if change was made in different window and not in this one
                 *  Details: http://dev.w3.org/html5/webstorage/#dom-localstorage
                 */
                window.addEventListener('storage', function(e){
                    if (/^codiad/.test(e.key)) {
                        var obj = { key: e.key, oldValue: e.oldValue, newValue: e.newValue };
                        /* Notify listeners */
                        window.parent.amplify.publish('settings.changed', obj);
                    }
                }, false);
            </script>
<?php
            break;
        default:
            break;
    }
?>
