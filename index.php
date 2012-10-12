<?php if(file_exists('config.php')){ require_once('config.php'); } ?>
<!doctype html>

<head>
    <meta charset="utf-8">
    <title>CODIAD</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/screen.css">
</head>

<body>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script>!window.jQuery && document.write(unescape('%3Cscript src="js/jquery-1.7.2.min.js"%3E%3C/script%3E'));</script>
    <script src="js/jquery-ui-1.8.23.custom.min.js"></script>
    <script src="js/jquery.css3.min.js"></script>
    <script src="js/jquery.easing.js"></script>
    <script src="js/localstorage.js"></script>
    <script src="js/system.js"></script>
    <script src="js/sidebars.js"></script>
    <script src="js/modal.js"></script>
    <script src="js/message.js"></script>
    <script src="js/jsend.js"></script>
    <div id="message"></div>

    <?php

    //////////////////////////////////////////////////////////////////
    // NOT LOGGED IN
    //////////////////////////////////////////////////////////////////

    if(!isset($_SESSION['user'])){

        $path = rtrim(str_replace("index.php", "", $_SERVER['PHP_SELF']),"/");

        $users = file_exists($_SERVER['DOCUMENT_ROOT'] . $path . "/data/users.php");
        $projects = file_exists($_SERVER['DOCUMENT_ROOT'] . $path . "/data/projects.php");
        $active = file_exists($_SERVER['DOCUMENT_ROOT'] . $path . "/data/active.php");

        if(!$users && !$projects && !$active){
            // Installer
            require_once('components/install/view.php');
        }else{
            // Login form
            ?>

            <form id="login" method="post" style="position: fixed; width: 350px; top: 30%; left: 50%; margin-left: -175px; padding: 35px;">

                <label><span class="icon">+</span> Username</label>
                <input type="text" name="username" autofocus="autofocus" autocomplete="off">

                <label><span class="icon">U</span> Password</label>
                <input type="password" name="password">

                <button>Login</button>

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

            <div class="sb-left-content">

                <a id="lock-left-sidebar" class="icon">U</a>

                <div id="context-menu" data-path="" data-type="">

                    <?php

                        ////////////////////////////////////////////////////////////
                        // Load Context Menu
                        ////////////////////////////////////////////////////////////

                        $context_menu = file_get_contents(COMPONENTS . "/filemanager/context_menu.json");
                        $context_menu = json_decode($context_menu,true);
                        foreach($context_menu as $menu_item=>$data){

                            if($data['title']=='Break'){
                                echo('<hr class="'.$data['applies-to'].'">');
                            }else{
                                echo('<a class="'.$data['applies-to'].'" onclick="'.$data['onclick'].'"><span class="icon">'.$data['icon'].'</span>'.$data['title'].'</a>');
                            }

                        }

                ?>

                </div>

                <div id="file-manager"></div>

                <ul id="active-files"></ul>

            </div>

            <div class="sidebar-handle"><span>||</span></div>

        </div>

        <div id="cursor-position">Ln: 0 &middot; Col: 0</div>

        <div id="editor-region">
            <div id="editor"></div>
            <div id="editor-bottom-bar">
                <a id="settings"><span class="icon">l</span>Settings</a>
                <div id="divider"></div>
                <div id="current-file"></div>
            </div>

        </div>



        <div id="sb-right" class="sidebar">

            <div class="sidebar-handle"><span>||</span></div>

            <div class="sb-right-content">

                <?php

                ////////////////////////////////////////////////////////////
                // Load Right Bar
                ////////////////////////////////////////////////////////////

                $right_bar = file_get_contents(COMPONENTS . "/right_bar.json");
                $right_bar = json_decode($right_bar,true);
                foreach($right_bar as $item_rb=>$data){

                    if($data['title']=='break'){
                        echo("<hr>");
                    }else{
                        echo('<a onclick="'.$data['onclick'].'"><span class="icon">'.$data['icon'].'</span>'.$data['title'].'</a>');
                    }

                }

                ?>

            </div>

        </div>

    </div>

    <div id="modal-overlay"></div>
    <div id="modal"><div id="drag-handle" class="icon">0</div><div id="modal-content"></div></div>

    <iframe id="download"></iframe>

    <!-- ACE -->
    <script src="components/editor/ace-editor/ace.js"></script>

    <!-- COMPONENTS -->
    <?php

        //////////////////////////////////////////////////////////////////
        // LOAD COMPONENTS
        //////////////////////////////////////////////////////////////////

        $components = file_get_contents(COMPONENTS . "/load.json");
        $components = json_decode($components,true);

        // JS
        foreach($components as $component){
            if(file_exists(COMPONENTS . "/" . $component . "/init.js")){
                echo('<script src="components/'.$component.'/init.js"></script>"');
            }
        }


        // CSS
        foreach($components as $component){
            if(file_exists(COMPONENTS . "/" . $component . "/screen.css")){
                echo('<link rel="stylesheet" href="components/'.$component.'/screen.css">');
            }
        }

    }

    ?>

</body>
</html>