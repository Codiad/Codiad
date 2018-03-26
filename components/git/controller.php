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

if ($_GET['action']=='submit') {
    $project_path = $_SESSION['project'];
    $submit = "";
    if (preg_match('/[a-zA-Z\-_\/~]+/', $project_path) && isset($_SESSION['user'])) {
        $date = new DateTime();
	$ts = $date->format('Y-m-d H:i:s');
        $command = "cd ../../workspace/$project_path ; eval $(ssh-agent -s) ; ssh-add /etc/apache2/private/id_rsa ; git add . ; git commit -m \"".$_SESSION['user']." submitted these changes at ".$ts."\"; BRANCH_NAME=$(git rev-parse --abbrev-ref HEAD) ; git push origin \$BRANCH_NAME ; git push origin master";
        $submit = shell_exec($command);
    }

    //preg_replace('/\n/','<br>',$diff);
    echo formatJSEND("success", array("path"=>$project_path,"submit"=>$submit));
}

if ($_GET['action']=='stash') {
    $project_path = $_SESSION['project'];
    $stash = "";
    if (preg_match('/[a-zA-Z\-_\/~]+/', $project_path) && isset($_SESSION['user'])) {
        $date = new DateTime();
	$ts = $date->getTimestamp();
        $branch_name = $_SESSION['user'].'_'.$ts;
        $stash = shell_exec("cd ../../workspace/$project_path ; eval $(ssh-agent -s) ; ssh-add /etc/apache2/private/id_rsa ; git stash ; git fetch ; git checkout origin/master ; git branch -D ".$branch_name." ; git checkout origin/master ; git checkout -b ".$branch_name." ; git merge origin/master");
error_log($stash);
    }

    //preg_replace('/\n/','<br>',$diff);
    echo formatJSEND("success", array("path"=>$project_path,"stash"=>$stash));
}

if ($_GET['action']=='diff') {
    $project_path = $_SESSION['project'];
    $diff = "";
    if (preg_match('/[a-zA-Z\-_\/~]+/', $project_path) && isset($_SESSION['user'])) {
        $diff = shell_exec("cd ../../workspace/$project_path ; git diff HEAD~1");
    }

    //preg_replace('/\n/','<br>',$diff);
    echo formatJSEND("success", array("path"=>$project_path,"diff"=>$diff));
}

