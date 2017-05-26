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
        // Marketplace
        //////////////////////////////////////////////////////////////
        
        case 'list':
            if(!checkAccess()){ 
            ?>
            <label><?php i18n("Restricted"); ?></label>
            <pre><?php i18n("You can not access the marketplace"); ?></pre>
            <button onclick="codiad.modal.unload();return false;"><?php i18n("Close"); ?></button>
            <?php } else {
            require_once('class.market.php');
            $market = new Market();
            if(sizeof($market->remote) == 0){
            ?>
            <label><?php i18n("Connection Error"); ?></label>
            <pre><?php i18n("Connection to the market place can not be made. Please check your internet connection."); ?></pre>
            <button onclick="codiad.modal.unload();return false;"><?php i18n("Close"); ?></button>
            <?php } else { ?>
            <label><?php i18n("Codiad Marketplace"); ?></label>
            <div id="market-list">
            <table width="100%">
                <tr>
                    <th valign="middle" style="white-space:nowrap;"><button style="margin:0;" class="btn-left" onclick="codiad.market.list();return false;"><?php i18n("All"); ?></button><button class="btn-mid" style="margin:0;"  onclick="codiad.market.list('plugins');return false;"><?php i18n("Plugins"); ?></button><button class="btn-right" style="margin:0;" onclick="codiad.market.list('themes');return false;"><?php i18n("Themes"); ?></button></th>
                    <th valign="middle" width="30%" style="white-space:nowrap;"><input style="margin:0;display:inline" onkeyup="codiad.market.search(event, this.value,'<?php echo $_GET['note']; ?>')" value="<?php if(isset($_GET['query'])) echo $_GET['query'];?>" placeholder="<?php i18n("Press Enter to Search"); ?>"></th>
                </tr>
             </table>
             <div class="market-wrapper">
             <table width="100%">
            <?php
                $marketplace = array();
                foreach($market->remote as $data) {
                    if(!isset($data['category']) || $data['category'] == '') {
                        $data['category'] = 'Common';
                    }
                    if(!array_key_exists($data['remote'], $marketplace)) {
                        $marketplace[$data['remote']] = array();
                    }
                    if(!array_key_exists($data['type'], $marketplace[$data['remote']])) {
                        $marketplace[$data['remote']][$data['type']] = array();
                    }
                    if(!array_key_exists($data['category'], $marketplace[$data['remote']][$data['type']])) {
                        $marketplace[$data['remote']][$data['type']][$data['category']] = array();
                    } 
                    array_push($marketplace[$data['remote']][$data['type']][$data['category']], $data);
                }
                ksort($marketplace);
                
                $extLoaded = (extension_loaded('zip') && extension_loaded('openssl') && ini_get('allow_url_fopen') == 1);
                function sort_name($a, $b) { return strnatcmp($a['name'], $b['name']); }
                
                foreach($marketplace as $remote=>$markettype) {
                    ksort($markettype);
                    echo '<tr><th class="market-remote-title">';
                    if(!$remote) {
                      echo get_i18n("Installed");
                    } else {
                      echo get_i18n("Available");
                    }
                    echo '</th></tr>';
                    foreach($markettype as $type=>$data) {
                        ksort($data);
                        if($_GET['type'] == 'undefined' || $_GET['type'] == $type) {
                          foreach($data as $category=>$subdata) {
                              usort($subdata, 'sort_name');
                              foreach($subdata as $addon){
                                if(isset($_GET['query']) && (strpos(strtolower(trim($addon['name'])), strtolower(trim($_GET['query']))) === false)) {
                                  continue;
                                }
                                echo '<tr><td><div style="position:relative;height:100px">';
                                $left = 0;
                                $right = 0;
                                if(isset($addon['image']) && $addon['image'] != '') {
                                  echo '<div style="position:absolute;top:5px;left:404px;"><a onclick="codiad.market.openInBrowser(\''.$addon['image'].'\');return false;"><img src="'.$addon['image'].'" width="150" height="90"></a></div>';
                                  $right = 160;
                                } 
                                if(isset($addon['new'])) {
                                    echo '<div style="position:absolute;top:0px;left:0px;z-index:10000;"><img src="./themes/default/images/new.png" width="35" height="35"></div>';
                                    $left = $left + 30;
                                }
                                echo '<div style="position:absolute;top:2px;left:'.($left+10).'px;"><a style="font-weight:bold;font-size:14px" onclick="codiad.market.openInBrowser(\''.$addon['url'].'\');return false;">'.$addon['name'].'</a></div>';
                                echo '<div style="position:absolute;top:15px;left:'.($left+10).'px;"><font style="font-size:10px">'.get_i18n(ucfirst(rtrim($type,'s'))).' - '.get_i18n(ucfirst($category)).' | <a style="font-weight:bold;text-decoration:underline;" onclick="codiad.market.openInBrowser(\'https://github.com/'.$addon['author'].'\');return false;">'.$addon['author'].'</a> | '.$addon['count'].' '.get_i18n("Users").'</font></div>';
                                echo '<div style="position:absolute;top:25px;left:5px;"><pre style="height:60px;color:#a8a6a8;width:'.(550-$right).'px;white-space: pre-wrap;">'.$addon['description'].'</pre></div>';
                                if(!$addon['remote']) {
                                  if(!isset($addon['update'])) {
                                      echo '<div style="position:absolute;top:7px;left:570px;"><font style="color:green">'.get_i18n("Latest Version").' v'.$addon['version'].'</font></div>';
                                  } else {
                                     if($extLoaded && is_writable(BASE_PATH.'/'.$type.'/'.$addon['folder'])) {
                                      echo '<div style="position:absolute;top:-5px;left:570px;"><button style="color: blue; width:150px;white-space:nowrap;" onclick="codiad.market.update(\''.$_GET['type'].'\',\''.$type.'\', \''.$addon['folder'].'\');return false;">'.get_i18n("Update ".ucfirst(rtrim($type,'s'))).'</button></div>';
                                     } else {
                                      echo '<div style="position:absolute;top:-5px;left:570px;"><button style="width:150px;white-space:nowrap;" onclick="codiad.market.openInBrowser(\''.$addon['url'].'\');">'.get_i18n("Download ".ucfirst(rtrim($type,'s'))).'</button><div>';
                                     }
                                  }
                                  if(is_writable(BASE_PATH.'/'.$type.'/'.$addon['folder'])) {
                                    echo '<div style="position:absolute;top:30px;left:570px;"><button style="color: red; width:150px;white-space:nowrap;" onclick="codiad.market.remove(\''.$_GET['type'].'\',\''.$type.'\', \''.$addon['folder'].'\');return false;">'.get_i18n("Delete ".ucfirst(rtrim($type,'s'))).'</button><div>';
                                  }
                                } else {
                                  if($extLoaded && is_writable(BASE_PATH.'/'.$type)) {
                                    echo '<div style="position:absolute;top:-5px;left:570px;"><button style="width:150px;white-space:nowrap;" onclick="codiad.market.install(\''.$_GET['type'].'\',\''.$type.'\', \''.$addon['name'].'\',\''.$addon['url'].'\');return false;">'.get_i18n("Install ".ucfirst(rtrim($type,'s'))).'</button><div>';
                                  } else {
                                    echo '<div style="position:absolute;top:-5px;left:570px;"><button style="width:150px;white-space:nowrap;" onclick="codiad.market.openInBrowser(\''.$addon['url'].'\');">'.get_i18n("Download ".ucfirst(rtrim($type,'s'))).'</button><div>';
                                  }
                                }
                                echo '</div></td></tr>';
                              }
                          }
                        }
                    } 
                }           
            ?>
            </table></div>
            </div>
            <?php } ?>
            <table width="100%">
                <tr>
                    <th valign="middle" align="center" width="40px"><?php
                      if($_GET['note'] != 'undefined' && $_GET['note'] == 'true') {
                         ?><button style="color: blue;white-space:nowrap;" onclick="window.location.reload();return false;"><?php i18n("Reload Codiad"); ?></button><?php
                      } else {
                         ?><button class="icon-arrows-ccw bigger-icon" onclick="window.location.reload();return false;"></button><?php
                      }                    
                    ?></th>
                    <th valign="middle"><input style="margin:0;display:inline" id="repourl" placeholder="<?php i18n("Enter GitHub Repository Url..."); ?>"></th>
                    <th valign="middle" align="right" style="white-space:nowrap;" width="222px"><button class="btn-left" onclick="codiad.market.install('<?php echo $_GET['type']; ?>','','Manually',getElementById('repourl').value);return false;"><?php i18n("Install Manually"); ?></button><button class="btn-right" onclick="codiad.modal.unload();return false;"><?php i18n("Close"); ?></button></th>
                </tr>
             </table>
            <?php
            }            
            break;
             
    }
    
?>
        
