<?php

    /*
    *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */

    require_once('../../common.php');
    require_once('../project/class.project.php');

    //$Git = new Git();

    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////

    checkSession();

    //////////////////////////////////////////////////////////////////
    // Get user's active files
    //////////////////////////////////////////////////////////////////


if ($_GET['action']=='stash') {
    $project_path = $_SESSION['project'];
    $stash = "";
    if (preg_match('/[a-zA-Z\-_\/~]+/', $project_path)) {
        $stash = shell_exec("cd ../../workspace/$project_path ; git stash");
    }

    //preg_replace('/\n/','<br>',$diff);
    echo formatJSEND("success", array("path"=>$project_path,"stash"=>$stash));
}

if ($_GET['action']=='diff') {
    $project_path = $_SESSION['project'];
    $diff = "";
    if (preg_match('/[a-zA-Z\-_\/~]+/', $project_path)) {
        $diff = shell_exec("cd ../../workspace/$project_path ; git diff");
    }

    //preg_replace('/\n/','<br>',$diff);
    echo formatJSEND("success", array("path"=>$project_path,"diff"=>$diff));
}

