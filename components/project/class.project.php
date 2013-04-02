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
        $pass = $this->checkDuplicate();
        if($pass){
            $this->projects[] = array("name"=>$this->name,"path"=>$this->path);
            saveJSON('projects.php',$this->projects);
            mkdir(WORKSPACE . "/" . $this->path);
            
            // Pull from Git Repo?
            if($this->gitrepo){
                $this->command_exec = "cd " . WORKSPACE . "/" . $this->path . " && git init && git remote add origin " . $this->gitrepo . " && git pull origin " . $this->gitbranch;
                $this->ExecuteCMD();
            }
            
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

        // prevent Poison Null Byte injections
        $sanitized = str_replace(chr(0), '', $sanitized );

        // prevent go out of the workspace
        while (strpos($sanitized , '../') !== false)
            $sanitized = str_replace( '../', '', $sanitized );

        return preg_replace('/[^\w-]/', '', $sanitized);
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
