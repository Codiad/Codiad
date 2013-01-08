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
        $filename = BASE_PATH . '/data/' . str_replace('/', '_', $_POST['filename']) . '%%' . $_SESSION['user'];
        if (!file_exists($filename)) {
            echo formatJSEND('error', 'Not registered as collaborator for ' . $_POST['filename']);
        } else {
            unlink($filename);
            echo formatJSEND('success');
        }
        break;

    case 'cursorChange':
        if (isUserRegisteredForFile($_POST['filename'])) {
            $filename = str_replace('/', '_', $_POST['filename']) . '%%' . $_SESSION['user'] . '%%selection';
            $selection = json_decode($_POST['selection']);
            saveJSON($filename, $selection);
            echo formatJSEND('success');
        } else {
            echo formatJSEND('error', 'Not registered as collaborator for ' . $_POST['filename']);
        }
        break;

    case 'documentChange':
        if (isUserRegisteredForFile($_POST['filename'])) {
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

    default:
        exit(formatJSEND('error', 'Unknown Action ' . $_POST['action']));
    }

    function isUserRegisteredForFile($filename) {
        $marker = BASE_PATH . '/data/' . str_replace('/', '_', $_POST['filename']) . '%%' . $_SESSION['user'];
        return file_exists($marker);
    }

?>

