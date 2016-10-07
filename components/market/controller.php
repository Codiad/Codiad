<?php

    /*
    *  Copyright (c) Codiad & daeks (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */


    require_once('../../common.php');
    require_once('class.market.php');

    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////

    checkSession();

    $market = new Market();
    
    //////////////////////////////////////////////////////////////////
    // Install
    //////////////////////////////////////////////////////////////////

if ($_GET['action']=='install') {
    if (checkAccess()) {
        $market->Install($_GET['type'], $_GET['name'], $_GET['repo']);
    }
}
    
    //////////////////////////////////////////////////////////////////
    // Remove
    //////////////////////////////////////////////////////////////////

if ($_GET['action']=='remove') {
    if (checkAccess()) {
        $market->Remove($_GET['type'], $_GET['name']);
    }
}
    
    //////////////////////////////////////////////////////////////////
    // Update
    //////////////////////////////////////////////////////////////////

if ($_GET['action']=='update') {
    if (checkAccess()) {
        $market->Update($_GET['type'], $_GET['name']);
    }
}
