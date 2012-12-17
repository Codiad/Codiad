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
    
    //////////////////////////////////////////////////////////////////
    // Check system() command
    //////////////////////////////////////////////////////////////////
    
    if(!isAvailable('system')){ exit('<script>parent.message.error("System Command Not Supported")</script>'); }
    
    //////////////////////////////////////////////////////////////////
    // Run Download
    //////////////////////////////////////////////////////////////////

    if($_GET['type']=='directory' || $_GET['type']=='root'){
        // Create tarball
        $filename = explode("/",$_GET['path']);
        $filename = array_pop($filename) . "-" . date('Y.m.d') . ".tar.gz";
        $targetPath = DATA . '/';
        $dir = WORKSPACE . $_GET['path'];
        # Execute the tar command and save file
        system("tar -pczf ".$targetPath ."/".$filename." ".$dir);
        $download_file = $targetPath.$filename;
    }else{
        $filename = explode("/",$_GET['path']);
        $filename = array_pop($filename);
        $download_file = WORKSPACE . $_GET['path'];
    }
    
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($filename));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($download_file));
    ob_clean();
    flush();
    readfile($download_file);
    // Remove temp tarball
    if($_GET['type']=='directory' || $_GET['type']=='root'){ unlink($download_file); }

?>
