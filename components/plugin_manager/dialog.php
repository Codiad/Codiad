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
        
        //////////////////////////////////////////////////////////////
        // List Projects
        //////////////////////////////////////////////////////////////
        
        case 'list':
            ?>
            <label>Plugin List</label>
            <div id="plugin-list">
            <table width="100%">
                <tr>
                    <th>Plugin Name</th>
                    <th>Version</th>
                    <th>Author</th>
                    <th width="5">Active</th>
                </tr>
            <?php
            
            // Get projects JSON data
            $plugin = getJSON('plugins.php');
            foreach (scandir(PLUGINS) as $fname){
                if($fname == '.' || $fname == '..' ){
                    continue;
                }
                if(is_dir(PLUGINS.'/'.$fname)){
                    if(file_exists(PLUGINS . "/" . $fname . "/plugin.json")) {
                        $data = file_get_contents(PLUGINS . "/" . $fname . "/plugin.json");
                        $data = json_decode($data,true);
                        ?>
                        <tr>
                            <td><?php if($data[0]['name'] != '') { echo $data[0]['name']; } else { echo $fname; } ?></td>
                            <td><?php echo $data[0]['version']; ?></td>
                            <td><?php echo $data[0]['author']; ?></td>
                            <?php
                                if(checkAccess()){
                                    if(in_array($fname, $plugin)) {
                                    ?>
                                    <td><a onclick="codiad.plugin_manager.deactivate('<?php echo($fname); ?>');" class="icon-check icon"></a></td>
                                    <?php 
                                    } else {
                                    ?>
                                     <td><a onclick="codiad.plugin_manager.activate('<?php echo($fname); ?>');" class="icon-block icon"></a></td>   
                                    <?php                                    
                                    }
                                } else {
                                    ?>
                                    <td><div class="<?php if(in_array($fname, $plugin)) { echo 'icon-check'; } else { echo 'icon-block'; } ?> icon"></div></td>
                                    <?php
                                }
                            ?>
                        </tr>
                        <?php
                    }
                }
            }
            ?>
            </table>
            </div>
            <button onclick="codiad.modal.unload();return false;">Close</button>
            <?php
            
            break;
                    
    }
    
?>
        
