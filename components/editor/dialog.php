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
    
}

?>
</form>
<script>

$(function(){ $('input[name="find"]').val(active.get_selected_text()); });

</script>