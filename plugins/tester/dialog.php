<?php

    /*
    *  Copyright (c) Codiad & daeks, distributed
    *  as-is and without warranty under the MIT License. See 
    *  [root]/license.txt for more. This information must remain intact.
    */

    require_once('../../common.php');
    require_once('class.tester.php');
    
    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////
    
    checkSession();

    switch($_GET['action']){
            
        //////////////////////////////////////////////////////////////////////
        // Pull Repo
        //////////////////////////////////////////////////////////////////////
        
        case 'list':
        
            $Tester = new Tester();
        
            ?>
            <form>
            <label>Pull Requests</label>
            <div class="scrollingArea">
            <table>
            <tr><th>#</th><th>Title</th><th>Description</th><th>Action</th></tr>
            <?php 
                if(!$Tester->isAbsPath($_GET['root'])) { $_GET['root'] = WORKSPACE. '/'. $_GET['root']; };
                foreach ($Tester->Get_Requests() as $key=>$request) {
                    echo '<tr>';
                    echo '<td>'.$request['number'].'</td>';
                    echo '<td>'.$request['title'].'</td>';
                    echo '<td><pre>'.htmlspecialchars($request['body']).'</pre></td>';
                    if(file_exists($_GET['root'].'/'.$request['number']) && is_dir($_GET['root'].'/'.$request['number'])) {
                        echo '<td></td>';
                    } else {
                        echo '<td><button class="btn" onclick="codiad.tester.pull('.$request['number'].');return false;">Clone</button></td>';
                    }
                    echo '</tr>';
                }
            ?></table></div> 
            <button class="btn" onclick="codiad.modal.unload();return false;">Cancel</button>
            <form>
            <?php
            break;
            
    }
    
?>
