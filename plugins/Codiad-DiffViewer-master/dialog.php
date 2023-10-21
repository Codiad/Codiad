<?php

    /*
    *  Copyright (c) Codiad & daeks, distributed
    *  as-is and without warranty under the MIT License. See 
    *  [root]/license.txt for more. This information must remain intact.
    */

    require_once('../../common.php');
    require_once 'class.diff.php';
    
    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////
    
    checkSession();

    switch($_GET['action']){
            
        //////////////////////////////////////////////////////////////////////
        // Compare Files
        //////////////////////////////////////////////////////////////////////
        
        case 'compare':
        
            $Diff = new Diff();
        
            ?>
            <form>
            <label>Diff Viewer</label>
            <pre>First: <?php if(!$Diff->isAbsPath($_GET['first'])) { echo '/'; }; echo($_GET['first']); ?> - Second: <?php if(!$Diff->isAbsPath($_GET['second'])) { echo '/'; };  echo($_GET['second']); ?></pre>
            <table class="diff"><tr><th class="firstcolumns">F</th><th class="firstcolumns">S</th><th>Second File Output</th></tr></table>
            <div class="scrollingArea">
            <table class="diff">
            <?php 
                if(!$Diff->isAbsPath($_GET['first'])) { $_GET['first'] = WORKSPACE. '/'. $_GET['first']; };
                if(!$Diff->isAbsPath($_GET['second'])) { $_GET['second'] = WORKSPACE. '/'. $_GET['second']; };
            
                $oldindex = 0;
                $newindex = 0;
                $del = 0;
                $ins = 0;
                $diff = $Diff->compareFiles($_GET['first'], $_GET['second']);
                foreach ($diff as $key=>$line) {
                    echo '<tr>';
                    if($line[1] == 0) {
                        $oldindex++;
                        $newindex++;
                        echo '<td class="linenumber firstcolumns">'.$oldindex.'</td><td class="linenumber firstcolumns">'.$newindex.'</td>';  
                        echo '<td class="diffUnmodified"><span>'.htmlspecialchars($line[0]).'</span></td>';
                    } else if($line[1] == -1) {
                        $oldindex++;
                        $del++;
                        echo '<td class="linenumber firstcolumns">'.$oldindex.'</td><td class="linenumber firstcolumns"></td>';  
                        echo '<td class="diffDeleted"><span>'.htmlspecialchars($line[0]).'</span></td>';
                    } else if($line[1] == 1) {
                        $newindex++;
                        $ins++;
                        echo '<td class="linenumber firstcolumns"></td><td class="linenumber firstcolumns">'.$newindex.'</td>';  
                        echo '<td class="diffInserted"><span>'.htmlspecialchars($line[0]).'</span></td>';
                    }
                    echo '</tr>';
                }
            ?></table></div>
            <?php if ($ins > 0 || $del > 0) { ?>
            <table class="diff-bottom"><tr><td colspan="3" style="text-align:right;"><span class="added"><?php echo $ins; ?> Line(s) added </span>, <span class="deleted"><?php echo $del; ?>  Line(s) deleted</span></td></tr></table>
            <?php } else { ?>
            <table class="diff-bottom"><tr><td colspan="3" style="text-align:right;">Both files are identical</td></tr></table>

            <?php } ?>
            <button class="btn" onclick="codiad.modal.unload();return false;">Close</button>
            <form>
            <?php
            break;
            
    }
    
?>
