<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See 
*  [root]/license.txt for more. This information must remain intact.
*/

require_once('../../config.php');
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
    <label><span class="icon">&amp;</span><?php echo(ucfirst($_GET['type'])); ?> Name</label>    
    <input type="text" name="object_name" autofocus="autofocus" autocomplete="off">  
    <button class="btn-left">Create</button><button class="btn-right" onclick="modal.unload(); return false;">Cancel</button>
    <?php
    break;
    
    //////////////////////////////////////////////////////////////////
    // Rename
    //////////////////////////////////////////////////////////////////
    case 'rename':
    ?>
    <input type="hidden" name="path" value="<?php echo($_GET['path']); ?>">
    <input type="hidden" name="type" value="<?php echo($_GET['type']); ?>"> 
    <label><span class="icon">&amp;</span>Rename <?php echo(ucfirst($_GET['type'])); ?></label>    
    <input type="text" name="object_name" autofocus="autofocus" autocomplete="off" value="<?php echo($_GET['short_name']); ?>">  
    <button class="btn-left">Rename</button><button class="btn-right" onclick="modal.unload(); return false;">Cancel</button>
    <?php
    break;
    
    //////////////////////////////////////////////////////////////////
    // Delete
    //////////////////////////////////////////////////////////////////
    case 'delete':
    ?>
    <input type="hidden" name="path" value="<?php echo($_GET['path']); ?>"> 
    <label>Are you sure you wish to delete the following:</label>
    <pre><?php echo($_GET['path']); ?></pre>
    <button class="btn-left">Delete</button><button class="btn-right" onclick="modal.unload();return false;">Cancel</button>
    <?php
    break;
    
    //////////////////////////////////////////////////////////////////
    // Overwrite
    //////////////////////////////////////////////////////////////////
    case 'overwrite':
    ?>
    <input type="hidden" name="path" value="<?php echo($_GET['path']); ?>">    
    <label>Are you sure you wish to overwrite the following:</label>
    <pre><?php echo($_GET['path']); ?></pre>
    <button class="btn-left">Overwrite</button><button class="btn-right" onclick="modal.unload();return false;">Cancel</button>
    <?php
    break;
    
}

?>
    
    