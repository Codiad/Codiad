<?php

require_once('common.php');

// Context Menu
$context_menu = file_get_contents(COMPONENTS . "/filemanager/context_menu.json");
$context_menu = json_decode($context_menu,true);

// Right Bar
$right_bar = file_get_contents(COMPONENTS . "/right_bar.json");
$right_bar = json_decode($right_bar,true);

// Components
$components = array();
//read all directories from components
$allFiles = scandir(COMPONENTS);
foreach ($allFiles as $fname){
    if($fname == '.' || $fname == '..' ){
        continue;
    }
    if(is_dir(COMPONENTS.'/'.$fname)){
        $components[] = $fname;
    }
}

// Plugins
$plugins = array();
if(!file_exists(DATA . '/plugins.php')) {
    //read all directories from plugins
    $allFiles = scandir(PLUGINS);
    foreach ($allFiles as $fname){
        if($fname == '.' || $fname == '..' ){
            continue;
        }
        if(is_dir(PLUGINS.'/'.$fname)){
            $plugins[] = $fname;
        }
    }
} else {
    $plugins = getJSON('plugins.php');
}

?>
<!doctype html>

<head>
    <meta charset="utf-8">
    <title>CODIAD</title>
    <?php
    // Load System CSS Files
    $stylesheets = array("jquery.toastmessage.css","reset.css","fonts.css","screen.css");
   
    foreach($stylesheets as $sheet){
        if(file_exists(THEMES . "/". THEME . "/".$sheet)){
            echo('<link rel="stylesheet" href="themes/'.THEME.'/'.$sheet.'">');
        } else {
            echo('<link rel="stylesheet" href="themes/default/'.$sheet.'">');
        }
    }
    
    // Load Component CSS Files    
    foreach($components as $component){
        if(file_exists(THEMES . "/". THEME . "/" . $component . "/screen.css")){
            echo('<link rel="stylesheet" href="themes/'.THEME.'/'.$component.'/screen.css">');
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
        if(file_exists(THEMES . "/". THEME . "/" . $plugin . "/screen.css")){
            echo('<link rel="stylesheet" href="themes/'.THEME.'/'.$plugin.'/screen.css">');
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
    <link rel="icon"       href="favicon.ico" type="image/x-icon" />
</head>

<body>
    <script>
    var i18n = (function(lang) {
        return function(word) {
            return (word in lang) ? lang[word] : word;
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

                <label><span class="icon-user login-icon"></span> Username</label>
                <input type="text" name="username" autofocus="autofocus" autocomplete="off">

                <label><span class="icon-lock login-icon"></span> Password</label>
                <input type="password" name="password">
                
                <div class="language-selector">
                    <label><span class="icon-language login-icon"></span> Language</label>
                    <select name="language">
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
                
                <button>Login</button>

                <a class="show-language-selector">Language</a>

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
                <h2 id="finder-label"> Explore </h2>
                <div id="finder-wrapper">
                   <a id="finder-options" class="icon icon-cog"></a>
                   <div id="finder-inner-wrapper">
                   <input type="text" id="finder"></input>
                   </div>
                   <ul id="finder-options-menu" class="options-menu">
                      <li class="chosen"><a data-option="left_prefix">Prefix</a></li>
                      <li><a data-option="substring">Substring</a></li>
                      <li><a data-option="regexp">Regular expression</a></li>
                      <li><a data-action="search">Search File Contents</a></li>
                   </ul>
                </div>
                <a id="lock-left-sidebar" class="icon-lock icon"></a>
                <a id="finder-quick" class="icon icon-archive"></a>
                <a id="tree-search" class="icon-search icon"></a>
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
                                echo('<a class="'.$data['applies-to'].'" onclick="'.$data['onclick'].'"><span class="'.$data['icon'].'"></span>'.$data['title'].'</a>');
                            }

                        }
                        
                        foreach ($plugins as $plugin){
                             if(file_exists(PLUGINS . "/" . $plugin . "/plugin.json")) {
                                $pdata = file_get_contents(PLUGINS . "/" . $plugin . "/plugin.json");
                                $pdata = json_decode($pdata,true);
                                if(isset($pdata[0]['contextmenu'])) {
                                    foreach($pdata[0]['contextmenu'] as $contextmenu) {
                                        if(isset($contextmenu['applies-to']) && isset($contextmenu['action']) && isset($contextmenu['icon']) && isset($contextmenu['title'])) {
                                            echo('<hr class="'.$contextmenu['applies-to'].'">');
                                            echo('<a class="'.$contextmenu['applies-to'].'" onclick="'.$contextmenu['action'].'"><span class="'.$contextmenu['icon'].'"></span>'.$contextmenu['title'].'</a>');
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
                        <h2>Projects</h2>
                        <a id="projects-collapse" class="icon-down-dir icon" alt="Collapse"></a>
                        <?php if(checkAccess()) { ?>
                        <a id="projects-create" class="icon-plus icon" alt="Create Project"></a>
                        <?php } ?>
                    </div>
                    
                    <div class="sb-projects-content"></div>
                    
                </div>
            </div>

            <div class="sidebar-handle"><span>||</span></div>

        </div>

        <div id="cursor-position">Ln: 0 &middot; Col: 0</div>

        <div id="editor-region">
            <div id="editor-top-bar">
                <ul id="tab-list-active-files"> </ul>
                <div id="tab-dropdown">
                    <a id="tab-dropdown-button" class="icon-down-open"></a>
                </div>
                <div id="tab-close">
                    <a id="tab-close-button" class="icon-cancel-circled"></a>
                </div>
                <ul id="dropdown-list-active-files"></ul>
                <div class="bar"></div>
            </div>

            <div id="root-editor-wrapper"></div>

            <div id="editor-bottom-bar">
                <a id="settings" class="ico-wrapper"><span class="icon-doc-text"></span>Settings</a>
                <div class="divider"></div>
                <a id="split" class="ico-wrapper"><span class="icon-layout"></span>Split</a>
                <div class="divider"></div>
                <a id="current-mode"><span class="icon-layout"></span></a>
                <div class="divider"></div>
                <div id="current-file"></div>
            </div>
            <div id="changemode-menu" class="options-menu">
            </div>
            <ul id="split-options-menu" class="options-menu">
              <li id="split-horizontally"><a> Split Horizontally </a></li>
              <li id="split-vertically"><a> Split Vertically </a></li>
              <li id="merge-all"><a> Merge all </a></li>
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

                    if($data['title']=='break'){
                        echo("<hr>");
                    } else if ($data['title']=='plugins'){
                        echo("<hr>");
                        foreach ($plugins as $plugin){
                             if(file_exists(PLUGINS . "/" . $plugin . "/plugin.json")) {
                                $pdata = file_get_contents(PLUGINS . "/" . $plugin . "/plugin.json");
                                $pdata = json_decode($pdata,true);
                                if(isset($pdata[0]['rightbar'])) {
                                    foreach($pdata[0]['rightbar'] as $rightbar) {
                                        if(isset($rightbar['action']) && isset($rightbar['icon']) && isset($rightbar['title'])) {
                                            echo('<a onclick="'.$rightbar['action'].'"><span class="'.$rightbar['icon'].'"></span>'.$rightbar['title'].'</a>');
                                        }
                                    }
                                    echo("<hr>");
                                }
                             }
                        }
                    } else{
                        echo('<a onclick="'.$data['onclick'].'"><span class="'.$data['icon'].' bigger-icon"></span>'.get_i18n($data['title']).'</a>');
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
