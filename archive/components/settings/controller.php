<?php

    /*
    *  Copyright (c) Codiad & Andr3as, distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */

    require_once('../../common.php');
    require_once('class.settings.php');

if (!isset($_GET['action'])) {
    die(formatJSEND("error", "Missing parameter"));
}
    
    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////

    checkSession();

    $Settings = new Settings();

    //////////////////////////////////////////////////////////////////
    // Save User Settings
    //////////////////////////////////////////////////////////////////

if ($_GET['action']=='save') {
    if (!isset($_POST['settings'])) {
        die(formatJSEND("error", "Missing settings"));
    }

    $Settings->username = $_SESSION['user'];
    $Settings->settings = json_decode($_POST['settings'], true);
    $Settings->Save();
}

    //////////////////////////////////////////////////////////////////
    // Load User Settings
    //////////////////////////////////////////////////////////////////

if ($_GET['action']=='load') {
    $Settings->username = $_SESSION['user'];
    $Settings->Load();
}
