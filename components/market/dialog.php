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
        
            require_once('class.market.php');
            $market = new Market();
            ?>
            <label><?php i18n("Codiad Marketplace"); ?></label>
            <div id="market-list">
            <table width="100%">
                <tr>
                    <th valign="middle"><button style="margin:0;" class="btn-left" onclick="codiad.market.list();return false;">All</button><button class="btn-mid" style="margin:0;"  onclick="codiad.market.list('plugins');return false;">Plugins</button><button class="btn-right" style="margin:0;" onclick="codiad.market.list('themes');return false;">Themes</button></th>
                    <th valign="middle" width="30%"><input style="margin:0;display:inline" onkeyup="codiad.market.search(event, this.value)" value="<?php if(isset($_GET['query'])) echo $_GET['query'];?>" placeholder="Press Enter to Search"></th>
                </tr>
             </table>
             <div style="height: 450px; width: 100%; overflow-y: auto; overflow-x: hidden;">
             <table width="100%">
            <?php
                $marketplace = array();
                foreach($market->remote as $data) {
                    if(!isset($data['category']) || $data['category'] == '') {
                        $data['category'] = 'Common';
                    }
                    if(!array_key_exists($data['type'], $marketplace)) {
                        $marketplace[$data['type']] = array();
                    }
                    if(!array_key_exists($data['category'], $marketplace[$data['type']])) {
                        $marketplace[$data['type']][$data['category']] = array();
                    } 
                    array_push($marketplace[$data['type']][$data['category']], $data);
                }
                ksort($marketplace);
                
                $extLoaded = (extension_loaded('zip') && extension_loaded('openssl') && ini_get('allow_url_fopen') == 1);
                function sort_name($a, $b) { return strnatcmp($a['name'], $b['name']); }
                
                foreach($marketplace as $type=>$data) {
                    ksort($data);
                    if($_GET['type'] == 'undefined' || $_GET['type'] == $type) {
                      foreach($data as $category=>$subdata) {
                          usort($subdata, 'sort_name');
                          foreach($subdata as $addon){
                            if(isset($_GET['query']) && (strpos(strtolower($addon['name']), strtolower($_GET['query'])) === false)) {
                              break;
                            }
                            echo '<tr><td><div style="position:relative;height:110px">';
                            $left = 0;
                            if(isset($addon['image']) && $addon['image'] != '') {
                              echo '<div style="margin-top:5px;"><a onclick="codiad.market.openInBrowser(\''.$addon['image'].'\');return false;"><img src="'.$addon['image'].'" width="150" height="100"></a></div>';
                              $left = 150;
                            } else {
                              if(isset($addon['new'])) {
                                $left = $left + 40;
                              }
                            }
                            if(isset($addon['new'])) {
                                echo '<div style="position:absolute;top:0px;left:0px;z-index:10000;"><img src="./themes/default/images/new.png"></div>';
                            }
                            echo '<div style="position:absolute;top:2px;left:'.($left+10).'px;"><a style="font-weight:bold;" onclick="codiad.market.openInBrowser(\''.$addon['url'].'\');return false;">'.$addon['name'].'</a></div>';
                            echo '<div style="position:absolute;top:15px;left:'.($left+10).'px;"><font style="font-size:10px">'.ucfirst(rtrim($type,'s')).' - '.ucfirst($category).' | <a style="font-weight:bold;text-decoration:underline;" onclick="codiad.market.openInBrowser(\'https://github.com/'.$addon['author'].'\');return false;">'.$addon['author'].'</a> | '.$addon['count'].' Users</font></div>';
                            echo '<div style="position:absolute;top:25px;left:'.($left+5).'px;"><pre style="height:60px;color:#a8a6a8;width:'.(550-$left).'px;white-space: pre-wrap;">'.$addon['description'].'</pre></div>';
                            if(isset($addon['version'])) {
                              if(!isset($addon['update'])) {
                                  echo '<div style="position:absolute;top:7px;left:570px;"><font style="color:green">Latest Version v'.$addon['version'].'</font></div>';
                              } else {
                                 if($extLoaded && is_writable(BASE_PATH.'/'.$type.'/'.$addon['folder'])) {
                                  echo '<div style="position:absolute;top:-5px;left:570px;"><button style="color: blue; width:150px" onclick="codiad.market.update(\''.$_GET['type'].'\',\''.$type.'\', \''.$addon['folder'].'\');return false;">Update '.ucfirst(rtrim($type,'s')).'</button></div>';
                                 } else {
                                  echo '<div style="position:absolute;top:-5px;left:570px;"><button style="width:150px" onclick="codiad.market.openInBrowser(\''.$addon['url'].'\');">Download '.ucfirst(rtrim($type,'s')).'</button><div>';
                                 }
                              }
                              if(is_writable(BASE_PATH.'/'.$type.'/'.$addon['folder'])) {
                                echo '<div style="position:absolute;top:30px;left:570px;"><button style="color: red; width:150px" onclick="codiad.market.remove(\''.$_GET['type'].'\',\''.$type.'\', \''.$addon['folder'].'\');return false;">Delete '.ucfirst(rtrim($type,'s')).'</button><div>';
                              }
                            } else {
                              if($extLoaded && is_writable(BASE_PATH.'/'.$type)) {
                                echo '<div style="position:absolute;top:-5px;left:570px;"><button style="width:150px" onclick="codiad.market.install(\''.$_GET['type'].'\',\''.$type.'\', \''.$addon['name'].'\',\''.$addon['url'].'\');return false;">Install '.ucfirst(rtrim($type,'s')).'</button><div>';
                              } else {
                                echo '<div style="position:absolute;top:-5px;left:570px;"><button style="width:150px" onclick="codiad.market.openInBrowser(\''.$addon['url'].'\');">Download '.ucfirst(rtrim($type,'s')).'</button><div>';
                              }
                            }
                            echo '</div></td></tr>';
                          }
                      }
                    }
                }            
            ?>
            </table></div>
            </div>
            <table width="100%">
                <tr>
                    <th valign="middle" width="150px"><button onclick="window.location.reload();return false;"><?php i18n("Reload Codiad"); ?></button></th>
                    <th valign="middle"><input style="margin:0;display:inline" id="repourl" placeholder="Enter GitHub Repository Url..."></th>
                    <th valign="middle" align="right" width="222px"><button class="btn-left" onclick="codiad.market.install('<?php echo $_GET['type']; ?>','','Manually',getElementById('repourl').value);return false;"><?php i18n("Install Manually"); ?></button><button class="btn-right" onclick="codiad.modal.unload();return false;"><?php i18n("Close"); ?></button></th>
                </tr>
             </table>
            <?php
            
            break;
             
    }
    
?>
        
