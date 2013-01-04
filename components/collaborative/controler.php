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

    if(!isset($_POST["action"]) || empty($_POST['action'])) {
        exit('{"status":"error","data":{"error":"No Action Specified"}}');
    }

    switch ($_POST['action']) {
    case "register":
        /* FIXME beware of filenames with '%' characters. */
        $filename = BASE_PATH . "/data/" . str_replace("/", "_", $_POST['filename']) . "%" . $_SESSION['user'];
        /* var_dump($filename); */
        /* die(); */
        touch($filename);
        /* TODO What to return to notify success? */
        break;
    case "unregister":
        /* FIXME beware of filenames with '%' characters. */
        $filename = BASE_PATH . "/data/" . str_replace("/", "_", $_POST['filename']) . "%" . $_SESSION['user'];
        unlink($filename);
        /* TODO What to return to notify success? */
        break;
    case "cursor":
        $data = json_decode($_POST["cursor"]);
        echo json_encode($data);
        break;
    case "change":
        $data = json_decode($_POST["change"]);
        echo json_encode($data);
        break;
    default:
        exit('{"status":"error","data":{"error":"Unknown Action ' . $_POST['action'] . '}}');
    }

?>

