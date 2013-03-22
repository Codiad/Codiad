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
    
    /* They should be the same as those
     * in the file_db_entry class. */
    private $separator = '|';
    private $separator_regex = '\|';
    private $key_value_separator = ':';
    private $key_value_separator_regex = '\:';
    
    private $index_name = 'index.db';
    private $base_path;
    
    function __construct($base_path) {
        $this->base_path = $base_path;
        if(!is_dir($base_path)) {
            mkdir($base_path);
        }
    }
    
    /* Create a new entry into the data base. */
    public function create($query, $group=null) {
        $query = $this->_normalize_query($query);
        
        if(!$this->_is_direct_query($query)) {
            return null;
        }
        
        $base_path = $this->base_path;
        if($group != null) {
            $base_path .= '/' . $group;
        }
        $index_file = $base_path . '/' . $this->index_name;
        
        if(!is_dir($base_path)) {
            mkdir($base_path);
        }
        
        $entry_name = $this->_make_entry_name($query);
        $entry_hash = md5($entry_name);
        $entry_file = $base_path . '/' . $entry_hash;
        
        if(!file_exists($entry_file)) {
            if(!file_exists($index_file)) {
                touch($index_file);
            }
            
            $entry = $entry_name . '>' . $entry_hash . '>' . PHP_EOL;
            file_put_contents($index_file, $entry, FILE_APPEND | LOCK_EX);
            touch($entry_file);
        }
        
        $entry = new file_db_entry($entry_name, $entry_file, $index_file, $group);
        $entry->clear();
        
        if(file_exists($entry_file)) {
            return $entry;
        }
        return null;
    }

    /* Get the content for the given query. */
    public function select($query, $group=null) {
        $query = $this->_normalize_query($query);
        
        $base_path = $this->base_path;
        if($group != null) {
            $base_path .= '/' . $group;
        }
        $index_file = $base_path . '/' . $this->index_name;
        
        if($this->_is_direct_query($query)) {
            $entry_name = $this->_make_entry_name($query);
            $entry_hash = md5($entry_name);
            $entry_file = $base_path . '/' . $entry_hash;
            
            if(file_exists($entry_file)) {
                return new file_db_entry($entry_name, $entry_file, $index_file, $group);
            }
            return null;
        }
        
        $entries = array();
        if(file_exists($index_file)) {
            $regex = $this->_make_regex($query);
            $file = fopen($index_file, 'r');
            while(!feof($file)) { 
                $line = fgets($file);
                if (preg_match($regex, $line, $matches)) {
                    $entry_file = $base_path . '/' . $matches[2];
                    if(file_exists($entry_file)) {
                        $entries[] = new file_db_entry($matches[1], $entry_file, $index_file, $group);
                    }
                }
            }
            fclose($file);
        }
        
        return $entries;
    }
    
    /* Select all entries into the given group. */
    public function select_group($group) {
        $entries = array();
        
        $base_path = $this->base_path . '/' . $group;
        $index_file = $base_path . '/' . $this->index_name;
        
        if(file_exists($index_file)) {
            $sep = $this->separator_regex;
            $regex = '/(' . $sep . '.*?' . $sep . ')\>(.*?)\>/'; 
            $file = fopen($index_file, 'r');
            while(!feof($file)) { 
                $line = fgets($file);
                if (preg_match($regex, $line, $matches)) {
                    $entry_file = $base_path . '/' . $matches[2];
                    $entries[] = new file_db_entry($matches[1], $entry_file, $index_file, $group);
                }
            }
            fclose($file);
        }
        
        return $entries;
    }
    
    /* Make the regex for the given query. */
    private function _make_regex($query) {
        $regex = '/(' . $this->separator_regex;
        foreach($query as $key => $value) {
            $regex .= $key . $this->key_value_separator_regex;
            if($value == '%2A') { // %2A=*
                $regex .= '.*?';
            }
            else {
                $regex .= $value;
            }
            $regex .= $this->separator_regex;
        }
        $regex .= ')' . '\>(.*?)\>' . '/';
        return $regex;
    }
        
    /* Make an entry name from the given normalized query. */
    private function _make_entry_name($query) {
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
            if($value == '%2A'){ // %2A=*
                return false;
            }
        }
        return true;
    }
    
    /* Normalize the given query. */
    private function &_normalize_query(&$query) {
        ksort($query);
        foreach($query as $key => $value) {
            $query[$key] = rawurlencode($value);
        }
        return $query;
    }
    
}

/* */
class file_db_entry {
    
    /* They should be the same as those
     * in the file_db class. */
    private $separator = '|';
    private $separator_regex = '\|';
    private $key_value_separator = ':';
    private $key_value_separator_regex = '\:';
    
    private $entry_name;
    private $entry_hash;
    private $entry_file;
    private $index_file;
    private $group;
    
    private $handler;
    
    /* Construct the entry with the given filename. */
    public function __construct($entry_name, $entry_file, $index_file, $group) {
        $this->entry_name = $entry_name;
        $this->entry_file = $entry_file;
        $this->index_file = $index_file;
        $this->group = $group;
    }
    
    /* Get the value of the field with the given name. */
    public function get_field($name) {
        $regex = '/'
            . $this->separator_regex 
            . rawurlencode($name)
            . $this->key_value_separator_regex
            . '(.*?)' . $this->separator_regex 
            . '/';
        if(preg_match($regex, $this->entry_name, $matches)) {
            return rawurldecode($matches[1]);
        }
        return null;
    }
    
    /* Get the group name of the entry. */
    public function get_group() {
        return $this->group;
    }
    
    /* Set the value of the entry. */
    public function put_value($value) {
        file_put_contents($this->entry_file, serialize($value), LOCK_EX);
    }
    
    /* Get the value of the entry. */
    public function get_value() {
        if(file_exists($this->entry_file)) {
            return unserialize(file_get_contents($this->entry_file));
        }
        return null;
    }
    
    /* Remove the entry. */
    public function remove() {
        $success = false;
        if(file_exists($this->index_file)) {
            $lines = file($this->index_file, FILE_SKIP_EMPTY_LINES);
            
            $file = fopen($this->index_file, 'w');
            flock($file, LOCK_EX);
            
            foreach($lines as $line) {
                if (strpos($line, $this->entry_name) !== 0) {
                    fwrite($file, $line);
                }
                else {
                    $success = true;
                }
            }
            
            flock($file, LOCK_UN);
            fclose($file);  
        }
        
        if($success && file_exists($this->entry_file)) {
            unlink($this->entry_file);
            return true;
        }
        return false;
    }
    
    /* Clear the value of the entry. */
    public function clear() {
        $this->put_value('');
    }
    
    /* Lock the entry. */
    public function lock() {
        $lock = $this->entry_file . '.lock';
        while(file_exists($lock)) {
            usleep(100);
        }
        touch($this->entry_file . '.lock');
    }
    
    /* Unlock the entry. */
    public function unlock() {
        $lock = $this->entry_file . '.lock';
        if(file_exists($lock)) {
            unlink($lock);
        }
    }
}

?>
