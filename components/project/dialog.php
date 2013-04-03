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
                    <?php if(!$projects_assigned){ ?><th width="5">Delete</th><?php } ?>
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
                    <td>/<?php echo($data['path']); ?></td>
                    <?php
                        if(!$projects_assigned){
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
            <?php if(!$projects_assigned){ ?><button class="btn-left" onclick="codiad.project.create();">New Project</button><?php } ?><button class="<?php if(!$projects_assigned){ echo('btn-right'); } ?>" onclick="codiad.modal.unload();return false;">Close</button>
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
            
            <!-- Clone From GitHub -->
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
            <!-- /Clone From GitHub -->
            
            <button class="btn-left">Create Project</button><button onclick="$('#git-clone').slideToggle(300); $(this).hide(); return false;" class="btn-mid">...From Git Repo</button><button class="btn-right" onclick="codiad.project.list();return false;">Cancel</button>
            <form>
            <?php
            break;
            
        //////////////////////////////////////////////////////////////////////
        // Delete Project
        //////////////////////////////////////////////////////////////////////
        
        case 'delete':
        
        ?>
            <form>
            <input type="hidden" name="project_path" value="/<?php echo($_GET['path']); ?>">
            <label>Confirm Project Deletion</label>
            <pre>Name: <?php echo($_GET['name']); ?>, Path: /<?php echo($_GET['path']); ?></pre>
            <button class="btn-left">Confirm</button><button class="btn-right" onclick="codiad.project.list();return false;">Cancel</button>
            <?php
            break;
        
    }
    
?>
        
