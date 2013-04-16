<?php

    /*
    *  Copyright (c) Codiad (codiad.com) & Florent Galland & Luc Verdier,
    *  distributed as-is and without warranty under the MIT License. See
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

    require_once('../../common.php');
    require_once('../../lib/diff_match_patch.php');
    require_once('../../lib/file_db.php');

    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////

    checkSession();

    //////////////////////////////////////////////////////////////////
    // Initialize Data Base
    //////////////////////////////////////////////////////////////////

    $collaborativeDataBase = new file_db(BASE_PATH . '/data/collaborative');

    function &getDB() {
        global $collaborativeDataBase;
        return $collaborativeDataBase;
    }

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

        $isRegistered = registerToFile($_SESSION['user'], $_POST['filename']);
        if ($isRegistered) {
            echo formatJSEND('success');
        } else {
            // Should only be enabled when testing
            //echo formatJSEND('success', 'Not registered as collaborator for ' . $_POST['filename']);
        }
        break;

    case 'unregisterFromFile':
        /* Unregister as a collaborator for the given filename. */
        if(!isset($_POST['filename']) || empty($_POST['filename'])) {
            exit(formatJSEND('error', 'No filename specified in unregister'));
        }

        $query = array('user' => $_SESSION['user'], 'filename' => $_POST['filename']);
        $entry = getDB()->select($query, 'registered');
        if ($entry != null) {
            $entry->remove();
            echo formatJSEND('success');
        } else {
            // Should only be enabled when testing
            //echo formatJSEND('success', 'Not registered as collaborator for ' . $_POST['filename']);
            echo formatJSEND('success');
        }
        break;

    case 'unregisterFromAllFiles':
        /* Find all the files for which the current user is registered as
         * collaborator and unregister him. */
        unregisterFromAllFiles($_SESSION['user']);

        echo formatJSEND('success');
        break;

    case 'removeSelectionAndChangesForAllFiles':
        $query = array('user' => $_SESSION['user'], 'filename' => '*');
        $entries = getDB()->select($query, 'selection');
        foreach($entries as $entry) {
            $entry->remove();
        }
        $entries = getDB()->select($query, 'change');
        foreach($entries as $entry) {
            $entry->remove();
        }
        echo formatJSEND('success');
        break;

    case 'removeServerTextForAllFiles':
        $entries = getDB()->select_group('text');
        foreach($entries as $entry) $entry->remove();
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

        /* If user is not already registerd for the given file, register him. */
        if (!isUserRegisteredForFile($_POST['filename'], $_SESSION['user'])) {
            $isRegistered = registerToFile($_POST['filename'], $_SESSION['user']);
            if (!$isRegistered) {
                // Should only be enabled when testing
                //echo formatJSEND('success', 'Not registered as collaborator for ' . $_POST['filename']);
                exit;
            }
        }

        $selection = json_decode($_POST['selection']);
        $query = array('user' => $_SESSION['user'], 'filename' => $_POST['filename']);
        $entry = getDB()->create($query, 'selection');
        $entry->put_value($selection);
        echo formatJSEND('success');
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
                    $data = array(
                        "selection" => $selection,
                        "color" => getColorForUser($user)
                    );
                    $usersAndSelections[$user] = $data;
                }
            }
        }

        echo formatJSEND('success', $usersAndSelections);
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

        /* If there is no server text for $filename or if there is still no or
        * only one user registered for $filename, set the server text equal
        * to the shadow. */
        $registeredUsersForFileCount = count(getRegisteredUsersForFile($filename));
        if (!existsServerText($filename) || $registeredUsersForFileCount == 0) {
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

        $query = array('filename' => $_POST['filename']);
        $serverTextEntry = getDB()->select($query, 'text');
        if ($serverTextEntry == null) {
            exit(formatJSEND('error', 'Inconsistent sever text filename in synchronizeText: ' . $serverTextEntry));
        }

        $query = array('user' => $_SESSION['user'], 'filename' => $_POST['filename']);
        $shadowTextEntry = getDB()->select($query, 'shadow');
        if ($shadowTextEntry == null) {
            exit(formatJSEND('error', 'Inconsistent sever text filename in synchronizeText: ' . $shadowTextEntry));
        }

        /* First acquire a lock or wait until a lock can be acquired for server
         * text and shadow. */
        $serverTextEntry->lock();
        $shadowTextEntry->lock();

        $serverText = $serverTextEntry->get_value();
        $shadowText = $shadowTextEntry->get_value();

        $patchFromClient = $_POST['patch'];

        /* Patch the shadow and server texts with the edits from the client. */
        $dmp = new diff_match_patch();
        $patchedServerText = $dmp->patch_apply($dmp->patch_fromText($patchFromClient), $serverText);
        $serverTextEntry->put_value($patchedServerText[0]);

        $patchedShadowText = $dmp->patch_apply($dmp->patch_fromText($patchFromClient), $shadowText);

        /* Make a diff between server text and shadow to get the edits to send
         * back to the client. */
        $patchFromServer = $dmp->patch_toText($dmp->patch_make($patchedShadowText[0], $patchedServerText[0]));

        /* Apply it to the shadow. */
        $patchedShadowText = $dmp->patch_apply($dmp->patch_fromText($patchFromServer), $patchedShadowText[0]);
        $shadowTextEntry->put_value($patchedShadowText[0]);

        /* Release locks. */
        $serverTextEntry->unlock();
        $shadowTextEntry->unlock();

        echo formatJSEND('success', $patchFromServer);
        break;

    case 'sendHeartbeat':
        /* Hard coded heartbeat time interval. Beware to keep this value here
        * twice the value on client side. */
        $maxHeartbeatInterval = 5;
        $currentTime = time();

        /* Check if the user is a new user, or if it is just an update of
         * his heartbeat. */
        $isUserNewlyConnected = true;

        $query = array('user' => $_SESSION['user']);
        $entry = getDB()->select($query, 'heartbeat');
        if($entry != null) {
            $heartbeatTime = $entry->get_value();
            $heartbeatInterval = $currentTime - $heartbeatTime;
            $isUserNewlyConnected = ($heartbeatInterval > 1.5*$maxHeartbeatInterval);

            /* If the user is newly connected and if the heartbeat file
             * exits, that mean that the user was the latest in the previous
             * collaborative session. We need to call the disconnect method
             * to clear the data relatives to the user before calling the
             * connect method. */
            if($isUserNewlyConnected) {
                onCollaboratorDisconnect($_SESSION['user']);
            }
        }

        updateHeartbeatMarker($_SESSION['user']);

        /* If the user is newly connected, we fire the
         * corresponding method. */
        if($isUserNewlyConnected) {
            onCollaboratorConnect($_SESSION['user']);
        }

        $usersAndHeartbeatTime = getUsersAndHeartbeatTime();
        foreach ($usersAndHeartbeatTime as $user => $heartbeatTime) {
            if (($currentTime - $heartbeatTime) > $maxHeartbeatInterval) {
                /* The $user heartbeat time is too old, consider him dead and
                 * remove his 'registered'  and 'heartbeat' marker files. */
                unregisterFromAllFiles($user);
                removeHeartbeatMarker($user);
                onCollaboratorDisconnect($user);
            }
        }

        /* Return the number of connected collaborators. */
        $collaboratorCount = count(getUsersAndHeartbeatTime());
        $data = array();
        $data['collaboratorCount'] = $collaboratorCount;
        echo formatJSEND('success', $data);
        break;

    default:
        exit(formatJSEND('error', 'Unknown Action ' . $_POST['action']));
    }

    // --------------------
    /* $filename must contain only the basename of the file. */
    function isUserRegisteredForFile($filename, $user) {
        $query = array('user' => $user, 'filename' => $filename);
        $entry = getDB()->select($query, 'registered');
        return ($entry != null);
    }

    /* Unregister the given user from all the files by removing his
     * 'registered' marker file. */
    function unregisterFromAllFiles($user) {
        $query = array('user' => $user, 'filename' => '*');
        $entries = getDB()->select($query, 'registered');
        foreach($entries as $entry) {
            $entry->remove();
        }
    }

    /* Register as a collaborator for the given filename. Return false if
    * failed. */
    function registerToFile($user, $filename) {
        $query = array('user' => $user, 'filename' => $filename);
        $entry = getDB()->select($query, 'registered');
        if ($entry != null) {
            debug('Warning: already registered as collaborator for ' . $filename);
            return true;
        } else {
            $entry = getDB()->create($query, 'registered');
            if ($entry != null) {
                return true;
            } else {
                debug('Error: unable to register as collaborator for ' . $filename);
                return false;
            }
        }
    }

    /* Touch the heartbeat marker file for the given user. Return true on
     * success, false on failure. */
    function updateHeartbeatMarker($user) {
        $query = array('user' => $user);
        $entry = getDB()->create($query, 'heartbeat');
        if($entry == null) return false;
        $entry->put_value(time());
        return true;
    }

    function removeHeartbeatMarker($user) {
        $query = array('user' => $user);
        $entry = getDB()->select($query, 'heartbeat');
        if($entry != null) $entry->remove();
    }

    /* Return an array containing the user as key and his last heartbeat time
     * as value. */
    function &getUsersAndHeartbeatTime() {
        $usersAndHeartbeatTime = array();
        $query = array('user' => '*');
        $entries = getDB()->select($query, 'heartbeat');
        foreach($entries as $entry) {
            $user = $entry->get_field('user');
            $usersAndHeartbeatTime[$user] = $entry->get_value();
        }
        return $usersAndHeartbeatTime;
    }

    /* $filename must contain only the basename of the file. */
    function &getRegisteredUsersForFile($filename) {
        $usernames = array();
        $query = array('user' => '*', 'filename' => $filename);
        $entries = getDB()->select($query, 'registered');
        foreach($entries as $entry) {
            $user = $entry->get_field('user');
            $usernames[] = $user;
        }
        return $usernames;
    }

    /* Return the selection object, if any, for the given filename and user.
     * $filename must contain only the basename of the file. */
    function getSelection($filename, $user) {
        $query = array('user' => $user, 'filename' => $filename);
        $entry = getDB()->select($query, 'selection');
        if($entry == null) return null;
        return $entry->get_value();
    }

    /* Return the list of changes, if any, for the given filename, user and
     * from the given revision number.
     * $filename must contain only the basename of the file. */
    function getChanges($filename, $user, $fromRevision) {
        $query = array('user' => $user, 'filename' => $filename);
        $entry = getDB()->select($query, 'change');
        if($entry == null) return null;
        return array_slice($entry->get_value(), $fromRevision, NULL, true);
    }

    /* Set the server shadow acquiring an exclusive lock on the file. $shadow
     * is a string. */
    function setShadow($filename, $user, $shadow) {
        $query = array('user' => $user, 'filename' => $filename);
        $entry = getDB()->create($query, 'shadow');
        if($entry == null) return null;
        $entry->put_value($shadow);
    }

    /* Return the shadow for the given filename as a string or an empty string
     * if no shadow exists. */
    function getShadow($filename, $user) {
        $query = array('user' => $user, 'filename' => $filename);
        $entry = getDB()->select($query, 'shadow');
        if($entry == null) return null;
        return $entry->get_value();
    }

    function existsServerText($filename) {
        $query = array('filename' => $filename);
        $entry = getDB()->select($query, 'text');
        return ($entry != null);
    }

    /* Set the server text acquiring an exclusive lock on the file. $serverText
     * is a string. */
    function setServerText($filename, $serverText) {
        $query = array('filename' => $filename);
        $entry = getDB()->create($query, 'text');
        if($entry == null) return null;
        $entry->put_value($serverText);
    }

    /* Return the server text for the given filename as a string or an empty string
     * if no server text exists. */
    function getServerText($filename) {
        $query = array('filename' => $filename);
        $entry = getDB()->select($query, 'text');
        if($entry == null) return null;
        return $entry->get_value();
    }

    /* Return the color of the given user. */
    function getColorForUser($user) {
        /* Check if the color is already defined for the
         * user. */
        $query = array('user' => $user);
        $entry = getDB()->select($query, 'color');
        if ($entry != null) {
            return $entry->get_value();
        }

        /* If the color is not defined for the given user,
         * we pick an unused color. */
        $colors = array(
            "#0000FF",
            "#FF0000",
            "#00FF00",
            "#FF00FF",
            "#0F00F0",
            "#F0000F",
            "#0F0F0F",
            "#F0F0F0"
        );

        /* Retreive all used colors. */
        $query = array('user' => '*');
        $entries = getDB()->select($query, 'color');
        $usedColors = array();
        foreach ($entries as $entry) {
            $usedColors[] = $entry->get_value();
        }

        $colors = array_diff($colors, $usedColors);

        if(count($colors) > 0) {
            $color = array_shift($colors);
        }
        else {
            $color = "#FFFFFF";
        }

        /* Save the picked color. */
        $query = array('user' => $user);
        $entry = getDB()->create($query, 'color');
        $entry->put_value($color);

        return $color;
    }

    /* Remove the color file for the given user. */
    function resetColorForUser($user) {
        $query = array('user' => $user);
        $entry = getDB()->create($query, 'color');
        if($entry != null) $entry->remove();
    }

    /* This function is called when a new collaborator
    /* is connected. */
    function onCollaboratorConnect($user) {
        //debug('User connected: '.$user);
    }

    /* This function is called when a collaborator is
     * disconnected. */
    function onCollaboratorDisconnect($user) {
        //debug('User disconnected: '.$user);
        resetColorForUser($user);
    }

?>
