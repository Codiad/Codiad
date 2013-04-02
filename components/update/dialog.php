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
            <input type="hidden" name="archive" value="<?php echo($_GET['archive']); ?>">
            <label>Update Check</label>
            <br><table>
                <tr><td>Your Version</td><td><?php echo $_GET['current']; ?></td></tr>
                <tr><td>GitHub Version</td><td><?php echo $_GET['remote']; ?></td></tr>
            </table>
            <?php if($_GET['current'] != $_GET['remote'] && $_GET['message'] != 'null') { ?>
            <br><label>News from Codiad</label>
            <pre><?php echo $_GET['message']; ?></pre>
            <?php } ?>
            <br><?php
                if($_GET['current'] != $_GET['remote']) {
                    echo '<button class="btn-left">Download Codiad</button>&nbsp;';
                }
            ?><button class="btn-right" onclick="codiad.modal.unload();return false;">Cancel</button>
            <form>
            <?php
            break;
            
    }
    
?>
