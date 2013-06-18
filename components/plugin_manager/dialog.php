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
                    <th>Download</th>
                </tr>
            <?php
            if($market != '') {
                $marketlist = array();
                foreach($market as $plugin) {
                    if(!isset($plugin['category']) || $plugin['category'] == '') {
                        $plugin['category'] = 'Common';
                    }
                    if(!array_key_exists($plugin['category'], $marketlist)) {
                        $marketlist[$plugin['category']] = array();
                    } 
                    array_push($marketlist[$plugin['category']], $plugin);
                }
                
                ksort($marketlist);
                $isDownloadable = (is_writeable(PLUGINS) && extension_loaded('zip') && extension_loaded('openssl') && ini_get('allow_url_fopen') == 1);
                
                foreach($marketlist as $category=>$pluginlist) {
                    ?>
                    <tr>
                    <th colspan="4"><?php echo $category;?></th>
                    </tr>
                    <?php
                    foreach($pluginlist as $plugin) {
                        if(substr($plugin['url'],-4) == '.git') {
                            $plugin['url'] = substr($plugin['url'],0,-4);
                        }
                    
                        ?>
                        <tr>
                            <td><?php echo $plugin['name']; ?></td>
                            <td width="400px"><?php echo $plugin['description']; ?></td>
                            <td><?php echo $plugin['author']; ?></td>
                            <?php
                                if(file_exists(PLUGINS.substr($plugin['url'],strrpos($plugin['url'],'/'))) || file_exists(PLUGINS.substr($plugin['url'],strrpos($plugin['url'],'/')).'-master')) {
                                    ?>
                                    <td><div class="icon-check icon"></div></td>
                                    <?php
                                } else {
                                    if(checkAccess()){
                                        if($isDownloadable) {
                                        ?>
                                         <td><table style="text-align:center;border-spacing:0;border-collapse:collapse;"><tr><td style="border: 0;padding: 0;"><a class="icon-download icon" onclick="codiad.plugin_manager.install('<?php echo $plugin['name']; ?>','<?php echo $plugin['url']; ?>');return false;"></a></td><td style="border: 0;padding: 0;"><a class="icon-github icon" onclick="codiad.plugin_manager.openInBrowser('<?php echo $plugin['url']; ?>');return false;"></a></td></tr></table></td>   
                                        <?php       
                                        } else {
                                        ?>
                                        <td><a class="icon-download icon" onclick="codiad.plugin_manager.openInBrowser('<?php echo $plugin['url']; ?>');return false;"></a></td>
                                        <?php
                                        }                             
                                    } else {
                                        ?>
                                        <td><div class="icon-block icon"></div></td>
                                        <?php
                                    }
                                }
                            ?>
                        </tr>
                        <?php
                    }
                }
            } else {
                ?>
                <tr><td colspan="4">Plugin Market currently unavailable.</td></tr>
                <?php
            }
            
            ?>
            </table>
            </div>
            <button class="btn-left" onclick="codiad.plugin_manager.list();return false;">Installed Plugins</button><button class="btn-mid" onclick="codiad.plugin_manager.check();return false;">Update Check</button><button class="btn-right" onclick="codiad.modal.unload();return false;">Close</button>
            <?php
            
            break;
        
        //////////////////////////////////////////////////////////////
        // List Plugins
        //////////////////////////////////////////////////////////////
        
        case 'list':
            ?>
            <label><?php i18n("Plugin List"); ?></label>
            <div id="plugin-list">
            <table width="100%">
                <tr>
                    <th><?php i18n("Plugin Name"); ?></th>
                    <th><?php i18n("Version"); ?></th>
                    <th><?php i18n("Author"); ?></th>
                    <th width="5"><?php i18n("Active"); ?></th>
                    <?php if(is_writeable(PLUGINS)) { ?>
                    <th width="5"><?php i18n("Delete"); ?></th>
                    <?php } ?>
                </tr>
            <?php
            
            // Get projects JSON data
            $plugins = getJSON('plugins.php');
            $availableplugins = array();
            $plugincount = 0;
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
                                    if(is_writeable(PLUGINS)) {
                                        ?>
                                        <td><a onclick="codiad.plugin_manager.remove('<?php echo($fname); ?>');" class="icon-cancel-circled icon"></a></td>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <td><div class="<?php if(in_array($fname, $plugins)) { echo 'icon-check'; } else { echo 'icon-block'; } ?> icon"></div></td>
                                    <?php if(is_writeable(PLUGINS)) { ?>
                                    <td></td>
                                    <?php
                                    }
                                }
                            ?>
                        </tr>
                        <?php
                        $plugincount++;
                    }
                }
            }
            
            $colspan = 4;            
            if(is_writeable(PLUGINS)) {
                $colspan = 5;
            }
            
            if($plugincount == 0) {
            ?>
            <tr><td colspan="<?php echo $colspan;?>">No Plugins installed. Check Plugin Market.</td></tr>
            <?php
            } else {
            ?>
            <tr><td colspan="<?php echo $colspan;?>" align="right"><?php echo $plugincount; ?> Plugins installed.</td></tr>
            <?php
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
            <button class="btn-left" onclick="window.location.reload();return false;"><?php i18n("Reload Codiad"); ?></button><button class="btn-mid" onclick="codiad.plugin_manager.market();return false;"><?php i18n("Plugin Market"); ?></button><button class="btn-mid" onclick="codiad.plugin_manager.check();return false;"><?php i18n("Update Check"); ?></button><button class="btn-right" onclick="codiad.modal.unload();return false;"><?php i18n("Close"); ?></button>
            <?php
            
            break;
            
        //////////////////////////////////////////////////////////////
        // Update Plugins
        //////////////////////////////////////////////////////////////
        
        case 'check':
            ?>
            <label><?php i18n("Plugin Update Check"); ?></label>
            <div id="plugin-list">
            <table width="100%">
                <tr>
                    <th><?php i18n("Plugin Name"); ?></th>
                    <th><?php i18n("Your Version"); ?></th>
                    <th><?php i18n("Latest Version"); ?></th>
                    <th><?php i18n("Download"); ?></th>
                </tr>
            <?php
            
            $isDownloadable = (is_writeable(PLUGINS) && extension_loaded('zip') && extension_loaded('openssl') && ini_get('allow_url_fopen') == 1);
            
            // Get projects JSON data
            $plugins = getJSON('plugins.php');
            $plugincount = 0;
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
                                        if($isDownloadable) {
                                        ?>
                                            <td><table style="text-align:center;border-spacing:0;border-collapse:collapse;"><tr><td style="border: 0;padding: 0;"><a class="icon-download icon" onclick="codiad.plugin_manager.update('<?php echo $fname; ?>');return false;"></a></td><td style="border: 0;padding: 0;"><a class="icon-github icon" onclick="codiad.plugin_manager.openInBrowser('<?php echo $data[0]['url']; ?>');return false;"></a></td></tr></table></td>
                                        <?php
                                        } else {
                                        ?>
                                        <td><a class="icon-download icon" onclick="codiad.plugin_manager.openInBrowser('<?php echo $data[0]['url']; ?>');return false;"></a></td>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                            <td><font style="color:green">Latest</font></td>
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
                        $plugincount++;
                    }
                }
            }
            
            if($plugincount == 0) {
            ?>
            <tr><td colspan="4">No Plugins installed. Check Plugin Market.</td></tr>
            <?php
            }             
            ?>
            </table>
            </div>
            <button class="btn-left" onclick="codiad.plugin_manager.check();return false;"><?php i18n("Rescan"); ?></button><button class="btn-mid" onclick="codiad.plugin_manager.list();return false;"><?php i18n("Installed Plugins"); ?></button><button class="btn-mid" onclick="codiad.plugin_manager.market();return false;"><?php i18n("Plugin Market"); ?></button><button class="btn-right" onclick="codiad.modal.unload();return false;"><?php i18n("Close"); ?></button>
            <?php
            
            break;
                    
    }
    
?>
        
