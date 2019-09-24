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
    
    switch($_GET['action']) {
        
        case 'getContent':
            if (isset($_GET['path'])) {
                $content = file_get_contents(getWorkspacePath($_GET['path']));
                echo json_encode(array("status" => "success", "content" => $content));
            } else {
                echo '{"status":"error","message":"Missing Parameter!"}';
            }
            break;
            
        case 'getFileTree':
            if (isset($_GET['path'])) {
                $path = dirname(getWorkspacePath($_GET['path']));
                $tree = scanProject($path);
                foreach($tree as $i => $file) {
                    $tree[$i] = str_replace($path . "/","",$file);
                }
                $result = array("status" => "success", "tree" => $tree);
                echo json_encode($result);
            } else {
                echo '{"status":"error","message":"Missing Parameter!"}';
            }
            break;
            
        case 'saveContent':
            if (isset($_GET['path']) && isset($_POST['content'])) {
                $dir = dirname($_GET['path']);
                $base = basename($_GET['path']);
                $new = preg_replace("/(\w+)(\.scss|\.sass)$/", "$1.css", $base);
                file_put_contents(getWorkspacePath($dir . "/" . $new), $_POST['content']);
                echo '{"status":"success","message":"Sass file compiled!"}';
            } else {
                echo '{"status":"error","message":"Missing Parameter!"}';
            }
            break;
        
        default:
            echo '{"status":"error","message":"No Type"}';
            break;
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
    
    //////////////////////////////////////////////////////////
    //
    //  Scan folder
    //
    //  @param {string} $path Path of the file or project
    //  @returns {array} Array of files, recursivly
    //
    //////////////////////////////////////////////////////////
    function scanProject($path) {
        if (is_file($path)) {
            $path = dirname($path);
        }
        
        $completeArray = array();
        $files  = scandir($path);
        foreach ($files as $file) {
            //filter . and ..
            $longPath   = $path."/".$file;
            if ($file != "." && $file != ".." && !is_link($longPath)) {
                //check if $file is a folder
                if (is_dir($longPath)) {
                    //scan dir
                    $parsedArray    = scanProject($longPath);
                    $completeArray  = array_merge($completeArray, $parsedArray);
                } else {
                    if (preg_match('/(\.sass|\.scss|\.css)$/',$longPath) === 1) {
                        array_push($completeArray, $longPath);
                    }
                }
            }
        }
        return $completeArray;
    }
?>