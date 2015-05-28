<?php

    /*
    *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See 
    *  [root]/license.txt for more. This information must remain intact.
    */

	require_once('../../lib/archive_tar.php');
    require_once('../../common.php');

    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////

    checkSession();

    //////////////////////////////////////////////////////////////////
    // Check $_GET for invalid path
    //////////////////////////////////////////////////////////////////
    //TODO check if the User is allowed to access the project
    if(!isset($_GET['path']) 
    		|| preg_match('#^[\\\/]?$#i', trim($_GET['path'])) // download all Projects
    		|| preg_match('#[\:*?\"<>\|]#i', $_GET['path']) //illegal chars in filenames
    		|| substr_count($_GET['path'], './') > 0) { // change directory up to escape Workspace
    	exit('<script>parent.codiad.message.error("Wrong data send")</script>');
    }

    //////////////////////////////////////////////////////////////////
    // Run Download
    //////////////////////////////////////////////////////////////////

    if(isset($_GET['type']) && ($_GET['type']=='directory' || $_GET['type']=='root')){
        // Create tarball
        $filename = explode("/",$_GET['path']);
        //$filename = array_pop($filename) . "-" . date('Y.m.d') . ".tar.gz";
        $filename = array_pop($filename) . "-" . date('Y.m.d');
        $targetPath = DATA . '/';
        $dir = WORKSPACE . '/' . $_GET['path'];
        if(!is_dir($dir)){
        	exit('<script>parent.codiad.message.error("Directory not found.")</script>');
        }

        // Check if Archive_Tar is available
        if(class_exists('Archive_Tar', false)){
          $filename .= '.tar.gz';

		  $files = indexesDir($dir);

          $archive = new Archive_Tar($targetPath.$filename, true);
		  $archive->createModify($files, '', WORKSPACE);

          $download_file = $targetPath.$filename;
        }elseif(extension_loaded('zip')){ //Check if zip-Extension is availiable
          //build zipfile
          require_once 'class.dirzip.php';

          $filename .= '.zip';
          $download_file = $targetPath.$filename;
          DirZip::zipDir($dir, $targetPath .$filename);
        }else{
          exit('<script>parent.codiad.message.error("Could not pack the folder, zip-extension missing")</script>');
        }
    }else{
        $filename = explode("/",$_GET['path']);
        $filename = array_pop($filename);
        $download_file = WORKSPACE . '/' . $_GET['path'];
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($filename).'"');
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

?>c
