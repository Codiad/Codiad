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

    public $name         = '';
    public $path         = '';
    public $gitrepo      = false;
    public $gitbranch    = '';
    public $projects     = '';
    public $no_return    = false;
    public $assigned     = false;
    public $command_exec = '';

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
        if($this->ValidateAbsPath()) {
            $pass = $this->checkDuplicate();
            if($pass){
                if($this->path[0] !== '/') {
                    mkdir(WORKSPACE . '/' . $this->path);
                } else {
                    if(!file_exists($this->path)) {
                        if(!mkdir($this->path.'/', 0755, true)) {
                            die(formatJSEND("error","Unable to create Absolute Path"));
                        }
                    }
                }
                $this->projects[] = array("name"=>$this->name,"path"=>$this->path);
                saveJSON('projects.php',$this->projects);
                
                // Pull from Git Repo?
                if($this->gitrepo){
                    if($this->path[0] !== '/') {
                        $this->command_exec = "cd " . WORKSPACE . '/' . $this->path . " && git init && git remote add origin " . $this->gitrepo . " && git pull origin " . $this->gitbranch;
                    } else {
                        $this->command_exec = "cd " . $this->path . " && git init && git remote add origin " . $this->gitrepo . " && git pull origin " . $this->gitbranch;
                    }
                    $this->ExecuteCMD();
                }
                
                echo formatJSEND("success",array("name"=>$this->name,"path"=>$this->path));
            }else{
                echo formatJSEND("error","A Project With the Same Name or Path Exists");
            }
         } else {
            echo formatJSEND("error","Not a valid Absolute Path");
         }
    }

    //////////////////////////////////////////////////////////////////
    // Delete Project
    //////////////////////////////////////////////////////////////////

    public function Delete(){
        $revised_array = array();
        foreach($this->projects as $project=>$data){
            if($data['path']!=$this->path){
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
    // Validate Path
    //////////////////////////////////////////////////////////////////

    public function ValidateAbsPath(){
        $pass = true;
        if($this->path[0] !== '/' && strpos($this->path, "/") !== false) {
            $pass = false;
        }
        return $pass;
    }

    //////////////////////////////////////////////////////////////////
    // Sanitize Path
    //////////////////////////////////////////////////////////////////

    public function SanitizePath(){
        $sanitized = str_replace(" ","_",$this->path);

        // prevent Poison Null Byte injections
        $sanitized = str_replace(chr(0), '', $sanitized );

        // prevent go out of the workspace
        while (strpos($sanitized , '../') !== false)
            $sanitized = str_replace( '../', '', $sanitized );
            
        if(substr($sanitized, -1) == '/') {
            $sanitized = substr($sanitized,0, strlen($sanitized)-1);
        }    

        return preg_replace('/[^\w-\/]/', '', $sanitized);
    }
    
    //////////////////////////////////////////////////////////////////
    // Execute Command
    //////////////////////////////////////////////////////////////////
    
    public function ExecuteCMD(){
        if(function_exists('system')){
            ob_start();
            system($this->command_exec);
            ob_end_clean();
        }
        //passthru
        else if(function_exists('passthru')){
            ob_start();
            passthru($this->command_exec);
            ob_end_clean();
        }
        //exec
        else if(function_exists('exec')){
            exec($this->command_exec , $this->output);
        }
        //shell_exec
        else if(function_exists('shell_exec')){
            shell_exec($this->command_exec);
        }
    }

}
