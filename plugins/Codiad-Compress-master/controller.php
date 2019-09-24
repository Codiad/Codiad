<?php
/*
 * Copyright (c) Codiad & Andr3as, distributed
 * as-is and without warranty under the MIT License. 
 * See [root]/license.md for more information. This information must remain intact.
 */

    require_once('../../common.php');
    
    checkSession();
    error_reporting(0);
    
    switch($_GET['action']) {
        
        /**
         * Compress a css file.
         *
         * @param {string} path The path of the file to compress
         * @param {string} code Compressed code
         */
        case 'compressCSS':
        case 'compressJS':
            if (isset($_GET['path']) && isset($_POST['code'])) {
                if ($_GET['action'] == "compressCSS") {
                    $ext    = ".css";
                    $print  = "CSS";
                } else {
                    $ext    = ".js";
                    $print  = "JS";
                }
                $path   = getWorkspacePath($_GET['path']);
                $nFile  = substr($path, 0, strrpos($path, $ext));
                $nFile  = $nFile . ".min".$ext;
                file_put_contents($nFile, $_POST['code']);
                echo '{"status":"success","message":"'.$print.' minified!"}';
            } else {
                echo '{"status":"error","message":"Missing Parameter!"}';
            }
            break;

        /**
         * Get file content
         *
         * @param {string} path The path of the file
         */
        case 'getContent':
            if (isset($_GET['path'])) {
                echo file_get_contents(getWorkspacePath($_GET['path']));
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
        return WORKSPACE . "/" . $path;
    }
?>