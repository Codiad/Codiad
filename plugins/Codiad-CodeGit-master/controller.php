<?php
/*
 * Copyright (c) Codiad & Andr3as, distributed
 * as-is and without warranty under the MIT License. 
 * See http://opensource.org/licenses/MIT for more information.
 * This information must remain intact.
 */
    error_reporting(0);

    require_once('../../common.php');
    require_once('class.git.php');
    
    checkSession();
    set_time_limit(0);
    
    if ($_GET['action'] != 'checkRepo') {
        $git = new Git();
        define('CONFIG', 'git.' . $_SESSION['user'] . '.php');
    }
    
    switch($_GET['action']) {
        
        case 'checkRepo':
            if (isset($_GET['path'])) {
                if (file_exists(getWorkspacePath($_GET['path'] . '/.git'))) {
                    echo '{"status": true,"message":"Repo exists"}';
                } else {
                    echo '{"status": false,"message":"Repo doesn\'t exits"}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'init':
            if (isset($_GET['path'])) {
                if ($git->init(getWorkspacePath($_GET['path']))) {
                    echo '{"status":"success","message":"Initialized empty Git repository!"}';
                } else {
                    echo '{"status":"error","message":"' . $git->result . '!"}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'clone':
            if (isset($_GET['path']) && isset($_GET['repo']) && isset($_GET['init_submodules'])) {
                echo $git->cloneRepo(getWorkspacePath($_GET['path']), $_GET['repo'], $_GET['init_submodules']);
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'status':
            if (isset($_GET['path'])) {
                $result = $git->status(getWorkspacePath($_GET['path']));
                if ($result === false) {
                    echo '{"status":"error","message":"Failed to get status!"}';
                } else {
                    echo '{"status":"success","data":'. json_encode($result) .'}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
           
        case 'add':
            if (isset($_GET['path']) && isset($_POST['files'])) {
                $files = json_decode($_POST['files']);
                if ($files) {
                    $result = true;
                    foreach($files as $file) {
                        $result = !(!$result | !$git->add(getWorkspacePath($_GET['path']), $file));
                    }
                    if ($result) {
                        echo '{"status":"success","message":"Changes added"}';
                        break;
                    }
                }
                echo '{"status":"success","message":"Failed to add changes"}';
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'commit':
            if (isset($_GET['path']) && isset($_POST['message'])) {
                echo $git->commit(getWorkspacePath($_GET['path']), $_POST['message']);
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'log':
            if (isset($_GET['repo'])) {
                $result = $git->getLog(getWorkspacePath($_GET['repo']));
                if ($result === false) {
                    echo '{"status":"error","message":"Failed to get log!"}';
                } else {
                    echo '{"status":"success","data":'. json_encode($result) .'}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'diff':
            if (isset($_GET['repo']) && isset($_GET['path'])) {
                $result = $git->diff(getWorkspacePath($_GET['repo']), $_GET['path']);
                if ($result === false) {
                    echo '{"status":"error","message":"Failed to get diff!"}';
                } else {
                    echo $result;
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
        
        case 'checkout':
            if (isset($_GET['repo']) && isset($_GET['path'])) {
                if ($git->checkout(getWorkspacePath($_GET['repo']), $_GET['path'])) {
                    echo '{"status":"success","message":"Changes reverted!"}';
                } else {
                    echo '{"status":"error","message":"Failed to undo changes!"}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'getRemotes':
            if (isset($_GET['path'])) {
                $result = $git->getRemotes(getWorkspacePath($_GET['path']));
                if ($result === false) {
                    echo '{"status":"error","message":"Failed to get remotes!"}';
                } else {
                    echo '{"status":"success","data":'. json_encode($result) .'}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'newRemote':
            if (isset($_GET['path']) && isset($_GET['name']) && isset($_GET['url'])) {
                $result = $git->newRemote(getWorkspacePath($_GET['path']), $_GET['name'], $_GET['url']);
                if ($result === false) {
                    echo '{"status":"error","message":"Failed to create remotes!"}';
                } else {
                    echo '{"status":"success","message": "New Remote created."}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'removeRemote':
            if (isset($_GET['path']) && isset($_GET['name'])) {
                $result = $git->removeRemote(getWorkspacePath($_GET['path']), $_GET['name']);
                if ($result === false) {
                    echo '{"status":"error","message":"Failed to remove remote!"}';
                } else {
                    echo '{"status":"success","message":"Remote removed!"}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'renameRemote':
            if (isset($_GET['path']) && isset($_GET['name']) && isset($_GET['newName'])) {
                $result = $git->renameRemote(getWorkspacePath($_GET['path']), $_GET['name'], $_GET['newName']);
                if ($result === false) {
                    echo '{"status":"error","message":"Failed to rename remote!"}';
                } else {
                    echo '{"status":"success","message":"Remote renamed!"}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
        
        case 'getRemoteBranches':
        	if (isset($_GET['path'])) {
                $result = $git->getRemoteBranches(getWorkspacePath($_GET['path']));
                if ($result === false) {
                    echo '{"status":"error","message":"Failed to get remote branches!"}';
                } else {
                    echo '{"status":"success","data":'. json_encode($result) .'}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
        
        case 'checkoutRemote':
            if (isset($_GET['path']) && isset($_GET['name']) && isset($_GET['remoteName'])) {
                $result = $git->checkoutRemote(getWorkspacePath($_GET['path']), $_GET['name'], $_GET['remoteName']);
                if ($result === false) {
                    echo '{"status":"error","message":"Failed to checkout remote!"}';
                } else {
                    echo '{"status":"success","message":"Remote checkedout!"}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'getBranches':
            if (isset($_GET['path'])) {
                $result = $git->getBranches(getWorkspacePath($_GET['path']));
                if ($result === false) {
                    echo '{"status":"error","message":"Failed to get branches!"}';
                } else {
                    echo '{"status":"success","data":'. json_encode($result) .'}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'newBranch':
            if (isset($_GET['path']) && isset($_GET['name'])) {
                $result = $git->newBranch(getWorkspacePath($_GET['path']), $_GET['name']);
                if ($result === false) {
                    echo '{"status":"error","message":"Failed to create branch!"}';
                } else {
                    echo '{"status":"success","message": "New branch created."}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'deleteBranch':
            if (isset($_GET['path']) && isset($_GET['name'])) {
                $result = $git->deleteBranch(getWorkspacePath($_GET['path']), $_GET['name']);
                if ($result === false) {
                    echo '{"status":"error","message":"Failed to delete branch!"}';
                } else {
                    echo '{"status":"success","message":"Branch deleted!"}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
        
        case 'checkoutBranch':
            if (isset($_GET['path']) && isset($_GET['name'])) {
                $result = $git->checkoutBranch(getWorkspacePath($_GET['path']), $_GET['name']);
                if ($result === false) {
                    echo '{"status":"error","message":"Failed to checkout branch!"}';
                } else {
                    echo '{"status":"success","message":"Switched to branch: ' . $_GET['name'] .'!"}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
        
        case 'renameBranch':
            if (isset($_GET['path']) && isset($_GET['name']) && isset($_GET['newName'])) {
                $result = $git->renameBranch(getWorkspacePath($_GET['path']), $_GET['name'], $_GET['newName']);
                if ($result === false) {
                    echo '{"status":"error","message":"Failed to rename branch!"}';
                } else {
                    echo '{"status":"success","message":"Branch renamed!"}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'merge':
            if (isset($_GET['path']) && isset($_GET['name'])) {
                $result = $git->merge(getWorkspacePath($_GET['path']), $_GET['name']);
                if ($result === false) {
                    echo '{"status":"error","message":"Failed to merge branch!"}';
                } else {
                    echo '{"status":"success","message":"Branch merged!"}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'push':
            if (isset($_GET['path']) && isset($_GET['remote']) && isset($_GET['branch'])) {
                echo $git->push(getWorkspacePath($_GET['path']), $_GET['remote'], $_GET['branch']);
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'pull':
            if (isset($_GET['path']) && isset($_GET['remote']) && isset($_GET['branch'])) {
                echo $git->pull(getWorkspacePath($_GET['path']), $_GET['remote'], $_GET['branch']);
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
        
        case 'fetch':
            if (isset($_GET['path']) && isset($_GET['remote'])) {
                echo $git->fetch(getWorkspacePath($_GET['path']), $_GET['remote']);
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'rename':
            if (isset($_GET['path']) && isset($_GET['old_name']) && isset($_GET['new_name'])) {
                echo $git->renameItem(getWorkspacePath($_GET['path']), $_GET['old_name'], $_GET['new_name']);
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'submodule':
            if (isset($_GET['repo']) && isset($_GET['path']) && isset($_GET['submodule'])) {
                echo $git->submodule(getWorkspacePath($_GET['repo']), $_GET['path'], $_GET['submodule']);
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'initSubmodule':
            if (isset($_GET['path'])) {
                echo $git->initSubmodule(getWorkspacePath($_GET['path']));
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'numstat':
            if (isset($_GET['path'])) {
                $result = $git->numstat(getWorkspacePath($_GET['path']));
                if ($result !== false) {
                    echo $result;
                } else {
                    echo '{"status":"error","message":"Failed to get numstat"}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'showCommit':
            if (isset($_GET['path']) && isset($_GET['commit'])) {
                echo $git->showCommit(getWorkspacePath($_GET['path']), $_GET['commit']);
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'blame':
            if (isset($_GET['repo']) && isset($_GET['path'])) {
                $result = $git->blame(getWorkspacePath($_GET['repo']), $_GET['path']);
                if ($result === false) {
                    echo '{"status":"error","message":"Failed to get diff!"}';
                } else {
                    echo $result;
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'network':
            if (isset($_GET['path'])) {
                $result = $git->network(getWorkspacePath($_GET['path']));
                if ($result === false) {
                    echo '{"status":"error","message":"Failed to get network!"}';
                } else {
                    echo '{"status":"success","data":'. json_encode($result) .'}';
                }
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'getSettings':
            if (isset($_GET['path'])) {
                $settings = $git->getSettings(getWorkspacePath($_GET['path']));
                echo '{"status":"success","data":'. json_encode($settings) .'}';
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
            }
            break;
            
        case 'setSettings':
            if (isset($_POST['settings']) && isset($_GET['path'])) {
                $settings = json_decode($_POST['settings'], true);
                
                $pluginSettings = getJSON('git.settings.php', 'config');
                if ($pluginSettings['lockuser'] == "true") {
                    $settings['username'] = $_SESSION['user'];
                    if (strlen($settings['local_username']) != 0) {
                        $settings['local_username'] = $_SESSION['user'];
                    }
                }
                
                $git->setSettings($settings, getWorkspacePath($_GET['path']));
                echo '{"status":"success","message":"Settings saved"}';
            } else {
                echo '{"status":"error","message":"Missing parameter!"}';
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
        return WORKSPACE."/".$path;
    }
?>
