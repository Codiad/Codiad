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
    // Confirm Close Unsaved File
    //////////////////////////////////////////////////////////////////
    case 'confirm':
    $path = $_GET['path'];
    ?>
    <label>Close Unsaved File?</label>
    
    <pre><?php echo($path); ?></pre>

    <button class="btn-left" onclick="save_and_close('<?php echo($path); ?>'); return false;">Save &amp; Close</button><button class="btn-mid" onclick="close_without_save('<?php echo($path); ?>'); return false;">Discard Changes</button><button class="btn-right" onclick="modal.unload(); return false;">Cancel</button>
    <?php
    break;
    
}

?>
</form>
<script>

    function save_and_close(path){
        var id = editor.get_id(path);
        var content = editor.get_content(id);
        filemanager.save_file(path,content);
        active.close(path);        
        modal.unload();
    }
    
    function close_without_save(path){
        active.close(path);        
        modal.unload();
    }

</script>