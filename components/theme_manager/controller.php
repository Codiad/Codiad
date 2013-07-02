<?php

    /*
    *  Copyright (c) Codiad & daeks (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */


    require_once('../../common.php');
    require_once('class.theme_manager.php');

    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////

    checkSession();

    $Theme_manager = new Theme_manager();
    
    //////////////////////////////////////////////////////////////////
    // Deactivate Theme
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='deactivate'){
        if(checkAccess()) {
            $Theme_manager->Deactivate($_GET['name']);
        }
    }
    
    //////////////////////////////////////////////////////////////////
    // Activate Theme
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='activate'){
        if(checkAccess()) {
            $Theme_manager->Activate($_GET['name']);
        }
    }
    
    //////////////////////////////////////////////////////////////////
    // Install Theme
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='install'){
        if(checkAccess()) {
            $Theme_manager->Install($_GET['name'], $_GET['repo']);
        }
    }
    
    //////////////////////////////////////////////////////////////////
    // Remove Theme
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='remove'){
        if(checkAccess()) {
            $Theme_manager->Remove($_GET['name']);
        }
    }
    
    //////////////////////////////////////////////////////////////////
    // Update Theme
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='update'){
        if(checkAccess()) {
            $Theme_manager->Update($_GET['name']);
        }
    }

?>