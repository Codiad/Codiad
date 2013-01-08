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
    // Get Action
    //////////////////////////////////////////////////////////////////

    if(!isset($_POST['action']) || empty($_POST['action'])) {
        exit(formatJSEND('error', 'No Action Specified'));
    }

    switch ($_POST['action']) {
    case 'register':
        /* Register as a collaborator for the given filename. */
        /* FIXME beware of filenames with '%' characters. */
        $filename = BASE_PATH . '/data/' . str_replace('/', '_', $_POST['filename']) . '%%' . $_SESSION['user'];
        if (file_exists($filename)) {
            echo formatJSEND('error', 'Already registered as collaborator for ' . $_POST['filename']);
        } else {
            touch($filename);
            echo formatJSEND('success');
        }
        break;

    case 'unregister':
        /* Unregister as a collaborator for the given filename. */
        $filename = BASE_PATH . '/data/' . str_replace('/', '_', $_POST['filename']) . '%%' . $_SESSION['user'];
        if (!file_exists($filename)) {
            echo formatJSEND('error', 'Not registered as collaborator for ' . $_POST['filename']);
        } else {
            unlink($filename);
            echo formatJSEND('success');
        }
        break;

    case 'unregisterFromAll':
        /* Find all the files for which the current user is registered as
         * collaborator and unregister him. */
        $basePath = BASE_PATH . '/data/';
        if ($handle = opendir($basePath)) {
            $regex = '/' . $_SESSION['user'] . '$/';
            while (false !== ($entry = readdir($handle))) {
                if (preg_match($regex, $entry)) {
                    unlink($basePath . $entry);
                }
            }
        }

        echo formatJSEND('success');
        break;

    case 'cursorChange':
        /* Push the current selection to the server. */
        if (isUserRegisteredForFile($_SESSION['user'], $_POST['filename'])) {
            $filename = str_replace('/', '_', $_POST['filename']) . '%%' . $_SESSION['user'] . '%%selection';
            $selection = json_decode($_POST['selection']);
            saveJSON($filename, $selection);
            echo formatJSEND('success');
        } else {
            echo formatJSEND('error', 'Not registered as collaborator for ' . $_POST['filename']);
        }
        break;

    case 'documentChange':
        /* Push a document change to the server. */
        if (isUserRegisteredForFile($_SESSION['user'], $_POST['filename'])) {
            $filename = str_replace('/', '_', $_POST['filename']) . '%%' . $_SESSION['user'] . '%%changes';

            $changes = array();
            if (file_exists(BASE_PATH . '/data/' . $filename)) {
                $changes = getJSON($filename);
            }

            $maxChangeIndex = max(array_keys($changes));

            $change = json_decode($_POST['change'], true);
            $change['version'] = json_decode($_POST['version']);
            $changes[++$maxChangeIndex] = $change;
            /* print_r($changes); */

            saveJSON($filename, $changes);
            echo formatJSEND('success');
        } else {
            echo formatJSEND('error', 'Not registered as collaborator for ' . $_POST['filename']);
        }
        break;

    case 'getUsersAndSelectionsForFile':
        /* Get an object containing all the users registered to the given file 
         * and their associated selections. */

        break;

    default:
        exit(formatJSEND('error', 'Unknown Action ' . $_POST['action']));
    }

    function isUserRegisteredForFile($user, $filename) {
        $marker = BASE_PATH . '/data/' . str_replace('/', '_', $filename) . '%%' . $user;
        return file_exists($marker);
    }

?>

