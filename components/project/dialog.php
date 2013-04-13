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
        // List Projects Mini Sidebar
        //////////////////////////////////////////////////////////////
        case 'sidelist':
            
            // Get access control data
            $projects_assigned = false;
            if(file_exists(BASE_PATH . "/data/" . $_SESSION['user'] . '_acl.php')){
                $projects_assigned = getJSON($_SESSION['user'] . '_acl.php');
            }
            
            ?>  
                    
            <ul>
            
            <?php
            
            // Get projects JSON data
            $projects = getJSON('projects.php');
            sort($projects);
            foreach($projects as $project=>$data){
                $show = true;
                if($projects_assigned && !in_array($data['path'],$projects_assigned)){ $show=false; }
                if($show){
                ?>
                <li onclick="codiad.project.open('<?php echo($data['path']); ?>');"><div class="icon-archive icon"></div><?php echo($data['name']); ?></li>
                
                <?php
                }
            } 
            ?>
            
            </ul>
                    
            <?php
            
            break;
        
        //////////////////////////////////////////////////////////////
        // List Projects
        //////////////////////////////////////////////////////////////
        
        case 'list':
        
            // Get access control data
            $projects_assigned = false;
            if(file_exists(BASE_PATH . "/data/" . $_SESSION['user'] . '_acl.php')){
                $projects_assigned = getJSON($_SESSION['user'] . '_acl.php');
            }
            
            ?>
            <label>Project List</label>
            <div id="project-list">
            <table width="100%">
                <tr>
                    <th width="5">Open</th>
                    <th>Project Name</th>
                    <th>Path</th>
                    <?php if(checkAccess()){ ?><th width="5">Delete</th><?php } ?>
                </tr>
            <?php
            
            // Get projects JSON data
            $projects = getJSON('projects.php');
            sort($projects);
            foreach($projects as $project=>$data){
                $show = true;
                if($projects_assigned && !in_array($data['path'],$projects_assigned)){ $show=false; }
                if($show){
                ?>
                <tr>
                    <td><a onclick="codiad.project.open('<?php echo($data['path']); ?>');" class="icon-folder bigger-icon"></a></td>
                    <td><?php echo($data['name']); ?></td>
                    <td><?php echo($data['path']); ?></td>
                    <?php
                        if(checkAccess()){
                            if($_SESSION['project'] == $data['path']){
                            ?>
                            <td><a onclick="codiad.message.error('Active Project Cannot Be Removed');" class="icon-block bigger-icon"></a></td>
                            <?php
                            }else{
                            ?>
                            <td><a onclick="codiad.project.delete('<?php echo($data['name']); ?>','<?php echo($data['path']); ?>');" class="icon-cancel-circled bigger-icon"></a></td>
                            <?php
                            }
                        }
                    ?>
                </tr>
                <?php
                }
            }
            ?>
            </table>
            </div>
            <?php if(checkAccess()){ ?><button class="btn-left" onclick="codiad.project.create();">New Project</button><?php } ?><button class="<?php if(checkAccess()){ echo('btn-right'); } ?>" onclick="codiad.modal.unload();return false;">Close</button>
            <?php
            
            break;
            
        //////////////////////////////////////////////////////////////////////
        // Create New Project
        //////////////////////////////////////////////////////////////////////
        
        case 'create':
        
            ?>
            <form>
            <label>Project Name</label>
            <input name="project_name" autofocus="autofocus" autocomplete="off">
            <?php if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') { ?>
            <label>Folder Name or Absolute Path</label>
            <input name="project_path" autofocus="off" autocomplete="off">
            <?php } else { ?>
            <input type="hidden" name="project_path">
            <?php }  ?>
            
            <!-- Clone From GitHub -->
            <div style="width: 500px;">
            <table class="hide" id="git-clone">
                <tr>
                    <td>
                        <label>Git Repository</label>
                        <input name="git_repo">
                    </td>
                    <td width="5%">&nbsp;</td>
                    <td width="25%">
                        <label>Branch</label>
                        <input name="git_branch" value="master">
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="note">Note: This will only work if your Git repo DOES NOT require interactive authentication and your server has git installed.</td>
                </tr>
            </table>
            </div>
            <!-- /Clone From GitHub --><?php
                $action = 'codiad.project.list();';
                if($_GET['close'] == 'true') {
                    $action = 'codiad.modal.unload();';
                } 
            ?>           
            <button class="btn-left">Create Project</button><button onclick="$('#git-clone').slideDown(300); $(this).hide(); return false;" class="btn-mid">...From Git Repo</button><button class="btn-right" onclick="<?php echo $action;?>return false;">Cancel</button>
            <form>
            <?php
            break;
            
        //////////////////////////////////////////////////////////////////
        // Rename
        //////////////////////////////////////////////////////////////////
        case 'rename':
        ?>
        <form>
        <input type="hidden" name="project_path" value="<?php echo($_GET['path']); ?>">
        <label><span class="icon-pencil"></span>Rename Project</label>    
        <input type="text" name="project_name" autofocus="autofocus" autocomplete="off" value="<?php echo($_GET['project_name']); ?>">  
        <button class="btn-left">Rename</button>&nbsp;<button class="btn-right" onclick="codiad.modal.unload(); return false;">Cancel</button>
        <form>
        <?php
        break;       
            
        //////////////////////////////////////////////////////////////////////
        // Delete Project
        //////////////////////////////////////////////////////////////////////
        
        case 'delete':
        
        ?>
            <form>
            <input type="hidden" name="project_path" value="<?php echo($_GET['path']); ?>">
            <label>Confirm Project Deletion</label>
            <pre>Name: <?php echo($_GET['name']); ?>, Path: <?php echo($_GET['path']); ?></pre>
            <table>
            <tr><td width="5"><input type="checkbox" name="delete" id="delete" value="true"></td><td>Delete Project Files</td></tr>
            <tr><td width="5"><input type="checkbox" name="follow" id="follow" value="true"></td><td>Follow Symbolic Links </td></tr>
            </table>
            <button class="btn-left">Confirm</button><button class="btn-right" onclick="codiad.project.list();return false;">Cancel</button>
            <?php
            break;
        
    }
    
?>
        
