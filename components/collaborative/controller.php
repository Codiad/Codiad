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
            echo formatJSEND('error', 'Unable to register as collaborator for ' . $_POST['filename']);
        } else {
            touch($filename);
            echo formatJSEND('success');
        }
        break;
    case 'unregister':
        $filename = BASE_PATH . '/data/' . str_replace('/', '_', $_POST['filename']) . '%%' . $_SESSION['user'];
        if (!file_exists($filename)) {
            echo formatJSEND('error', 'Unable to unregister as collaborator for ' . $_POST['filename']);
        } else {
            unlink($filename);
            echo formatJSEND('success');
        }
        break;
    case 'cursorChange':
        $data = json_decode($_POST['selection']);
        echo json_encode($data);
        break;
    case 'documentChange':
        $data = json_decode($_POST['change']);
        echo json_encode($data);
        break;
    default:
        exit(formatJSEND('error', 'Unknown Action ' . $_POST['action']));
    }

?>

