<?php

    /*
    *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */

    require_once('../../common.php');
    require_once('class.active.php');

    $Active = new Active();

    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////

    checkSession();

    //////////////////////////////////////////////////////////////////
    // Get user's active files
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='list'){
        $Active->username = $_SESSION['user'];
        $Active->ListActive();
    }

    //////////////////////////////////////////////////////////////////
    // Add active record
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='add'){
        $Active->username = $_SESSION['user'];
        $Active->path = $_GET['path'];
        $Active->Add();
    }

    //////////////////////////////////////////////////////////////////
    // Rename
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='rename'){
        $Active->username = $_SESSION['user'];
        $Active->path = $_GET['old_path'];
        $Active->new_path = $_GET['new_path'];
        $Active->Rename();
    }

    //////////////////////////////////////////////////////////////////
    // Check if file is active
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='check'){
        $Active->username = $_SESSION['user'];
        $Active->path = $_GET['path'];
        $Active->Check();
    }

    //////////////////////////////////////////////////////////////////
    // Remove active record
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='remove'){
        $Active->username = $_SESSION['user'];
        $Active->path = $_GET['path'];
        $Active->Remove();
    }
    
    //////////////////////////////////////////////////////////////////
    // Remove all active record
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='removeall'){
        $Active->username = $_SESSION['user'];
        $Active->RemoveAll();
    }
    
    //////////////////////////////////////////////////////////////////
    // Mark file as focused
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='focused'){
        $Active->username = $_SESSION['user'];
        $Active->path = $_GET['path'];
        $Active->MarkFileAsFocused();
    }

?>