<?php

    /*
    *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */

    require_once('../../common.php');

    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////

    checkSession();

    switch($_GET['action']){

        //////////////////////////////////////////////////////////////
        // List Projects
        //////////////////////////////////////////////////////////////

        case 'list':

            $projects_assigned = false;
            if(!checkAccess()){
            ?>
            <label><?php i18n("Restricted"); ?></label>
            <pre><?php i18n("You can not edit the user list"); ?></pre>
            <button onclick="codiad.modal.unload();return false;"><?php i18n("Close"); ?></button>
            <?php } else { ?>
            <label><?php i18n("User List"); ?></label>
            <div id="user-list">
            <table width="100%">
                <tr>
                    <th width="150"><?php i18n("Username"); ?></th>
                    <th width="85"><?php i18n("Password"); ?></th>
                    <th width="75"><?php i18n("Projects"); ?></th>
                    <th width="70"><?php i18n("Delete"); ?></th>
                </tr>
            </table>
            <div class="user-wrapper">
            <table width="100%" style="word-wrap: break-word;word-break: break-all;">    
            <?php

            // Get projects JSON data
            $users = getJSON('users.php');
            foreach($users as $user=>$data){
            ?>
            <tr>
                <td width="150"><?php echo($data['username']); ?></td>
                <td width="85"><a onclick="codiad.user.password('<?php echo($data['username']); ?>');" class="icon-flashlight bigger-icon"></a></td>
                <td width="75"><a onclick="codiad.user.projects('<?php echo($data['username']); ?>');" class="icon-archive bigger-icon"></a></td>
                <?php
                    if($_SESSION['user'] == $data['username']){
                    ?>
                    <td width="75"><a onclick="codiad.message.error('You Cannot Delete Your Own Account');" class="icon-block bigger-icon"></a></td>
                    <?php
                    }else{
                    ?>
                    <td width="70"><a onclick="codiad.user.delete('<?php echo($data['username']); ?>');" class="icon-cancel-circled bigger-icon"></a></td>
                    <?php
                    }
                    ?>
            </tr>
            <?php
            }
            ?>
            </table>
            </div>
            </div>
            <button class="btn-left" onclick="codiad.user.createNew();"><?php i18n("New Account"); ?></button>
    		<button class="btn-right" onclick="codiad.modal.unload();return false;"><?php i18n("Close"); ?></button>
            <?php
            }

            break;

        //////////////////////////////////////////////////////////////////////
        // Create New User
        //////////////////////////////////////////////////////////////////////

        case 'create':

            ?>
            <form>
            <label><?php i18n("Username"); ?></label>
            <input type="text" name="username" autofocus="autofocus" autocomplete="off">
            <label><?php i18n("Password"); ?></label>
            <input type="password" name="password1">
            <label><?php i18n("Confirm Password"); ?></label>
            <input type="password" name="password2">
            <button class="btn-left"><?php i18n("Create Account"); ?></button>
			<button class="btn-right" onclick="codiad.user.list();return false;"><?php i18n("Cancel"); ?></button>
            <form>
            <?php
            break;

        //////////////////////////////////////////////////////////////////////
        // Set Project Access
        //////////////////////////////////////////////////////////////////////

        case 'projects':

            // Get project list
            $projects = getJSON('projects.php');
            // Get control list (if exists)
            $projects_assigned = false;
            if(file_exists(BASE_PATH . "/data/" . $_GET['username'] . '_acl.php')){
                $projects_assigned = getJSON($_GET['username'] . '_acl.php');
            }

        ?>
            <form>
            <input type="hidden" name="username" value="<?php echo($_GET['username']); ?>">
            <label><?php i18n("Project Access for "); ?><?php echo(ucfirst($_GET['username'])); ?></label>
            <select name="access_level" onchange="if($(this).val()=='0'){ $('#project-selector').slideUp(300); }else{ $('#project-selector').slideDown(300).css({'overflow-y':'scroll'}); }">
                <option value="0" <?php if(!$projects_assigned){ echo('selected="selected"'); } ?>><?php i18n("Access ALL Projects"); ?></option>
                <option value="1" <?php if($projects_assigned){ echo('selected="selected"'); } ?>><?php i18n("Only Selected Projects"); ?></option>
            </select>
            <div id="project-selector" <?php if(!$projects_assigned){ echo('style="display: none;"'); }  ?>>
                <table>
                <?php
                    // Build list
                    foreach($projects as $project=>$data){
                        $sel = '';
                        if($projects_assigned && in_array($data['path'],$projects_assigned)){ $sel = 'checked="checked"'; }
                        echo('<tr><td width="5"><input type="checkbox" name="project" '.$sel.' id="'.$data['path'].'" value="'.$data['path'].'"></td><td>'.$data['name'].'</td></tr>');
                    }
                ?>
                </table>
            </div>
            <button class="btn-left"><?php i18n("Confirm"); ?></button>
			<button class="btn-right" onclick="codiad.user.list();return false;"><?php i18n("Close"); ?></button>
            <?php
            break;

        //////////////////////////////////////////////////////////////////////
        // Delete User
        //////////////////////////////////////////////////////////////////////

        case 'delete':

        ?>
            <form>
            <input type="hidden" name="username" value="<?php echo($_GET['username']); ?>">
            <label><?php i18n("Confirm User Deletion"); ?></label>
            <pre><?php i18n("Account:"); ?> <?php echo($_GET['username']); ?></pre>
            <button class="btn-left"><?php i18n("Confirm"); ?></button>
			<button class="btn-right" onclick="codiad.user.list();return false;"><?php i18n("Cancel"); ?></button>
            <?php
            break;

        //////////////////////////////////////////////////////////////////////
        // Change Password
        //////////////////////////////////////////////////////////////////////

        case 'password':

            if($_GET['username']=='undefined'){
                $username = $_SESSION['user'];
            }else{
                $username = $_GET['username'];
            }

        ?>
            <form>
            <input type="hidden" name="username" value="<?php echo($username); ?>">
            <label><?php i18n("New Password"); ?></label>
            <input type="password" name="password1" autofocus="autofocus">
            <label><?php i18n("Confirm Password"); ?></label>
            <input type="password" name="password2">
          <button class="btn-left"><?php i18n("Change %{username}%&apos;s Password", array("username" => ucfirst($username))) ?></button>
			<button class="btn-right" onclick="codiad.modal.unload();return false;"><?php i18n("Cancel"); ?></button>
            <?php
            break;

    }

?>
