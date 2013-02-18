<?php

/*
*  Copyright (c) Luc Verdier & Florent Galland, distributed
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


/* */
class file_db {
    
    public $separator = '%%%';
    public $key_value_separator = ':%:';
    
    private $base_path;
    private $null_ref = null;
    
    function __construct($base_path) {
        $this->base_path = $base_path;
        if(!is_dir($base_path)) {
            mkdir($base_path);
        }
    }
    
    /* Create a new entry into the data base. */
    public function &create($query, $group=null) {
        if(!$this->_is_direct_query($query)) {
            return $this->null_ref;
        }
        $base_path = $this->base_path;
        if($group != null) {
            $base_path .= '/' . $group;
        }
        if(!is_dir($base_path)) {
            mkdir($base_path);
        }
        $query = $this->_sanitize_query($query);
        ksort($query);
        $filename = $this->_make_file_name($query);
        $entry = new file_db_entry($this, $this->base_path, $filename, $group);
        $entry->clear();
        if(file_exists($base_path . '/' . $filename)) {
            return $entry;
        }
        return $this->null_ref;
    }

    /* Get the content for the given query. */
    public function &select($query, $group=null) {
        $query = $this->_sanitize_query($query);
        ksort($query);
        $base_path = $this->base_path;
        if($group != null) {
            $base_path .= '/' . $group;
        }
        if($this->_is_direct_query($query)) {
            $filename = $this->_make_file_name($query);
            if(file_exists($base_path . '/' . $filename)) {
                return new file_db_entry($this, $this->base_path, $filename, $group);
            }
            return $this->null_ref;
        }
        $entries = array();
        $regex = $this->_make_regex($query);
        if ($handle = opendir($base_path)) {
            while (false !== ($entry = readdir($handle))) {
                if (preg_match($regex, $entry)) {
                    $entries[] = new file_db_entry($this, $this->base_path, $entry, $group);
                }
            }
        }
        return $entries;
    }
    
    /* Select all entries into the given group. */
    public function &select_group($group) {
        $entries = array();
        $base_path = $this->base_path . '/' . $group;
         if ($handle = opendir($base_path)) {
            while (false !== ($entry = readdir($handle))) {
                if(strpos($entry, $this->separator) === 0) {
                    $entries[] = new file_db_entry($this, $this->base_path, $entry, $group);
                }
            }
        }
        return $entries;
    }
    
    /* Make the regex for the given query. */
    private function _make_regex($query) {
        $regex = '/' . $this->separator;
        foreach($query as $key => $value) {
            $regex .= $key . $this->key_value_separator;
            if($value == '*') {
                $regex .= '.*?';
            }
            else {
                $regex .= $value;
            }
            $regex .= $this->separator;
        }
        return $regex . '/';
    }
    
    /* Make a file name from the given sorted query. */
    private function _make_file_name($query) {
        $filename = $this->separator;
        foreach($query as $key => $value) {
            $filename .= $key . $this->key_value_separator . $value;
            $filename .= $this->separator;
        }
        return $filename;
    }
    
    /* Check if the given query is a direct query. */
    private function _is_direct_query($query) {
        foreach($query as $key => $value) {
            if($value == '*'){
                return false;
            }
        }
        return true;
    }
    
    /* Sanitize the given query. */
    private function &_sanitize_query(&$query) {
        foreach($query as $key => $value) {
            $query[$key] = str_replace('/', '_', $value);
        }
        return $query;
    }
    
}

/* */
class file_db_entry {
    
    private $separator;
    private $key_value_separator;
    
    private $base_path;
    private $filename;
    private $full_filename;
    private $group;
    
    /* Construct the entry with the given filename. */
    public function __construct($db, $base_path, $filename, $group) {
        $this->separator = $db->separator;
        $this->key_value_separator = $db->key_value_separator;
        $this->base_path = $base_path;
        $this->filename = $filename;
        $this->group = $group;
        if($group != null) {
            $this->full_filename = $base_path . '/' . $group . '/' . $filename;
        }
        else {
            $this->full_filename = $base_path . '/' . $filename;
        }
    }
    
    /* Get the value of the field with the given name. */
    public function get_field($name) {
        $regex = '/' . $this->separator . $name;
        $regex .= $this->key_value_separator;
        $regex .= '(.*?)' . $this->separator . '/';
        if(preg_match($regex, $this->filename, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    /* Get the group name of the entry. */
    public function get_group() {
        return $this->group;
    }
    
    /* Set the value of the entry. */
    public function put_value($value) {
        file_put_contents($this->full_filename, serialize($value), LOCK_EX);
    }
    
    /* Get the value of the entry. */
    public function &get_value() {
        if(file_exists($this->full_filename)) {
            return unserialize(file_get_contents($this->full_filename));
        }
        return null;
    }
    
    /* Remove the entry. */
    public function remove() {
        if(file_exists($this->full_filename)) {
            unlink($this->full_filename);
        }
    }
    
    /* Clear the value of the entry. */
    public function clear() {
        $this->put_value('');
    }
    
    /* Lock the entry. */
    public function lock() {
        if(file_exists($this->full_filename)) {
            flock($this->full_filename, LOCK_EX);
        }
    }
    
    /* Unlock the entry. */
    public function unlock() {
        if(file_exists($this->full_filename)) {
            flock($this->full_filename, LOCK_UN);
        }
    }
}

?>