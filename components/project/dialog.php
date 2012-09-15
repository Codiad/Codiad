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

    switch($_GET['action']){
    
        //////////////////////////////////////////////////////////////
        // List Projects
        //////////////////////////////////////////////////////////////
        
        case 'list':
        
            ?>
            <label>Project List</label>
            <div id="project-list">
            <table width="100%">
                <tr>
                    <th width="5">Open</th>
                    <th>Project Name</th>
                    <th>Path</th>
                    <th width="5">Delete</th>
                </tr>
            <?php
        
            // Get projects JSON data
            $projects = getJSON('projects.php');
            foreach($projects as $project=>$data){        
            ?>
            <tr>
                <td><a onclick="project.open('<?php echo($data['path']); ?>');" class="icon">s</a></td>
                <td><?php echo($data['name']); ?></td>
                <td>/<?php echo($data['path']); ?></td>
                <?php
                    if($_SESSION['project'] == $data['path']){
                    ?>
                    <td><a onclick="message.error('Active Project Cannot Be Removed');" class="icon">^</a></td>
                    <?php
                    }else{
                    ?>
                    <td><a onclick="project.delete('<?php echo($data['name']); ?>','<?php echo($data['path']); ?>');" class="icon">[</a></td>
                    <?php
                    }
                    ?>
            </tr>
            <?php
            }
            ?>
            </table>
            </div>
            <button class="btn-left" onclick="project.create();">New Project</button><button class="btn-right" onclick="modal.unload();return false;">Close</button>
            <?php
            
            break;
            
        //////////////////////////////////////////////////////////////////////
        // Create New Project
        //////////////////////////////////////////////////////////////////////
        
        case create:
        
            ?>
            <form>
            <label>Project Name</label>
            <input name="project_name" autofocus="autofocus" autocomplete="off">
            <button class="btn-left">Create Project</button><button class="btn-right" onclick="project.list();return false;">Cancel</button>
            <form>
            <?php
            break;
            
        //////////////////////////////////////////////////////////////////////
        // Delete Project
        //////////////////////////////////////////////////////////////////////
        
        case delete:
        
        ?>
            <form>
            <input type="hidden" name="project_path" value="/<?php echo($_GET['path']); ?>">
            <label>Confirm Project Deletion</label>
            <pre>Name: <?php echo($_GET['name']); ?>, Path: /<?php echo($_GET['path']); ?></pre>
            <button class="btn-left">Confirm</button><button class="btn-right" onclick="project.list();return false;">Cancel</button>
            <?php
            break;
        
    }
    
?>
        