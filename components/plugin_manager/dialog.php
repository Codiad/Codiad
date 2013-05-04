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
        // Plugin Market
        //////////////////////////////////////////////////////////////
        
        case 'market':
        
            require_once('class.plugin_manager.php');
            $pm = new Plugin_manager();
            $market = $pm->Market();
            ?>
            <label>Plugin Market</label>
            <div id="plugin-list">
            <table width="100%">
                <tr>
                    <th>Plugin Name</th>
                    <th>Description</th>
                    <th>Author</th>
                    <th width="5">Install</th>
                </tr>
            <?php
            if($market != '') {
                foreach($market as $plugin) {
                    ?>
                    <tr>
                        <td><?php echo $plugin['name']; ?></td>
                        <td><?php echo $plugin['description']; ?></td>
                        <td><?php echo $plugin['author']; ?></td>
                        <?php
                            if(checkAccess()){
                                ?>
                                 <td><table style="text-align:center;border-spacing:0;border-collapse:collapse;"><tr><td style="border: 0;padding: 0;"><a class="icon-download icon" onclick="codiad.plugin_manager.install('<?php echo $plugin['name']; ?>','<?php echo $plugin['url']; ?>');return false;"></a></td><td style="border: 0;padding: 0;"><a class="icon-github icon" onclick="codiad.plugin_manager.openInBrowser('<?php echo $data[0]['url']; ?>');return false;"></a></td></tr></table></td>   
                                <?php                                    
                            } else {
                                ?>
                                <td><div class="icon-block icon"></div></td>
                                <?php
                            }
                        ?>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr><td colspan="4">Plugin Market currently unavailable.</td></tr>
                <?php
            }
            
            ?>
            </table>
            </div>
            <button class="btn-left" onclick="codiad.plugin_manager.list();return false;">Installed Plugins</button><button class="btn-right" onclick="codiad.modal.unload();return false;">Close</button>
            <?php
            
            break;
        
        //////////////////////////////////////////////////////////////
        // List Plugins
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
            $plugins = getJSON('plugins.php');
            $availableplugins = array();
            foreach (scandir(PLUGINS) as $fname){
                if($fname == '.' || $fname == '..' ){
                    continue;
                }
                if(is_dir(PLUGINS.'/'.$fname)){
                    $availableplugins[] = $fname;
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
                                    if(in_array($fname, $plugins)) {
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
                                    <td><div class="<?php if(in_array($fname, $plugins)) { echo 'icon-check'; } else { echo 'icon-block'; } ?> icon"></div></td>
                                    <?php
                                }
                            ?>
                        </tr>
                        <?php
                    }
                }
            }
            
            // clean old plugins from json file
            $revised_array = array();
            foreach($plugins as $plugin){
                if(in_array($plugin, $availableplugins)){
                    $revised_array[] = $plugin;
                }
            }
            // Save array back to JSON
            saveJSON('plugins.php',$revised_array);
            
            ?>
            </table>
            </div>
            <button class="btn-left" onclick="window.location.reload();return false;">Reload Codiad</button><button class="btn-mid" onclick="codiad.plugin_manager.market();return false;">Plugin Market</button><button class="btn-mid" onclick="codiad.plugin_manager.check();return false;">Update Check</button><button class="btn-right" onclick="codiad.modal.unload();return false;">Close</button>
            <?php
            
            break;
            
        //////////////////////////////////////////////////////////////
        // Update Plugins
        //////////////////////////////////////////////////////////////
        
        case 'check':
            ?>
            <label>Plugin Update Check</label>
            <div id="plugin-list">
            <table width="100%">
                <tr>
                    <th>Plugin Name</th>
                    <th>Your Version</th>
                    <th>Latest Version</th>
                    <th>Update</th>
                </tr>
            <?php
            
            // Get projects JSON data
            $plugins = getJSON('plugins.php');
            foreach (scandir(PLUGINS) as $fname){
                if($fname == '.' || $fname == '..' ){
                    continue;
                }
                if(is_dir(PLUGINS.'/'.$fname)){
                    if(file_exists(PLUGINS . "/" . $fname . "/plugin.json")) {
                        $data = file_get_contents(PLUGINS . "/" . $fname . "/plugin.json");
                        $data = json_decode($data,true);
                        
                        if($data[0]['url'] != '') {
                            $url = str_replace('github.com','raw.github.com',$data[0]['url']);
                            if(substr($url,-4) == '.git') {
                                $url = substr($url,0,-4);
                            }
                            $url = $url.'/master/plugin.json';
                            $remote = json_decode(file_get_contents($url),true);
                        }
                        
                        ?>
                        <tr>
                            <td><?php if($data[0]['name'] != '') { echo $data[0]['name']; } else { echo $fname; } ?></td>
                            <td><?php echo $data[0]['version']; ?></td>
                            <?php
                                if($data[0]['url'] != '') {                                   
                                    ?>
                                    <td><?php if($remote[0]['version'] != '') { echo($remote[0]['version']); } else { echo 'n/a'; } ?></td>
                                    <?php
                                    if($remote[0]['version'] != $data[0]['version']) {
                                        ?>
                                            <td><table style="text-align:center;border-spacing:0;border-collapse:collapse;"><tr><td style="border: 0;padding: 0;"><a class="icon-download icon" onclick="codiad.plugin_manager.update('<?php echo $fname; ?>');return false;"></a></td><td style="border: 0;padding: 0;"><a class="icon-github icon" onclick="codiad.plugin_manager.openInBrowser('<?php echo $data[0]['url']; ?>');return false;"></a></td></tr></table></td>
                                        <?php
                                    } else {
                                        ?>
                                            <td></td>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <td>n/a</td>
                                    <td></td> 
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
            <button class="btn-left" onclick="codiad.plugin_manager.check();return false;">Rescan</button><button class="btn-right" onclick="codiad.modal.unload();return false;">Close</button>
            <?php
            
            break;
                    
    }
    
?>
        
