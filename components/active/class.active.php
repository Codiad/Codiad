<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

require_once('../../common.php');

class Active extends Common
{

    //////////////////////////////////////////////////////////////////
    // PROPERTIES
    //////////////////////////////////////////////////////////////////

    public $username    = "";
    public $path        = "";
    public $new_path    = "";
    public $actives     = "";

    //////////////////////////////////////////////////////////////////
    // METHODS
    //////////////////////////////////////////////////////////////////

    // -----------------------------||----------------------------- //

    //////////////////////////////////////////////////////////////////
    // Construct
    //////////////////////////////////////////////////////////////////

    public function __construct()
    {
        $this->actives = getJSON('active.php');
    }

    //////////////////////////////////////////////////////////////////
    // List User's Active Files
    //////////////////////////////////////////////////////////////////

    public function ListActive()
    {
        $active_list = array();
        $tainted = false;
        $root = WORKSPACE;
        if ($this->actives) {
            foreach ($this->actives as $active => $data) {
                if (is_array($data) && isset($data['username']) && $data['username']==$this->username) {
                    if ($this->isAbsPath($data['path'])) {
                        $root = "";
                    } else {
                        $root = $root.'/';
                    }
                    if (file_exists($root.$data['path'])) {
                        $focused = isset($data['focused']) ? $data['focused'] : false;
                        $active_list[] = array('path'=>$data['path'], 'focused'=>$focused);
                    } else {
                        unset($this->actives[$active]);
                        $tainted = true;
                    }
                }
            }
        }
        if ($tainted) {
            saveJSON('active.php', $this->actives);
        }
        echo formatJSEND("success", $active_list);
    }

    //////////////////////////////////////////////////////////////////
    // Check File
    //////////////////////////////////////////////////////////////////

    public function Check()
    {
        $cur_users = array();
        foreach ($this->actives as $active => $data) {
            if (is_array($data) && isset($data['username']) && $data['username']!=$this->username && $data['path']==$this->path) {
                $cur_users[] = $data['username'];
            }
        }
        if (count($cur_users)!=0) {
            echo formatJSEND("error", "Warning: File ".substr($this->path, strrpos($this->path, "/")+1)." Currently Opened By: " . implode(", ", $cur_users));
        } else {
            echo formatJSEND("success");
        }
    }

    //////////////////////////////////////////////////////////////////
    // Add File
    //////////////////////////////////////////////////////////////////

    public function Add()
    {
        $process_add = true;
        foreach ($this->actives as $active => $data) {
            if (is_array($data) && isset($data['username']) && $data['username']==$this->username && $data['path']==$this->path) {
                $process_add = false;
            }
        }
        if ($process_add) {
            $this->actives[] = array("username"=>$this->username,"path"=>$this->path);
            saveJSON('active.php', $this->actives);
            echo formatJSEND("success");
        }
    }

    //////////////////////////////////////////////////////////////////
    // Rename File
    //////////////////////////////////////////////////////////////////

    public function Rename()
    {
        $revised_actives = array();
        foreach ($this->actives as $active => $data) {
            if (is_array($data) && isset($data['username'])) {
                $revised_actives[] = array("username"=>$data['username'],"path"=>str_replace($this->path, $this->new_path, $data['path']));
            }
        }
        saveJSON('active.php', $revised_actives);
        echo formatJSEND("success");
    }

    //////////////////////////////////////////////////////////////////
    // Remove File
    //////////////////////////////////////////////////////////////////

    public function Remove()
    {
        foreach ($this->actives as $active => $data) {
            if (is_array($data) && isset($data['username']) && $this->username==$data['username'] && $this->path==$data['path']) {
                unset($this->actives[$active]);
            }
        }
        saveJSON('active.php', $this->actives);
        echo formatJSEND("success");
    }
    
    //////////////////////////////////////////////////////////////////
    // Remove All Files
    //////////////////////////////////////////////////////////////////

    public function RemoveAll()
    {
        foreach ($this->actives as $active => $data) {
            if (is_array($data) && isset($data['username']) && $this->username==$data['username']) {
                unset($this->actives[$active]);
            }
        }
        saveJSON('active.php', $this->actives);
        echo formatJSEND("success");
    }
    
    //////////////////////////////////////////////////////////////////
    // Mark File As Focused
    //  All other files will be marked as non-focused.
    //////////////////////////////////////////////////////////////////

    public function MarkFileAsFocused()
    {
        foreach ($this->actives as $active => $data) {
            if (is_array($data) && isset($data['username']) && $this->username==$data['username']) {
                $this->actives[$active]['focused']=false;
                if ($this->path==$data['path']) {
                    $this->actives[$active]['focused']=true;
                }
            }
        }
        saveJSON('active.php', $this->actives);
        echo formatJSEND("success");
    }
}
