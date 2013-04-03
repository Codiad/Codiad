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

    switch($_GET['action']){
            
        //////////////////////////////////////////////////////////////////////
        // Update
        //////////////////////////////////////////////////////////////////////
        
        case 'check':
        
            ?>
            <form>
            <input type="hidden" name="archive" value="<?php echo($_GET['remote']); ?>">
            <label>Update Check</label>
            <br><table>
                <tr><td>Your Version</td><td><?php echo $_GET['current']; ?></td></tr>
                <tr><td>Latest Version</td><td><?php echo $_GET['remote']; ?></td></tr>
            </table>
            <?php if($_GET['current'] != $_GET['remote']) { ?>
            <br><label>Changes on Codiad</label>
            <pre style="overflow-x: auto; overflow-y: scroll; max-height: 200px; max-width: 400px;"><?php echo $_GET['message']; ?></pre>
            <?php } else { ?>
            <br><br><b><label>Congratulation, your system is up to date.</label></b>
            <?php } ?>
            <br><?php
                if($_GET['current'] != $_GET['remote']) {
                    echo '<button class="btn-left">Update Codiad</button>&nbsp;';
                }
            ?><button class="btn-right" onclick="codiad.modal.unload();return false;">Cancel</button>
            <form>
            <?php
            break;
            
    }
    
?>
