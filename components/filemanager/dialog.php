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
    <label><span class="icon-pencil"></span><?php echo(ucfirst($_GET['type'])); ?> Name</label>    
    <input type="text" name="object_name" autofocus="autofocus" autocomplete="off">  
    <button class="btn-left">Create</button><button class="btn-right" onclick="codiad.modal.unload(); return false;">Cancel</button>
    <?php
    break;
    
    //////////////////////////////////////////////////////////////////
    // Rename
    //////////////////////////////////////////////////////////////////
    case 'rename':
    ?>
    <input type="hidden" name="path" value="<?php echo($_GET['path']); ?>">
    <input type="hidden" name="type" value="<?php echo($_GET['type']); ?>"> 
    <label><span class="icon-pencil"></span>Rename <?php echo(ucfirst($_GET['type'])); ?></label>    
    <input type="text" name="object_name" autofocus="autofocus" autocomplete="off" value="<?php echo($_GET['short_name']); ?>">  
    <button class="btn-left">Rename</button><button class="btn-right" onclick="codiad.modal.unload(); return false;">Cancel</button>
    <?php
    break;
    
    //////////////////////////////////////////////////////////////////
    // Delete
    //////////////////////////////////////////////////////////////////
    case 'delete':
    ?>
    <input type="hidden" name="path" value="<?php echo($_GET['path']); ?>"> 
    <label>Are you sure you wish to delete the following:</label>
    <pre><?php if($_GET['path'][0] != '/') { echo '/'; }; echo($_GET['path']); ?></pre>
    <button class="btn-left">Delete</button><button class="btn-right" onclick="codiad.modal.unload();return false;">Cancel</button>
    <?php
    break;
    
    //////////////////////////////////////////////////////////////////
    // Preview
    //////////////////////////////////////////////////////////////////
    case 'preview':
    ?> 
    <label>Inline Preview</label>
    <div><br><br><img src="<?php echo($_GET['path']); ?>"><br><br></div>
    <button class="btn-right" onclick="codiad.modal.unload();return false;">Close</button>
    <?php
    break;
    
    //////////////////////////////////////////////////////////////////
    // Overwrite
    //////////////////////////////////////////////////////////////////
    case 'overwrite':
    ?>
    <input type="hidden" name="path" value="<?php echo($_GET['path']); ?>">    
    <label>Would you like to overwrite or duplicate the following:</label>
    <pre><?php if($_GET['path'][0] != '/') { echo '/'; }; echo($_GET['path']); ?></pre>
    <select name="or_action">
        <option value="0">Overwrite Original</option>
        <option value="1">Create Duplicate</option>
    </select>
    <button class="btn-left">Continue</button><button class="btn-right" onclick="codiad.modal.unload();return false;">Cancel</button>
    <?php
    break;
    
    //////////////////////////////////////////////////////////////////
    // Search
    //////////////////////////////////////////////////////////////////
    case 'search':
    ?>
    <input type="hidden" name="path" value="<?php echo($_GET['path']); ?>">    
    <label>Search Files:</label>
    <table>
        <tr>
            <td width="75%">
                   <input type="text" name="search_string" autofocus="autofocus">
            </td>
            <td>
                <select name="search_type">
                    <option value="0">Current Project</option>
                    <?php if(checkAccess()) { ?>
                    <option value="1">All Projects</option>
                    <? } ?>
                </select>
            </td>
        </tr>
    </table>
    <pre id="filemanager-search-results"></pre>
    <div id="filemanager-search-processing"></div>
    <button class="btn-left">Search</button><button class="btn-right" onclick="codiad.modal.unload();return false;">Cancel</button>
    <?php
    break;
    
}

?>
</form>
    
    
