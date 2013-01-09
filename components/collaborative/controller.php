<?php

    /*
    *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */

    /*
     * Suppose a user wants to register as a collaborator of file '/test/test.js'.
     * He registers by creating a marker file 'data/_test_test.js%%usename'. Then
     * his current selection will be in file
     * 'data/_test_test.js%%username%%selection' and his changes history in
     * 'data/_test_test.js%%username%%changes'. He can unregister by deleting his
     * marker file.
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
        if(!isset($_POST['filename']) || empty($_POST['filename'])) {
            exit(formatJSEND('error', 'No Filename Specified in register'));
        }

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
        if(!isset($_POST['filename']) || empty($_POST['filename'])) {
            exit(formatJSEND('error', 'No Filename Specified in unregister'));
        }

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

    case 'sendCursorChange':
        /* Push the current selection to the server. */
        if(!isset($_POST['filename']) || empty($_POST['filename'])) {
            exit(formatJSEND('error', 'No Filename Specified in sendCursorChange'));
        }

        if(!isset($_POST['selection']) || empty($_POST['selection'])) {
            exit(formatJSEND('error', 'No Selection Specified in sendCursorChange'));
        }

        if (isUserRegisteredForFile($_SESSION['user'], $_POST['filename'])) {
            $filename = str_replace('/', '_', $_POST['filename']) . '%%' . $_SESSION['user'] . '%%selection';
            $selection = json_decode($_POST['selection']);
            saveJSON($filename, $selection);
            echo formatJSEND('success');
        } else {
            echo formatJSEND('error', 'Not registered as collaborator for ' . $_POST['filename']);
        }
        break;

    case 'sendDocumentChange':
        /* Push a document change to the server. */
        if(!isset($_POST['filename']) || empty($_POST['filename'])) {
            exit(formatJSEND('error', 'No Filename Specified in sendDocumentChange'));
        }

        if(!isset($_POST['change']) || empty($_POST['change'])) {
            exit(formatJSEND('error', 'No Change Specified in sendDocumentChange'));
        }

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

            saveJSON($filename, $changes);
            echo formatJSEND('success');
        } else {
            echo formatJSEND('error', 'Not registered as collaborator for ' . $_POST['filename']);
        }
        break;

    case 'getUsersAndSelectionsForFile':
        /* Get an object containing all the users registered to the given file
         * and their associated selections. The data corresponding to the
         * current user is omitted. */
        if(!isset($_POST['filename']) || empty($_POST['filename'])) {
            exit(formatJSEND('error', 'No Filename Specified in getUsersAndSelectionsForFile'));
        }

        $filename = $_POST['filename'];

        $usersAndSelections = array();
        $users = getRegisteredUsersForFile($filename);
        foreach ($users as $user) {
            if ($user !== $_SESSION['user']) {
                $selection = getSelection($filename, $user);
                if (!empty($selection)) {
                    $usersAndSelections[$user] = $selection;
                }
            }
        }

        echo formatJSEND('success', $usersAndSelections);
        break;

    default:
        exit(formatJSEND('error', 'Unknown Action ' . $_POST['action']));
    }

    /* $filename must contain only the basename of the file. */
    function isUserRegisteredForFile($user, $filename) {
        $marker = BASE_PATH . '/data/' . str_replace('/', '_', $filename) . '%%' . $user;
        return file_exists($marker);
    }

    /* $filename must contain only the basename of the file. */
    function getRegisteredUsersForFile($filename) {
        $usernames = array();
        $markers = getMarkerFilesForFilename($filename);
        if (!empty($markers)) {
            foreach ($markers as $entry) {
                /* Beware if you add new marker file types to add them also in
                 * this test. */
                if (!strpos($entry, 'selection') && !strpos($entry, 'changes')) {
                    /* $entry is a marker file marking a registered user.
                     * Extract the user name from the filename. */
                    $matches = array();
                    preg_match('/\w+$/', $entry, $matches);
                    if (count($matches) !== 1) {
                        exit(formatJSEND('error', 'Unable To Match Username in getMarkerFilesForFilename'));
                    }

                    $usernames[] = $matches[0];
                }
            }
        }

        return $usernames;
    }

    /* Return all marker files related to $filename. $filename must contain
     * only the basename of the file. */
    function getMarkerFilesForFilename($filename) {
        $markers = array();
        $basePath = BASE_PATH . '/data/';
        if ($handle = opendir($basePath)) {
            $sanitizedFilename = str_replace('/', '_', $filename);
            while (false !== ($entry = readdir($handle))) {
                if (strpos($entry, $sanitizedFilename) !== false) {
                    $markers[] = $entry;
                }
            }
        }

        return $markers;
    }

    /* Return the selection object, if any, for the given filename and user.
     * $filename must contain only the basename of the file. */
    function getSelection($filename, $user) {
        $sanitizedFilename = str_replace('/', '_', $filename);
        $json = getJSON($sanitizedFilename . '%%' . $user . '%%selection');
        return $json;
    }

?>
