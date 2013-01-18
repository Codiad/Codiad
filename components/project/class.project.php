<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

class Project {

    //////////////////////////////////////////////////////////////////
    // PROPERTIES
    //////////////////////////////////////////////////////////////////

    public $name        = '';
    public $path        = '';
    public $projects    = '';
    public $no_return   = false;
    public $assigned    = false;

    //////////////////////////////////////////////////////////////////
    // METHODS
    //////////////////////////////////////////////////////////////////

    // -----------------------------||----------------------------- //

    //////////////////////////////////////////////////////////////////
    // Construct
    //////////////////////////////////////////////////////////////////

    public function __construct(){
        $this->projects = getJSON('projects.php');
        if(file_exists(BASE_PATH . "/data/" . $_SESSION['user'] . '_acl.php')){
            $this->assigned = getJSON($_SESSION['user'] . '_acl.php');
        }
    }

    //////////////////////////////////////////////////////////////////
    // Get First (Default, none selected)
    //////////////////////////////////////////////////////////////////

    public function GetFirst(){

        $projects_assigned = false;
        if($this->assigned){
            foreach($this->projects as $project=>$data){
                if(in_array($data['path'],$this->assigned)){
                    $this->name = $data['name'];
                    $this->path = $data['path'];
                    break;
                }
            }
        }else{
            $this->name = $this->projects[0]['name'];
            $this->path = $this->projects[0]['path'];
        }
        // Set Sessions
        $_SESSION['project'] = $this->path;

        if(!$this->no_return){
            echo formatJSEND("success",array("name"=>$this->name,"path"=>$this->path));
        }
    }

    //////////////////////////////////////////////////////////////////
    // Get Name From Path
    //////////////////////////////////////////////////////////////////

    public function GetName(){
        foreach($this->projects as $project=>$data){
            if($data['path']==$this->path){
                $this->name = $data['name'];
            }
        }
        return $this->name;
    }

    //////////////////////////////////////////////////////////////////
    // Open Project
    //////////////////////////////////////////////////////////////////

    public function Open(){
        $pass = false;
        foreach($this->projects as $project=>$data){
            if($data['path']==$this->path){
                $pass = true;
                $this->name = $data['name'];
                $_SESSION['project'] = $data['path'];
            }
        }
        if($pass){
            echo formatJSEND("success",array("name"=>$this->name,"path"=>$this->path));
        }else{
            echo formatJSEND("error","Error Opening Project");
        }
    }

    //////////////////////////////////////////////////////////////////
    // Create
    //////////////////////////////////////////////////////////////////

    public function Create(){
        $this->path = $this->SanitizePath();
        $pass = $this->checkDuplicate();
        if($pass){
            $this->projects[] = array("name"=>$this->name,"path"=>$this->path);
            saveJSON('projects.php',$this->projects);
            mkdir(WORKSPACE . "/" . $this->path);
            echo formatJSEND("success",array("name"=>$this->name,"path"=>$this->path));
        }else{
            echo formatJSEND("error","A Project With the Same Name or Path Exists");
        }
    }

    //////////////////////////////////////////////////////////////////
    // Delete Project
    //////////////////////////////////////////////////////////////////

    public function Delete(){
        $revised_array = array();
        foreach($this->projects as $project=>$data){
            if($data['path']!=str_replace("/","",$this->path)){
                $revised_array[] = array("name"=>$data['name'],"path"=>$data['path']);
            }
        }
        // Save array back to JSON
        saveJSON('projects.php',$revised_array);
        // Response
        echo formatJSEND("success",null);
    }


    //////////////////////////////////////////////////////////////////
    // Check Duplicate
    //////////////////////////////////////////////////////////////////

    public function CheckDuplicate(){
        $pass = true;
        foreach($this->projects as $project=>$data){
            if($data['name']==$this->name || $data['path']==$this->path){
                $pass = false;
            }
        }
        return $pass;
    }

    //////////////////////////////////////////////////////////////////
    // Sanitize Path
    //////////////////////////////////////////////////////////////////

    public function SanitizePath(){
        $sanitized = str_replace(" ","_",$this->name);
        return preg_replace('/[^\w-]/', '', $sanitized);
    }

}
