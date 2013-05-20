<?php

    /*
    *  Copyright (c) Codiad & daeks (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */


    require_once('../../common.php');
    require_once('class.plugin_manager.php');

    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////

    checkSession();

    $Plugin_manager = new Plugin_manager();
    
    //////////////////////////////////////////////////////////////////
    // Deactivate Plugin
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='deactivate'){
        if(checkAccess()) {
            $Plugin_manager->Deactivate($_GET['name']);
        }
    }
    
    //////////////////////////////////////////////////////////////////
    // Activate Plugin
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='activate'){
        if(checkAccess()) {
            $Plugin_manager->Activate($_GET['name']);
        }
    }
    
    //////////////////////////////////////////////////////////////////
    // Install Plugin
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='install'){
        if(checkAccess()) {
            $Plugin_manager->Install($_GET['name'], $_GET['repo']);
        }
    }
    
    //////////////////////////////////////////////////////////////////
    // Remove Plugin
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='remove'){
        if(checkAccess()) {
            $Plugin_manager->Remove($_GET['name']);
        }
    }
    
    //////////////////////////////////////////////////////////////////
    // Update Plugin
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='update'){
        if(checkAccess()) {
            $Plugin_manager->Update($_GET['name']);
        }
    }

?>