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
        // Theme Market
        //////////////////////////////////////////////////////////////
        
        case 'market':
        
            require_once('class.theme_manager.php');
            $tm = new Theme_manager();
            $market = $tm->Market();
            ?>
            <label><?php i18n("Theme Market"); ?></label>
            <div id="theme-list">
            <table width="100%">
                <tr>
                    <th><?php i18n("Theme Name"); ?></th>
                    <th><?php i18n("Description"); ?></th>
                    <th><?php i18n("Author"); ?></th>
                    <th><?php i18n("Download"); ?></th>
                </tr>
            <?php
            if($market != '') {
                $marketlist = array();
                foreach($market as $theme) {
                    if(!isset($theme['category']) || $theme['category'] == '') {
                        $theme['category'] = 'Common';
                    }
                    if(!array_key_exists($theme['category'], $marketlist)) {
                        $marketlist[$theme['category']] = array();
                    } 
                    array_push($marketlist[$theme['category']], $theme);
                }
                
                ksort($marketlist);
                $isDownloadable = (is_writeable(THEMES) && extension_loaded('zip') && extension_loaded('openssl') && ini_get('allow_url_fopen') == 1);
                
                foreach($marketlist as $category=>$themelist) {
                    ?>
                    <tr>
                    <th colspan="4"><?php echo $category;?></th>
                    </tr>
                    <?php
                    foreach($themelist as $theme) {
                        if(substr($theme['url'],-4) == '.git') {
                            $theme['url'] = substr($theme['url'],0,-4);
                        }
                    
                        ?>
                        <tr>
                            <td><?php echo $theme['name']; ?></td>
                            <td width="400px"><?php echo $theme['description']; ?></td>
                            <td><?php echo $theme['author']; ?></td>
                            <?php
                                if(file_exists(THEMES.substr($theme['url'],strrpos($theme['url'],'/'))) || file_exists(THEMES.substr($theme['url'],strrpos($theme['url'],'/')).'-master')) {
                                    ?>
                                    <td><div class="icon-check icon"></div></td>
                                    <?php
                                } else {
                                    if(checkAccess()){
                                        if($isDownloadable) {
                                        ?>
                                         <td><table style="text-align:center;border-spacing:0;border-collapse:collapse;"><tr><td style="border: 0;padding: 0;"><a class="icon-download icon" onclick="codiad.theme_manager.install('<?php echo $theme['name']; ?>','<?php echo $theme['url']; ?>');return false;"></a></td><td style="border: 0;padding: 0;"><a class="icon-github icon" onclick="codiad.theme_manager.openInBrowser('<?php echo $theme['url']; ?>');return false;"></a></td></tr></table></td>   
                                        <?php       
                                        } else {
                                        ?>
                                        <td><a class="icon-download icon" onclick="codiad.theme_manager.openInBrowser('<?php echo $theme['url']; ?>');return false;"></a></td>
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
                <tr><td colspan="4"><?php i18n("Theme Market currently unavailable."); ?></td></tr>
                <?php
            }
            
            ?>
            </table>
            </div>
            <button class="btn-left" onclick="codiad.theme_manager.list();return false;"><?php i18n("Installed Themes"); ?></button><button class="btn-mid" onclick="codiad.theme_manager.check();return false;"><?php i18n("Update Check"); ?></button><button class="btn-right" onclick="codiad.modal.unload();return false;"><?php i18n("Close"); ?></button>
            <?php
            
            break;
        
        //////////////////////////////////////////////////////////////
        // List Themes
        //////////////////////////////////////////////////////////////
        
        case 'list':
            ?>
            <label><?php i18n("Theme List"); ?></label>
            <div id="theme-list">
            <table width="100%">
                <tr>
                    <th><?php i18n("Theme Name"); ?></th>
                    <th><?php i18n("Version"); ?></th>
                    <th><?php i18n("Author"); ?></th>
                    <th width="5"><?php i18n("Active"); ?></th>
                    <?php if(is_writeable(THEMES)) { ?>
                    <th width="5"><?php i18n("Delete"); ?></th>
                    <?php } ?>
                </tr>
            <?php
            
            // Get JSON data
            $themes = getJSON('themes.php');
            $availablethemes = array();
            $themecount = 0;
            foreach (scandir(THEMES) as $fname){
                if($fname == '.' || $fname == '..' ){
                    continue;
                }
                if(is_dir(THEMES.'/'.$fname)){
                    $availablethemes[] = $fname;
                    if(file_exists(THEMES . "/" . $fname . "/theme.json")) {
                        $data = file_get_contents(THEMES . "/" . $fname . "/theme.json");
                        $data = json_decode($data,true);
                        ?>
                        <tr>
                            <td><?php if($data[0]['name'] != '') { echo $data[0]['name']; } else { echo $fname; } ?></td>
                            <td><?php echo $data[0]['version']; ?></td>
                            <td><?php echo $data[0]['author']; ?></td>
                            <?php
                                if(checkAccess()){
                                    if(in_array($fname, $themes)) {
                                    ?>
                                    <td><a onclick="codiad.theme_manager.deactivate('<?php echo($fname); ?>');" class="icon-check icon"></a></td>
                                    <?php 
                                    } else {
                                    ?>
                                     <td><a onclick="codiad.theme_manager.activate('<?php echo($fname); ?>');" class="icon-block icon"></a></td>   
                                    <?php                                    
                                    }
                                    if(is_writeable(THEMES)) {
                                        ?>
                                        <td><a onclick="codiad.theme_manager.remove('<?php echo($fname); ?>');" class="icon-cancel-circled icon"></a></td>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <td><div class="<?php if(in_array($fname, $themes)) { echo 'icon-check'; } else { echo 'icon-block'; } ?> icon"></div></td>
                                    <?php if(is_writeable(THEMES)) { ?>
                                    <td></td>
                                    <?php
                                    }
                                }
                            ?>
                        </tr>
                        <?php
                        $themecount++;
                    }
                }
            }
            
            $colspan = 4;            
            if(is_writeable(THEMES)) {
                $colspan = 5;
            }
            
            if($themecount == 0) {
            ?>
            <tr><td colspan="<?php echo $colspan;?>"><?php i18n("No Themes installed. Check Theme Market."); ?></td></tr>
            <?php
            } else {
            ?>
            <tr><td colspan="<?php echo $colspan;?>" align="right"><?php echo $themecount; ?> <?php i18n("Themes installed."); ?></td></tr>
            <?php
            }
            
            // clean old Themes from json file
            $revised_array = array();
            foreach($themes as $theme){
                if(in_array($theme, $availablethemes)){
                    $revised_array[] = $theme;
                }
            }
            // Save array back to JSON
            saveJSON('themes.php',$revised_array);
            
            ?>
            </table>
            </div>
            <button class="btn-mid" onclick="codiad.theme_manager.market();return false;"><?php i18n("Theme Market"); ?></button><button class="btn-mid" onclick="codiad.theme_manager.check();return false;"><?php i18n("Update Check"); ?></button><button class="btn-right" onclick="codiad.modal.unload();return false;"><?php i18n("Close"); ?></button>
            <?php
            
            break;
            
        //////////////////////////////////////////////////////////////
        // Update Themes
        //////////////////////////////////////////////////////////////
        
        case 'check':
            ?>
            <label><?php i18n("Theme Update Check"); ?></label>
            <div id="theme-list">
            <table width="100%">
                <tr>
                    <th><?php i18n("Theme Name"); ?></th>
                    <th><?php i18n("Your Version"); ?></th>
                    <th><?php i18n("Latest Version"); ?></th>
                    <th><?php i18n("Download"); ?></th>
                </tr>
            <?php
            
            $isDownloadable = (is_writeable(THEMES) && extension_loaded('zip') && extension_loaded('openssl') && ini_get('allow_url_fopen') == 1);
            
            // Get projects JSON data
            $themes = getJSON('themes.php');
            $themecount = 0;
            foreach (scandir(THEMES) as $fname){
                if($fname == '.' || $fname == '..' ){
                    continue;
                }
                if(is_dir(THEMES.'/'.$fname)){
                    if(file_exists(THEMES . "/" . $fname . "/theme.json")) {
                        $data = file_get_contents(THEMES . "/" . $fname . "/theme.json");
                        $data = json_decode($data,true);
                        
                        if($data[0]['url'] != '') {
                            $url = str_replace('github.com','raw.github.com',$data[0]['url']);
                            if(substr($url,-4) == '.git') {
                                $url = substr($url,0,-4);
                            }
                            $url = $url.'/master/theme.json';
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
                                            <td><table style="text-align:center;border-spacing:0;border-collapse:collapse;"><tr><td style="border: 0;padding: 0;"><a class="icon-download icon" onclick="codiad.theme_manager.update('<?php echo $fname; ?>');return false;"></a></td><td style="border: 0;padding: 0;"><a class="icon-github icon" onclick="codiad.theme_manager.openInBrowser('<?php echo $data[0]['url']; ?>');return false;"></a></td></tr></table></td>
                                        <?php
                                        } else {
                                        ?>
                                        <td><a class="icon-download icon" onclick="codiad.theme_manager.openInBrowser('<?php echo $data[0]['url']; ?>');return false;"></a></td>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                            <td><font style="color:green"><?php i18n("Latest"); ?></font></td>
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
                        $themecount++;
                    }
                }
            }
            
            if($themecount == 0) {
            ?>
            <tr><td colspan="4"><?php i18n("No Themes installed. Check Themes Market."); ?></td></tr>
            <?php
            }             
            ?>
            </table>
            </div>
            <button class="btn-left" onclick="codiad.theme_manager.check();return false;"><?php i18n("Rescan"); ?></button><button class="btn-mid" onclick="codiad.theme_manager.list();return false;"><?php i18n("Installed Themes"); ?></button><button class="btn-mid" onclick="codiad.theme_manager.market();return false;"><?php i18n("Theme Market"); ?></button><button class="btn-right" onclick="codiad.modal.unload();return false;"><?php i18n("Close"); ?></button>
            <?php
            
            break;
                    
    }
    
?>
        
