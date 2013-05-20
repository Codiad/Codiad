<?php

    /*
    *  Copyright (c) Codiad & daeks (codiad.com), distributed
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
        
            if(!checkAccess()){ 
            ?>
            <label>Restricted</label>
            <pre>You can not check for updates</pre>
            <button onclick="codiad.modal.unload();return false;">Close</button>
            <?php } else {
                require_once('class.update.php');
                $update = new Update();
                $vars = json_decode($update->Check(), true);
            ?>
            <form>
            <input type="hidden" name="archive" value="<?php echo $vars[0]['data']['archive']; ?>">
            <input type="hidden" name="remoteversion" value="<?php echo $vars[0]['data']['remoteversion']; ?>">
            <label>Update Check</label>
            <br><table>
                <tr><td>Your Version</td><td><?php echo $vars[0]['data']['currentversion']; ?></td></tr>
                <tr><td>Latest Version</td><td><?php echo $vars[0]['data']['remoteversion']; ?></td></tr>
            </table>
            <?php if($vars[0]['data']['currentversion'] != $vars[0]['data']['remoteversion']) { ?>
            <br><label>Changes on Codiad</label>
            <pre style="overflow: auto; max-height: 200px; max-width: 450px;"><?php echo $vars[0]['data']['message']; ?></pre>
            <?php } else { ?>
            <br><br><b><label>Congratulation, your system is up to date.</label></b>
            <?php if($vars[0]['data']['name'] != '') { ?>
            <em>Last update was done by <?php echo $vars[0]['data']['name']; ?>.</em>
            <?php } } ?>
            <br><?php
                if($vars[0]['data']['currentversion'] != $vars[0]['data']['remoteversion']) {
                    echo '<button class="btn-left" onclick="codiad.update.download();return false;">Download Codiad</button>&nbsp;';
                }
            ?><button class="btn-right" onclick="codiad.modal.unload();return false;">Cancel</button>
            <form>
            <?php }
            break;
            
    }
    
?>
