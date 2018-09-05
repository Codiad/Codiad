<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

require_once('../../common.php');

class Git extends Common
{
    public static function save_wip()
    {
        $project_path = $_SESSION['project'];
        $submit = "";
        if (preg_match('/[a-zA-Z\-_\/~]+/', $project_path) && isset($_SESSION['user'])) {
            $date = new DateTime();
        $ts = $date->format('Y-m-d H:i:s');
        $wip_branch = $_SESSION['user']."_wip";
        $command = "cd ../../workspace/$project_path ; eval $(ssh-agent -s) ; ssh-add /etc/apache2/private/id_rsa ; BRANCH_NAME=$(git rev-parse --abbrev-ref HEAD) ; git branch -D $wip_branch ; git checkout -b $wip_branch ; git add . ; git commit -m \"".$_SESSION['user']." saved these changes at ".$ts."\"; git checkout \$BRANCH_NAME ; git checkout $wip_branch ./* ; git reset ; git push -f origin $wip_branch";
            $save = shell_exec($command);
        }
    }
}
