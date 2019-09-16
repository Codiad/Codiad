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
		
		case 'move':
			if (isset($_GET['source']) && isset($_GET['dest'])) {
				$source = getWorkspacePath($_GET['source']);
				$dest   = getWorkspacePath($_GET['dest']) ."/". basename($source);
				if (!file_exists($dest)) {
					if (rename($source, $dest)) {
						echo '{"status":"success","message":"Path moved"}';
					} else {
						echo '{"status":"error","message":"Failed to move path!"}';
					}
				} else {
					echo '{"status":"error","message":"Path already exists!"}';
				}
			} else {
				echo '{"status":"error","message":"Missing Parameter"}';
			}
			break;
		
		case 'getContent':
            if (isset($_GET['path'])) {
                $content = file_get_contents(getWorkspacePath($_GET['path']));
                $result = array("status" => "success", "content" => $content);
                echo json_encode($result);
            } else {
                echo '{"status":"error","message":"Missing Parameter"}';
            }
            break;
		
		default:
			echo '{"status":"error","message":"No Type"}';
			break;
	}
	
	
	function getWorkspacePath($path) {
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