<?php

    /*
    *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */
    

    require_once('../../common.php');
    require_once('class.update.php');

    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////

    if($_GET['action']!='authenticate'){ checkSession(); }

    $update = new Update();
    
    //////////////////////////////////////////////////////////////////
    // Set Initial Version
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='init'){
        $update->Init();
    }
    
    //////////////////////////////////////////////////////////////////
    // Clear Version
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='clear'){
        if(checkAccess()) {
            $update->Clear();
        }
    }

?>
