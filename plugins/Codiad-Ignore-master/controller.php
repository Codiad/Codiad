<?php
/*
 * Copyright (c) Codiad & Andr3as, distributed
 * as-is and without warranty under the MIT License. 
 * See http://opensource.org/licenses/MIT for more information.
 * This information must remain intact.
 */
    error_reporting(0);

    require_once('../../common.php');
    checkSession();
    
    switch($_GET['action']){
        case 'load':
            if (file_exists(DATA . "/config/" . getFileName())) {
                echo json_encode(getJSON(getFileName(), "config"));
            } else {
                echo json_encode(array());
            }
            break;
            
        case 'save':
            if (isset($_POST['data'])) {
                saveJSON(getFileName(), json_decode($_POST['data']), "config");
                echo '{"status":"success","message":"Data saved"}';
            } else {
                echo '{"status":"error","message":"Missing Parameter"}';
            }
            break;
            
        case 'isDir':
            if (isset($_GET['path'])) {
                $result = array();
                $result['status'] = "success";
                $result['result'] = is_dir(getWorkspacePath($_GET['path']));
                echo json_encode($result);
            } else {
                echo '{"status":"error","message":"Missing Parameter"}';
            }
            break;
        
        default: 
            echo '{"status":"error","message":"No Type"}';
            break;
    }
    
    function getFileName() {
        return "ignore.".$_SESSION['user'].".php";
    }
    
    function getWorkspacePath($path) {
        //Security check
        if (!Common::checkPath($path)) {
            die('{"status":"error","message":"Invalid path"}');
        }
        if (strpos($path, "/") === 0) {
            //Unix absolute path
            return $path;
        }
        if (strpos($path, ":/") !== false) {
            //Windows absolute path
            return $path;
        }
        if (strpos($path, ":\\") !== false) {
            //Windows absolute path
            return $path;
        }
        return "../../workspace/".$path;
    }
?>