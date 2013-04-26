<?php

    /*
    *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */


    require_once('../../common.php');
    require_once('class.tester.php');

    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////

    checkSession();

    $Tester = new Tester();
    
    //////////////////////////////////////////////////////////////////
    // Pull Repo
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='pull'){
        $Tester->root = $_GET['root'];
        $Tester->pull = $_GET['pull'];
        $Tester->Pull();
    }
   
?>