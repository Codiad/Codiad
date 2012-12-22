<?php

    /*
    *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */

    require_once('../../config.php');

    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////

    checkSession();

    //////////////////////////////////////////////////////////////////
    // Get Action
    //////////////////////////////////////////////////////////////////

    /*if(!empty($_GET['action'])){ $action = $_GET['action']; }
    else{ exit('{"status":"error","data":{"error":"No Action Specified"}}'); }*/

    if (isset($_POST["change"]) && !empty($_POST["change"])) {
        $data = json_decode($_POST["change"]);
        echo json_encode($data);
    }

?>

