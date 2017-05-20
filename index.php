<?php

require_once('common.php');

// Context Menu
$context_menu = file_get_contents(COMPONENTS . "/filemanager/context_menu.json");
$context_menu = json_decode($context_menu,true);

// Right Bar
$right_bar = file_get_contents(COMPONENTS . "/right_bar.json");
$right_bar = json_decode($right_bar,true);

// Read Components, Plugins, Themes
$components = Common::readDirectory(COMPONENTS);
$plugins = Common::readDirectory(PLUGINS);
$themes = Common::readDirectory(THEMES);

// Theme
$theme = THEME;
if(isset($_SESSION['theme'])) {
  $theme = $_SESSION['theme'];
}

?>
<!doctype html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php i18n("CODIAD"); ?></title>
    <?php
    // Load System CSS Files
    $stylesheets = array("jquery.toastmessage.css","reset.css","fonts.css","screen.css");
   
    foreach($stylesheets as $sheet){
        if(file_exists(THEMES . "/". $theme . "/".$sheet)){
            echo('<link rel="stylesheet" href="themes/'.$theme.'/'.$sheet.'">');
        } else {
            echo('<link rel="stylesheet" href="themes/default/'.$sheet.'">');
        }
    }
    
    // Load Component CSS Files    
    foreach($components as $component){
        if(file_exists(THEMES . "/". $theme . "/" . $component . "/screen.css")){
            echo('<link rel="stylesheet" href="themes/'.$theme.'/'.$component.'/screen.css">');
        } else {
            if(file_exists("themes/default/" . $component . "/screen.css")){
                echo('<link rel="stylesheet" href="themes/default/'.$component.'/screen.css">');
            } else {
                if(file_exists(COMPONENTS . "/" . $component . "/screen.css")){
                    echo('<link rel="stylesheet" href="components/'.$component.'/screen.css">');
                }
            }
        }
    }
    
    // Load Plugin CSS Files    
    foreach($plugins as $plugin){
        if(file_exists(THEMES . "/". $theme . "/" . $plugin . "/screen.css")){
            echo('<link rel="stylesheet" href="themes/'.$theme.'/'.$plugin.'/screen.css">');
        } else {
            if(file_exists("themes/default/" . $plugin . "/screen.css")){
                echo('<link rel="stylesheet" href="themes/default/'.$plugin.'/screen.css">');
            } else {
                if(file_exists(PLUGINS . "/" . $plugin . "/screen.css")){
                    echo('<link rel="stylesheet" href="plugins/'.$plugin.'/screen.css">');
                }
            }
        }
    }
    ?>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
</head>

<body>
    <script>
    var i18n = (function(lang) {
        return function(word,args) {
            var x;
            var returnw = (word in lang) ? lang[word] : word;
            for(x in args){
                returnw=returnw.replace("%{"+x+"}%",args[x]);   
            }
            return returnw;
        }
    })(<?php echo json_encode($lang); ?>)
    </script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script>!window.jQuery && document.write(unescape('%3Cscript src="js/jquery-1.7.2.min.js"%3E%3C/script%3E'));</script>
    <script src="js/jquery-ui-1.8.23.custom.min.js"></script>
    <script src="js/jquery.css3.min.js"></script>
    <script src="js/jquery.easing.js"></script>
    <script src="js/jquery.toastmessage.js"></script>
    <script src="js/amplify.min.js"></script>
    <script src="js/localstorage.js"></script>
    <script src="js/jquery.hoverIntent.min.js"></script>
    <script src="js/system.js"></script>
    <script src="js/sidebars.js"></script>
    <script src="js/modal.js"></script>
    <script src="js/message.js"></script>
    <script src="js/jsend.js"></script>
    <script src="js/instance.js?v=<?php echo time(); ?>"></script>
    <div id="message"></div>
    <?php

    //////////////////////////////////////////////////////////////////
    // NOT LOGGED IN
    //////////////////////////////////////////////////////////////////

    if(!isset($_SESSION['user'])){

        $path = rtrim(str_replace("index.php", "", $_SERVER['SCRIPT_FILENAME']),"/");

        $users = file_exists($path . "/data/users.php");
        $projects = file_exists($path . "/data/projects.php");
        $active = file_exists($path . "/data/active.php");

        if(!$users && !$projects && !$active){
            // Installer
            require_once('components/install/view.php');
        }else{
            // Login form
            ?>

            <form id="login" method="post" style="position: fixed; width: 350px; top: 30%; left: 50%; margin-left: -175px; padding: 35px;">

                <label><span class="icon-user login-icon"></span> <?php i18n("Username"); ?></label>
                <input type="text" name="username" autofocus="autofocus" autocomplete="off">

                <label><span class="icon-lock login-icon"></span> <?php i18n("Password"); ?></label>
                <input type="password" name="password">
                
                <div class="language-selector">
                    <label><span class="icon-picture login-icon"></span> <?php i18n("Theme"); ?></label>
                    <select name="theme" id="theme">
                        <option value="default"><?php i18n("Default"); ?></option>
                        <?php
                        include 'languages/code.php';
                        foreach($themes as $theme): 
                            if(file_exists(THEMES."/" . $theme . "/theme.json")) {
                                $data = file_get_contents(THEMES."/" . $theme . "/theme.json");
                                $data = json_decode($data,true);
                            ?>
                            <option value="<?php echo $theme; ?>" <?php if($theme == THEME) { echo "selected"; } ?>><?php if($data[0]['name'] != '') { echo $data[0]['name']; } else { echo $theme; } ?></option>
                        <?php } endforeach; ?>
                    </select>
                    <label><span class="icon-language login-icon"></span> <?php i18n("Language"); ?></label>
                    <select name="language" id="language">
                        <?php
                        include 'languages/code.php';
                        foreach(glob("languages/*.php") as $filename): 
                            $lang_code = str_replace(array("languages/", ".php"), "", $filename);
                            if(!isset($languages[$lang_code])) continue;
                            $lang_disp = ucfirst(strtolower($languages[$lang_code]));
                            ?>
                            <option value="<?php echo $lang_code; ?>" <?php if ($lang_code == "en"){echo "selected";}?>><?php echo $lang_disp; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button><?php i18n("Login"); ?></button>

                <a class="show-language-selector"><?php i18n("More"); ?></a>

            </form>

            <script src="components/user/init.js"></script>
            <?php

        }

    //////////////////////////////////////////////////////////////////
    // AUTHENTICATED
    //////////////////////////////////////////////////////////////////

    }else{

    ?>

    <div id="workspace">

        <div id="sb-left" class="sidebar">
            <div id="sb-left-title">
                <a id="lock-left-sidebar" class="icon-lock icon"></a>
                <?php if (!common::isWINOS()) { ?>
                <a id="finder-quick" class="icon icon-archive"></a>
                <a id="tree-search" class="icon-search icon"></a>
                <h2 id="finder-label"> <?php i18n("Explore"); ?> </h2>
                <div id="finder-wrapper">
                   <a id="finder-options" class="icon icon-cog"></a>
                   <div id="finder-inner-wrapper">
                   <input type="text" id="finder"></input>
                   </div>
                   <ul id="finder-options-menu" class="options-menu">
                      <li class="chosen"><a data-option="left_prefix"><?php i18n("Prefix"); ?></a></li>
                      <li><a data-option="substring"><?php i18n("Substring"); ?></a></li>
                      <li><a data-option="regexp"><?php i18n("Regular expression"); ?></a></li>
                      <li><a data-action="search"><?php i18n("Search File Contents"); ?></a></li>
                   </ul>
                </div>
                <?php } ?>
            </div>

            <div class="sb-left-content">
                <div id="context-menu" data-path="" data-type="">

                    <?php

                        ////////////////////////////////////////////////////////////
                        // Load Context Menu
                        ////////////////////////////////////////////////////////////

                        foreach($context_menu as $menu_item=>$data){

                            if($data['title']=='Break'){
                                echo('<hr class="'.$data['applies-to'].'">');
                            } else{
                                echo('<a class="'.$data['applies-to'].'" onclick="'.$data['onclick'].'"><span class="'.$data['icon'].'"></span>'.get_i18n($data['title']).'</a>');
                            }

                        }
                        
                        foreach ($plugins as $plugin){
                             if(file_exists(PLUGINS . "/" . $plugin . "/plugin.json")) {
                                $pdata = file_get_contents(PLUGINS . "/" . $plugin . "/plugin.json");
                                $pdata = json_decode($pdata,true);
                                if(isset($pdata[0]['contextmenu'])) {
                                    foreach($pdata[0]['contextmenu'] as $contextmenu) {
                                        if((!isset($contextmenu['admin']) || ($contextmenu['admin']) && checkAccess()) || !$contextmenu['admin']){
                                            if(isset($contextmenu['applies-to']) && isset($contextmenu['action']) && isset($contextmenu['icon']) && isset($contextmenu['title'])) {
                                                echo('<hr class="'.$contextmenu['applies-to'].'">');
                                                echo('<a class="'.$contextmenu['applies-to'].'" onclick="'.$contextmenu['action'].'"><span class="'.$contextmenu['icon'].'"></span>'.$contextmenu['title'].'</a>');
                                            }
                                        }
                                    }
                                }
                             }
                        }

                ?>

                </div>

                <div id="file-manager"></div>

                <ul id="list-active-files"></ul>

            </div>
            
            <div id="side-projects" class="sb-left-projects">
                <div id="project-list" class="sb-project-list">
                
                    <div class="project-list-title">
                        <h2><?php i18n("Projects"); ?></h2>
                        <a id="projects-collapse" class="icon-down-dir icon" alt="<?php i18n("Collapse"); ?>"></a>
                        <?php if(checkAccess()) { ?>
                        <a id="projects-manage" class="icon-archive icon"></a>
                        <a id="projects-create" class="icon-plus icon" alt="<?php i18n("Create Project"); ?>"></a>
                        <?php } ?>
                    </div>
                    
                    <div class="sb-projects-content"></div>
                    
                </div>
            </div>

            <div class="sidebar-handle"><span>||</span></div>

        </div>

        <div id="cursor-position"><?php i18n("Ln"); ?>: 0 &middot; <?php i18n("Col"); ?>: 0</div>

        <div id="editor-region">
            <div id="editor-top-bar">
                <ul id="tab-list-active-files"> </ul>
                <div id="tab-dropdown">
                    <a id="tab-dropdown-button" class="icon-down-open"></a>
                </div>
                <div id="tab-close">
                    <a id="tab-close-button" class="icon-cancel-circled" title="<?php i18n("Close All") ?>"></a>
                </div>
                <ul id="dropdown-list-active-files"></ul>
                <div class="bar"></div>
            </div>

            <div id="root-editor-wrapper"></div>

            <div id="editor-bottom-bar">
                <a id="settings" class="ico-wrapper"><span class="icon-doc-text"></span><?php i18n("Settings"); ?></a>
                
                <?php

                    ////////////////////////////////////////////////////////////
                    // Load Plugins
                    ////////////////////////////////////////////////////////////
                    
                    foreach ($plugins as $plugin){
                         if(file_exists(PLUGINS . "/" . $plugin . "/plugin.json")) {
                            $pdata = file_get_contents(PLUGINS . "/" . $plugin . "/plugin.json");
                            $pdata = json_decode($pdata,true);
                            if(isset($pdata[0]['bottombar'])) {
                                foreach($pdata[0]['bottombar'] as $bottommenu) {
                                    if((!isset($bottommenu['admin']) || ($bottommenu['admin']) && checkAccess()) || !$bottommenu['admin']){
                                        if(isset($bottommenu['action']) && isset($bottommenu['icon']) && isset($bottommenu['title'])) {
                                            echo('<div class="divider"></div>');
                                            echo('<a onclick="'.$bottommenu['action'].'"><span class="'.$bottommenu['icon'].'"></span>'.$bottommenu['title'].'</a>');
                                        }
                                    }
                                }
                            }
                         }
                    }

                ?>
                
                <div class="divider"></div>
                <a id="split" class="ico-wrapper"><span class="icon-layout"></span><?php i18n("Split"); ?></a>
                <div class="divider"></div>
                <a id="current-mode"><span class="icon-layout"></span></a>                
                <div class="divider"></div>
                <div id="current-file"></div>
            </div>
            <div id="changemode-menu" class="options-menu">
            </div>
            <ul id="split-options-menu" class="options-menu">
              <li id="split-horizontally"><a> <?php i18n("Split Horizontally"); ?> </a></li>
              <li id="split-vertically"><a> <?php i18n("Split Vertically"); ?> </a></li>
              <li id="merge-all"><a> <?php i18n("Merge all"); ?> </a></li>
            </ul>
        </div>

        <div id="sb-right" class="sidebar">

            <div class="sidebar-handle"><span><a class="icon-menu"></a></span></div>
            <div id="sb-right-title">
                <span id="lock-right-sidebar" class="icon-switch icon"></span>
            </div>

            <div class="sb-right-content">

                <?php

                ////////////////////////////////////////////////////////////
                // Load Right Bar
                ////////////////////////////////////////////////////////////

                foreach($right_bar as $item_rb=>$data){
                    if(!isset($data['admin'])) {
                      $data['admin'] = false;
                    }
                    if($data['title']=='break'){
                        if(!$data['admin'] || $data['admin'] && checkAccess()) {
                            echo("<hr>");
                        }
                    }else if($data['title']!='break' && $data['title']!='pluginbar' && $data['onclick'] == ''){
                        if(!$data['admin'] || $data['admin'] && checkAccess()) {
                            echo("<hr><div class='sb-right-category'>".get_i18n($data['title'])."</div>");
                        }
                    }else if ($data['title']=='pluginbar'){
                        if(!$data['admin'] || $data['admin'] && checkAccess()) {
                            foreach ($plugins as $plugin){
                                 if(file_exists(PLUGINS . "/" . $plugin . "/plugin.json")) {
                                    $pdata = file_get_contents(PLUGINS . "/" . $plugin . "/plugin.json");
                                    $pdata = json_decode($pdata,true);
                                    if(isset($pdata[0]['rightbar'])) {
                                        foreach($pdata[0]['rightbar'] as $rightbar) {
                                            if((!isset($rightbar['admin']) || ($rightbar['admin']) && checkAccess()) || !$rightbar['admin']){
                                                if(isset($rightbar['action']) && isset($rightbar['icon']) && isset($rightbar['title'])) {
                                                    echo('<a onclick="'.$rightbar['action'].'"><span class="'.$rightbar['icon'].'"></span>'.get_i18n($rightbar['title']).'</a>');
                                                }
                                            }
                                        }
                                        //echo("<hr>");
                                    }
                                 }
                            }
                        }
                    } else{
                        if(!$data['admin'] || $data['admin'] && checkAccess()) {
                            echo('<a onclick="'.$data['onclick'].'"><span class="'.$data['icon'].' bigger-icon"></span>'.get_i18n($data['title']).'</a>');
                        }
                    }

                }

                ?>

            </div>

        </div>

    </div>

    <div id="modal-overlay"></div>
    <div id="modal"><div id="close-handle" class="icon-cancel" onclick="codiad.modal.unload();"></div><div id="drag-handle" class="icon-location"></div><div id="modal-content"></div></div>

    <iframe id="download"></iframe>

    <div id="autocomplete"><ul id="suggestions"></ul></div>

    <!-- ACE -->
    <script src="components/editor/ace-editor/ace.js"></script>

    <!-- COMPONENTS -->
    <?php

        //////////////////////////////////////////////////////////////////
        // LOAD COMPONENTS
        //////////////////////////////////////////////////////////////////

        // JS
        foreach($components as $component){
            if(file_exists(COMPONENTS . "/" . $component . "/init.js")){
                echo('<script src="components/'.$component.'/init.js"></script>"');
            }
        }
        
        foreach($plugins as $plugin){
            if(file_exists(PLUGINS . "/" . $plugin . "/init.js")){
                echo('<script src="plugins/'.$plugin.'/init.js"></script>"');
            }
        }

    }

    ?>

</body>
</html>
