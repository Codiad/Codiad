<?php

    /*
    *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */

    /*
     * Suppose a user wants to register as a collaborator of file '/test/test.js'.
     * He registers to a specific file by creating a marker file
     * 'data/_test_test.js%%filename%%username%%registered', and he can
     * unregister by deleting this file. Then his current selection will be in
     * file 'data/_test_test.js%%username%%selection'.
     * The collaborative editing algorithm is based on the differential synchronization
     * algorithm by Neil Fraser. The text shadow and server text are stored
     * respectively in 'data/_test_test.js%%filename%%username%%shadow' and
     * 'data/_test_test.js%%filename%%text'.
     * At regular time intervals, the user send an heartbeat which is stored in
     * 'data/_test_test.js%%username%%heartbeat' .
     */

    require_once('../../config.php');
    require_once('../../lib/diff_match_patch.php');

    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////

    checkSession();

    //////////////////////////////////////////////////////////////////
    // Get Action
    //////////////////////////////////////////////////////////////////

    if(!isset($_POST['action']) || empty($_POST['action'])) {
        exit(formatJSEND('error', 'No action specified'));
    }

    switch ($_POST['action']) {
    case 'registerToFile':
        /* Register as a collaborator for the given filename. */
        if(!isset($_POST['filename']) || empty($_POST['filename'])) {
            exit(formatJSEND('error', 'No filename specified in register'));
        }

        /* FIXME beware of filenames with '%' characters. */
        $filename = makeRegisteredMarkerFilename($_POST['filename'], $_SESSION['user']);
        if (file_exists($filename)) {
            echo formatJSEND('error', 'Already registered as collaborator for ' . $_POST['filename']);
        } else {
            $succes = touch($filename);
            if ($success) {
                echo formatJSEND('success');
            } else {
                echo formatJSEND('error', 'Unable to register as collaborator for ' . $_POST['filename']);
            }
        }
        break;

    case 'unregisterFromFile':
        /* Unregister as a collaborator for the given filename. */
        if(!isset($_POST['filename']) || empty($_POST['filename'])) {
            exit(formatJSEND('error', 'No filename specified in unregister'));
        }

        $filename = makeRegisteredMarkerFilename($_POST['filename'], $_SESSION['user']);
        if (!file_exists($filename)) {
            echo formatJSEND('error', 'Not registered as collaborator for ' . $_POST['filename']);
        } else {
            unlink($filename);
            echo formatJSEND('success');
        }
        break;

    case 'unregisterFromAllFiles':
        /* Find all the files for which the current user is registered as
         * collaborator and unregister him. */
        $basePath = BASE_PATH . '/data/';
        if ($handle = opendir($basePath)) {
            $regex = '/' . $_SESSION['user'] . '%%registered$/';
            while (false !== ($entry = readdir($handle))) {
                if (preg_match($regex, $entry)) {
                    unlink($basePath . $entry);
                }
            }
        }

        echo formatJSEND('success');
        break;

    case 'removeSelectionAndChangesForAllFiles':
        $basePath = BASE_PATH . '/data/';
        if ($handle = opendir($basePath)) {
            $regex = '/' . $_SESSION['user'] . '/';
            while (false !== ($entry = readdir($handle))) {
                if (preg_match($regex, $entry)) {
                    unlink($basePath . $entry);
                }
            }
        }

        echo formatJSEND('success');
        break;

    case 'removeServerTextForAllFiles':
        $basePath = BASE_PATH . '/data/';
        if ($handle = opendir($basePath)) {
            $regex = '/%%text$/';
            while (false !== ($entry = readdir($handle))) {
                if (preg_match($regex, $entry)) {
                    unlink($basePath . $entry);
                }
            }
        }

        echo formatJSEND('success');
        break;

    case 'sendSelectionChange':
        /* Push the current selection to the server. */
        if(!isset($_POST['filename']) || empty($_POST['filename'])) {
            exit(formatJSEND('error', 'No Filename Specified in sendSelectionChange'));
        }

        if(!isset($_POST['selection']) || empty($_POST['selection'])) {
            exit(formatJSEND('error', 'No selection specified in sendSelectionChange'));
        }

        if (isUserRegisteredForFile($_SESSION['user'], $_POST['filename'])) {
            $filename = makeSelectionMarkerFilename($_POST['filename'], $_SESSION['user']);
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
            exit(formatJSEND('error', 'No filename specified in sendDocumentChange'));
        }

        if(!isset($_POST['change']) || empty($_POST['change'])) {
            exit(formatJSEND('error', 'No change specified in sendDocumentChange'));
        }

        if (isUserRegisteredForFile($_SESSION['user'], $_POST['filename'])) {
            $filename = makeChangesMarkerFilename($_POST['filename'], $_SESSION['user']);

            $changes = array();
            if (file_exists(BASE_PATH . '/data/' . $filename)) {
                $changes = getJSON($filename);
            }

            $maxChangeIndex = max(array_keys($changes));

            $change = json_decode($_POST['change'], true);
            $change['revision'] = json_decode($_POST['revision']);
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
            exit(formatJSEND('error', 'No filename specified in getUsersAndSelectionsForFile'));
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

    case 'getUsersAndChangesForFile':
        /* Get an object containing all the users registered to the given file
        * and their associated list of changes from the given revision
        * number. The data corresponding to the current user is omitted. */
        if(!isset($_POST['filename']) || empty($_POST['filename'])) {
            exit(formatJSEND('error', 'No filename specified in getUsersAndChangesForFile'));
        }

        if(!isset($_POST['fromRevision'])) {
            exit(formatJSEND('error', 'No fromRevision argument Specified in getUsersAndChangesForFile'));
        }

        $filename = $_POST['filename'];
        $fromRevision = $_POST['fromRevision'];

        $usersAndChanges = array();
        $users = getRegisteredUsersForFile($filename);
        foreach ($users as $user) {
            if ($user !== $_SESSION['user']) {
                $changes = getChanges($filename, $user, $fromRevision);
                if (!empty($changes)) {
                    $usersAndChanges[$user] = $changes;
                }
            }
        }

        echo formatJSEND('success', $usersAndChanges);
        break;

    case 'sendShadow':
        if(!isset($_POST['filename']) || empty($_POST['filename'])) {
            exit(formatJSEND('error', 'No filename specified in sendShadow'));
        }

        if(!isset($_POST['shadow'])) {
            exit(formatJSEND('error', 'No shadow specified in sendShadow'));
        }

        $filename = $_POST['filename'];
        $clientShadow = $_POST['shadow'];

        setShadow($filename, $_SESSION['user'], $clientShadow);
        if (!existsServerText($filename)) {
            setServerText($filename, $clientShadow);
        }

        echo formatJSEND('success');
        break;

    case 'synchronizeText':
        if(!isset($_POST['filename']) || empty($_POST['filename'])) {
            exit(formatJSEND('error', 'No filename specified in synchronizeText'));
        }

        if(!isset($_POST['patch'])) {
            exit(formatJSEND('error', 'No patch specified in synchronizeText'));
        }

        /* First acquire a lock or wait until a lock can be acquired for server
         * text and shadow. */
        $serverTextFilename = makeServerTextMarkerFilename($_POST['filename']);
        $shadowTextFilename = makeShadowMarkerFilename($_POST['filename']);
        flock($serverTextFilename, LOCK_EX);
        flock($shadowTextFilename, LOCK_EX);

        $serverText = file_get_contents($serverTextFilename);
        $shadowText = file_get_contents($shadowTextFilename);

        $patchFromClient = $_POST['patch'];

        /* Patch the shadow and server texts with the edits from the client. */
        $dmp = new diff_match_patch();
        $patchedServerText = $dmp->patch_apply($dmp->patch_fromText($patchFromClient), $serverText);
        file_put_contents($serverTextFilename, $patchedServerText[0]);

        $patchedShadowText = $dmp->patch_apply($dmp->patch_fromText($patchFromClient), $shadowText);

        /* Make a diff between server text and shadow to get the edits to send
         * back to the client. */
        $patchFromServer = $dmp->patch_toText($dmp->patch_make($patchedShadowText[0], $patchedServerText[0]));

        /* Apply it to the shadow. */
        $patchedShadowText = $dmp->patch_apply($dmp->patch_fromText($patchFromServer), $patchedShadowText[0]);
        file_put_contents($shadowTextFilename, $patchedShadowText[0]);

        /* Release locks. */
        flock($serverTextFilename, LOCK_UN);
        flock($shadowTextFilename, LOCK_UN);

        echo formatJSEND('success', $patchFromServer);
        break;

    case 'sendHeartbeat':
        updateHeartbeatMarker($_SESSION['user']);
        echo formatJSEND('success');
        break;

    default:
        exit(formatJSEND('error', 'Unknown Action ' . $_POST['action']));
    }

    // --------------------
    /* Helper functions to make the marker filenames corresponding to the given
     * parameters. */
    function makeRegisteredMarkerFilename($filename, $user) {
        $sanitizedFilename = str_replace('/', '_', $filename);
        validatePathOrDie($sanitizedFilename);
        validatePathOrDie($user);
        return BASE_PATH . '/data/' . $sanitizedFilename . '%%' . $user . '%%registered';
    }

    function makeSelectionMarkerFilename($filename, $user) {
        $sanitizedFilename = str_replace('/', '_', $filename);
        validatePathOrDie($sanitizedFilename);
        validatePathOrDie($user);
        return $sanitizedFilename . '%%' . $user . '%%selection';
    }

    function makeChangesMarkerFilename($filename, $user) {
        $sanitizedFilename = str_replace('/', '_', $filename);
        validatePathOrDie($sanitizedFilename);
        validatePathOrDie($user);
        return $sanitizedFilename . '%%' . $user . '%%changes';
    }

    function makeShadowMarkerFilename($filename, $user) {
        $sanitizedFilename = str_replace('/', '_', $filename);
        validatePathOrDie($sanitizedFilename);
        validatePathOrDie($user);
        return BASE_PATH . '/data/' . $sanitizedFilename . '%%' . $username . '%%shadow';
    }

    function makeServerTextMarkerFilename($filename) {
        $sanitizedFilename = str_replace('/', '_', $filename);
        validatePathOrDie($sanitizedFilename);
        validatePathOrDie($user);
        return BASE_PATH . '/data/' . $sanitizedFilename . '%%shadow';
    }

    function makeHeartbeatMarkerFilename($user) {
        validatePathOrDie($user);
        return BASE_PATH . '/data/' . $user . '%%heartbeat';
    }

    // TODO Put this in a more robust way in common.php
    /* Validate that a path does not contain '..' or stuff like that for security. */
    function validatePathOrDie($path) {
        if (strstr($path, '/') ||
            strstr($path, '\\') ||
            strstr($path, '..') ) {
            // Security fault.
            die();
        }
    }

    // --------------------
    /* $filename must contain only the basename of the file. */
    function isUserRegisteredForFile($user, $filename) {
        $marker = BASE_PATH . '/data/' . str_replace('/', '_', $filename) . '%%' . $user . '%%registered';
        return file_exists($marker);
    }

    /* Touch the heartbeat marker file for the given user. Return true on
     * success, false on failure. */
    function updateHeartbeatMarker($user) {
        $marker = BASE_PATH . '/data/' . $user . '%%heartbeat';
        return touch($marker);
    }

    /* $filename must contain only the basename of the file. */
    function getRegisteredUsersForFile($filename) {
        $usernames = array();
        $markers = getMarkerFilesForFilename($filename);
        if (!empty($markers)) {
            foreach ($markers as $entry) {
                if (strpos($entry, 'registered')) {
                    /* $entry is a marker file marking a registered user.
                     * Extract the user name from the filename. */
                    $matches = array();
                    $entry = substr($entry, 0, strlen($entry) - 12); // Remove '%%registered' from $entry.
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

    /* Return the list of changes, if any, for the given filename, user and
     * from the given revision number.
     * $filename must contain only the basename of the file. */
    function getChanges($filename, $user, $fromRevision) {
        $sanitizedFilename = str_replace('/', '_', $filename);
        $json = getJSON($sanitizedFilename . '%%' . $user . '%%changes');
        /* print_r(array_slice($json, $fromRevision, NULL, true));  */
        return array_slice($json, $fromRevision, NULL, true);
    }

    /* Set the server shadow acquiring an exclusive lock on the file. $shadow
     * is a string. */
    function setShadow($filename, $username, $shadow) {
        $sanitizedFilename = BASE_PATH . '/data/' . str_replace('/', '_', $filename) . '%%' . $username . '%%shadow';
        file_put_contents($sanitizedFilename, $shadow, LOCK_EX);
    }

    /* Return the shadow for the given filename as a string or an empty string
     * if no shadow exists. */
    function getShadow($filename) {
        $shadow = '';
        $markers = getMarkerFilesForFilename($filename);
        if (!empty($markers)) {
            foreach ($markers as $entry) {
                if (strpos($entry, 'shadow')) {
                    $shadow = file_get_contents($entry);
                    print_r($shadow);
                }
            }
        }

        return $shadow;
    }

    function existsServerText($filename) {
        $sanitizedFilename = BASE_PATH . '/data/' . str_replace('/', '_', $filename) . '%%text';
        return file_exists($sanitizedFilename);
    }

    /* Set the server text acquiring an exclusive lock on the file. $serverText
     * is a string. */
    function setServerText($filename, $serverText) {
        $sanitizedFilename = BASE_PATH . '/data/' . str_replace('/', '_', $filename) . '%%text';
        file_put_contents($sanitizedFilename, $serverText, LOCK_EX);
    }

    /* Return the server text for the given filename as a string or an empty string
     * if no server text exists. */
    function getServerText($filename) {
        $serverText = '';
        $markers = getMarkerFilesForFilename($filename);
        if (!empty($markers)) {
            foreach ($markers as $entry) {
                if (strpos($entry, 'text')) {
                    $serverText = file_get_contents($entry);
                    print_r($serverText);
                }
            }
        }

        return $serverText;
    }

?>
