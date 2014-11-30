<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See 
*  [root]/license.txt for more. This information must remain intact.
*/

require_once('../../common.php');
require_once('class.filemanager.php');
//////////////////////////////////////////////////////////////////
// Verify Session or Key
//////////////////////////////////////////////////////////////////

checkSession();

?>
<form>
<?php

switch($_GET['action']){

    //////////////////////////////////////////////////////////////////
    // Create
    //////////////////////////////////////////////////////////////////
    case 'create':
    ?>
    <input type="hidden" name="path" value="<?php echo($_GET['path']); ?>">
    <input type="hidden" name="type" value="<?php echo($_GET['type']); ?>">
    <label><span class="icon-pencil"></span><?php echo i18n((ucfirst($_GET['type']))); ?></label>
    <input type="text" name="object_name" autofocus="autofocus" autocomplete="off">
    <button class="btn-left"><?php i18n("Create"); ?></button>
    <button class="btn-right" onclick="codiad.modal.unload(); return false;"><?php i18n("Cancel"); ?></button>
    <?php
    break;

    //////////////////////////////////////////////////////////////////
    // Rename
    //////////////////////////////////////////////////////////////////
    case 'rename':
    ?>
    <input type="hidden" name="path" value="<?php echo($_GET['path']); ?>">
    <input type="hidden" name="type" value="<?php echo($_GET['type']); ?>">
    <label><span class="icon-pencil"></span> <?php i18n("Rename"); ?> <?php echo i18n((ucfirst($_GET['type']))); ?></label>
    <input type="text" name="object_name" autofocus="autofocus" autocomplete="off" value="<?php echo($_GET['short_name']); ?>">
    <button class="btn-left"><?php i18n("Rename"); ?></button>
	<button class="btn-right" onclick="codiad.modal.unload(); return false;"><?php i18n("Cancel"); ?></button>
    <?php
    break;

    //////////////////////////////////////////////////////////////////
    // Delete
    //////////////////////////////////////////////////////////////////
    case 'delete':
    ?>
    <input type="hidden" name="path" value="<?php echo($_GET['path']); ?>">
    <label><?php i18n("Are you sure you wish to delete the following:"); ?></label>
    <pre><?php if(!FileManager::isAbsPath($_GET['path'])) { echo '/'; }; echo($_GET['path']); ?></pre>
    <button class="btn-left"><?php i18n("Delete"); ?></button>
	<button class="btn-right" onclick="codiad.modal.unload();return false;"><?php i18n("Cancel"); ?></button>
    <?php
    break;

    //////////////////////////////////////////////////////////////////
    // Preview
    //////////////////////////////////////////////////////////////////
    case 'preview':
    ?>
    <label><?php i18n("Inline Preview"); ?></label>
    <div><br><br><img src="<?php echo(str_replace(BASE_PATH . "/", "", WORKSPACE) . "/" . $_GET['path']); ?>"><br><br></div>
    <button class="btn-right" onclick="codiad.modal.unload();return false;"><?php i18n("Close"); ?></button>
    <?php
    break;

    //////////////////////////////////////////////////////////////////
    // Overwrite
    //////////////////////////////////////////////////////////////////
    case 'overwrite':
    ?>
    <input type="hidden" name="path" value="<?php echo($_GET['path']); ?>">
    <label><?php i18n("Would you like to overwrite or duplicate the following:"); ?></label>
    <pre><?php if(!FileManager::isAbsPath($_GET['path'])) { echo '/'; }; echo($_GET['path']); ?></pre>
    <select name="or_action">
        <option value="0"><?php i18n("Overwrite Original"); ?></option>
        <option value="1"><?php i18n("Create Duplicate"); ?></option>
    </select>
    <button class="btn-left"><?php i18n("Continue"); ?></button>
	<button class="btn-right" onclick="codiad.modal.unload();return false;"><?php i18n("Cancel"); ?></button>
    <?php
    break;

    //////////////////////////////////////////////////////////////////
    // Search
    //////////////////////////////////////////////////////////////////
    case 'search':
    ?>
    <input type="hidden" name="path" value="<?php echo($_GET['path']); ?>">
    <table class="file-search-table">
        <tr>
            <td width="65%">
               <label><?php i18n("Search Files:"); ?></label>
               <input type="text" name="search_string" autofocus="autofocus">
            </td>
            <td width="5%">&nbsp;&nbsp;</td>
            <td>
                <label><?php i18n("In:"); ?></label>
                <select name="search_type">
                    <option value="0"><?php i18n("Current Project"); ?></option>
                    <?php if(checkAccess()) { ?>
                    <option value="1"><?php i18n("Workspace Projects"); ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="3">
               <label><?php i18n("File Type:"); ?></label>
               <input type="text" name="search_file_type" placeholder="<?php i18n("space seperated file types eg: js c php"); ?>">
            </td>
        </tr>
    </table>
    <pre id="filemanager-search-results"></pre>
    <div id="filemanager-search-processing"></div>
    <button class="btn-left"><?php i18n("Search"); ?></button>
	<button class="btn-right" onclick="codiad.modal.unload();return false;"><?php i18n("Cancel"); ?></button>
    <?php
    break;

}

?>
</form>
