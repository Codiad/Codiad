<?php

    /*
    *  Copyright (c) Codiad & daeks, distributed
    *  as-is and without warranty under the MIT License. See 
    *  [root]/license.txt for more. This information must remain intact.
    */
require_once('../../common.php');
include(COMPONENTS.'/common/class.common.php');

class Tester extends Common {
  
    //////////////////////////////////////////////////////////////////
    // PROPERTIES
    //////////////////////////////////////////////////////////////////
    
    public $root            = '';
    public $remote_list     = '';
    public $request         = '';
    public $pull            = '';
    public $command_exec    = '';
    
    //////////////////////////////////////////////////////////////////
    // METHODS
    //////////////////////////////////////////////////////////////////

    // -----------------------------||----------------------------- //

    //////////////////////////////////////////////////////////////////
    // Construct
    //////////////////////////////////////////////////////////////////

    public function __construct(){
        ini_set("user_agent" , "Codiad");
        $this->remote_list = 'https://api.github.com/repos/Codiad/Codiad/pulls';
        $this->request = 'https://api.github.com/repos/Codiad/Codiad/pulls/';
    }
    
    //////////////////////////////////////////////////////////////////
    // get Pull Requests
    //////////////////////////////////////////////////////////////////
    
    public function Get_Requests(){
        return json_decode(file_get_contents($this->remote_list),true);
    }
    
    //////////////////////////////////////////////////////////////////
    // get Pull Request
    //////////////////////////////////////////////////////////////////
    
    public function Pull(){
        $pull = json_decode(file_get_contents($this->request.$this->pull),true);
        if(!$this->isAbsPath($this->root)) {
            $this->root = WORKSPACE . '/' . $this->root;
        }
        
        if(!file_exists($this->root.'/'.$this->pull)) {
            mkdir($this->root.'/'.$this->pull);
        } else {
            if(!is_dir($this->root.'/'.$this->pull)) {
                mkdir($this->root.'/'.$this->pull);
            } else {
                die(formatJSEND("error",array("message"=>"Folder already exists")));
            }
        }
        $this->command_exec = "cd " . $this->root.'/'.$this->pull . " && git init && git pull " . $pull['head']['repo']['clone_url'] . " " . $pull['head']['ref'];
        $this->ExecuteCMD();
        echo formatJSEND("success",array("message"=>"Pull Request cloned"));
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

?>
