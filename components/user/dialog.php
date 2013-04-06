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
            <label>Restricted</label>
            <pre>You can not edit the user list</pre>
            <button onclick="codiad.modal.unload();return false;">Close</button>
            <?php } else { ?>
            <label>User List</label>
            <div id="user-list">
            <table width="100%">
                <tr>
                    <th>Login</th>
                    <th width="5">Password</th>
                    <th width="5">Projects</th>
                    <th width="5">Delete</th>
                </tr>
            <?php
        
            // Get projects JSON data
            $users = getJSON('users.php');
            foreach($users as $user=>$data){        
            ?>
            <tr>
                <td><?php echo($data['username']); ?></td>
                <td><a onclick="codiad.user.password('<?php echo($data['username']); ?>');" class="icon-flashlight bigger-icon"></a></td>
                <td><a onclick="codiad.user.projects('<?php echo($data['username']); ?>');" class="icon-archive bigger-icon"></a></td>
                <?php
                    if($_SESSION['user'] == $data['username']){
                    ?>
                    <td><a onclick="codiad.message.error('You Cannot Delete Your Own Account');" class="icon-block bigger-icon"></a></td>
                    <?php
                    }else{
                    ?>
                    <td><a onclick="codiad.user.delete('<?php echo($data['username']); ?>');" class="icon-cancel-circled bigger-icon"></a></td>
                    <?php
                    }
                    ?>
            </tr>
            <?php
            }
            ?>
            </table>
            </div>
            <button class="btn-left" onclick="codiad.user.createNew();">New Account</button><button class="btn-right" onclick="codiad.modal.unload();return false;">Close</button>
            <?php
            }
            
            break;
            
        //////////////////////////////////////////////////////////////////////
        // Create New User
        //////////////////////////////////////////////////////////////////////
        
        case 'create':
        
            ?>
            <form>
            <label>Username</label>
            <input type="text" name="username" autofocus="autofocus" autocomplete="off">
            <label>Password</label>
            <input type="password" name="password1">
            <label>Confirm Password</label>
            <input type="password" name="password2">
            <button class="btn-left">Create Account</button><button class="btn-right" onclick="codiad.user.list();return false;">Cancel</button>
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
            <label>Project Access for <?php echo(ucfirst($_GET['username'])); ?></label>
            <select name="access_level" onchange="if($(this).val()=='0'){ $('#project-selector').slideUp(300); }else{ $('#project-selector').slideDown(300).css({'overflow-y':'scroll'}); }">
                <option value="0" <?php if(!$projects_assigned){ echo('selected="selected"'); } ?>>Access ALL Projects</option>
                <option value="1" <?php if($projects_assigned){ echo('selected="selected"'); } ?>>Only Selected Projects</option>
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
            <button class="btn-left">Confirm</button><button class="btn-right" onclick="codiad.user.list();return false;">Close</button>
            <?php
            break;
        
        //////////////////////////////////////////////////////////////////////
        // Delete User
        //////////////////////////////////////////////////////////////////////
        
        case 'delete':
        
        ?>
            <form>
            <input type="hidden" name="username" value="<?php echo($_GET['username']); ?>">
            <label>Confirm User Deletion</label>
            <pre>Account: <?php echo($_GET['username']); ?></pre>
            <button class="btn-left">Confirm</button><button class="btn-right" onclick="codiad.user.list();return false;">Cancel</button>
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
            <label>New Password</label>
            <input type="password" name="password1" autofocus="autofocus">
            <label>Confirm Password</label>
            <input type="password" name="password2">
            <button class="btn-left">Change <?php echo(ucfirst($username)); ?>&apos;s Password</button><button class="btn-right" onclick="codiad.modal.unload();return false;">Cancel</button>
            <?php
            break;
        
    }
    
?>
