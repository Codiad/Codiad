<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

class User {

    //////////////////////////////////////////////////////////////////
    // PROPERTIES
    //////////////////////////////////////////////////////////////////

    public $username    = '';
    public $password    = '';
    public $challenge   = '';
    public $project     = '';
    public $projects    = '';
    public $users       = '';
    public $challenges  = '';
    public $actives     = '';
    public $lang        = '';
    public $theme       = '';

    //////////////////////////////////////////////////////////////////
    // METHODS
    //////////////////////////////////////////////////////////////////

    // -----------------------------||----------------------------- //

    //////////////////////////////////////////////////////////////////
    // Construct
    //////////////////////////////////////////////////////////////////

    public function __construct(){
        $this->users = getJSON('users.php');
        $this->actives = getJSON('active.php');
        $this->challenges = getJSON('challenges.php','cache');
    }

    //////////////////////////////////////////////////////////////////
    // Authenticate
    //////////////////////////////////////////////////////////////////

    public function Authenticate(){

        $pass = false;
        $challenges = getJSON('challenges.php','cache');
        $revised_array = array();
        foreach($challenges as $challenge){
            if($challenge['username']==$this->username){
                $this->challenge = $challenge['challenge'];
            } else {
               $revised_array[] = array("username"=>$challenge['username'],"challenge"=>$challenge['challenge']);
            }
        }
        saveJSON('challenges.php',$revised_array, 'cache');

        $users = getJSON('users.php');        
        foreach($users as $user){
            if($user['username']==$this->username && md5($user['password'].$this->challenge)==$this->password){
                $pass = true;
                $_SESSION['user'] = $this->username;
                $_SESSION['lang'] = $this->lang;
                $_SESSION['theme'] = $this->theme;
                if($user['project']!=''){ $_SESSION['project'] = $user['project']; }
            }
        }

        if($pass){ echo formatJSEND("success",array("username"=>$this->username)); }
        else{ echo formatJSEND("error","Incorrect Username or Password "); }
    }
    
    //////////////////////////////////////////////////////////////////
    // Get Challenge
    //////////////////////////////////////////////////////////////////

    public function Challenge(){
        $revised_array = array();
        $this->challenge = time();
        $pass = false;
        foreach($this->challenges as $challenges=>$data){
            if($data['username']==$this->username){
                $revised_array[] = array("username"=>$data['username'],"challenge"=>$this->challenge);
                $pass = true;
            }else{
                $revised_array[] = array("username"=>$data['username'],"challenge"=>$data['challenge']);
            }
        }
        if(!$pass) {
            array_push($revised_array,array("username"=>$this->username,"challenge"=>$this->challenge));
        }        
        // Save array back to JSON
        saveJSON('challenges.php',$revised_array, 'cache');
        // Response
        echo formatJSEND("success",array("challenge"=>$this->challenge));
    }

    //////////////////////////////////////////////////////////////////
    // Create Account
    //////////////////////////////////////////////////////////////////

    public function Create(){
        $this->EncryptPassword();
        $pass = $this->checkDuplicate();
        if($pass){
            $this->users[] = array("username"=>$this->username,"password"=>$this->password,"project"=>"");
            saveJSON('users.php',$this->users);
            echo formatJSEND("success",array("username"=>$this->username));
        }else{
            echo formatJSEND("error","The Username is Already Taken");
        }
    }

    //////////////////////////////////////////////////////////////////
    // Delete Account
    //////////////////////////////////////////////////////////////////

    public function Delete(){
        // Remove User
        $revised_array = array();
        foreach($this->users as $user=>$data){
            if($data['username']!=$this->username){
                $revised_array[] = array("username"=>$data['username'],"password"=>$data['password'],"project"=>$data['project']);
            }
        }
        // Save array back to JSON
        saveJSON('users.php',$revised_array);

        // Remove any active files
        foreach($this->actives as $active=>$data){
            if($this->username==$data['username']){
                unset($this->actives[$active]);
            }
        }
        saveJSON('active.php',$this->actives);

        // Remove access control list (if exists)
        if(file_exists(BASE_PATH . "/data/" . $this->username . '_acl.php')){
            unlink(BASE_PATH . "/data/" . $this->username . '_acl.php');
        }

        // Response
        echo formatJSEND("success",null);
    }

    //////////////////////////////////////////////////////////////////
    // Change Password
    //////////////////////////////////////////////////////////////////

    public function Password(){
        $this->EncryptPassword();
        $revised_array = array();
        foreach($this->users as $user=>$data){
            if($data['username']==$this->username){
                $revised_array[] = array("username"=>$data['username'],"password"=>$this->password);
            }else{
                $revised_array[] = array("username"=>$data['username'],"password"=>$data['password'],"project"=>$data['project']);
            }
        }
        // Save array back to JSON
        saveJSON('users.php',$revised_array);
        // Response
        echo formatJSEND("success",null);
    }

    //////////////////////////////////////////////////////////////////
    // Set Project Access
    //////////////////////////////////////////////////////////////////

    public function Project_Access(){
        // Access set to all projects
        if($this->projects==0){
            if(file_exists(BASE_PATH . "/data/" . $this->username . '_acl.php')){
                unlink(BASE_PATH . "/data/" . $this->username . '_acl.php');
            }
        // Access set to restricted list
        }else{
            // Save array back to JSON
            saveJSON($this->username . '_acl.php',$this->projects);
        }
        // Response
        echo formatJSEND("success",null);
    }

    //////////////////////////////////////////////////////////////////
    // Set Current Project
    //////////////////////////////////////////////////////////////////

    public function Project(){
        $revised_array = array();
        foreach($this->users as $user=>$data){
            if($this->username==$data['username']){
                $revised_array[] = array("username"=>$data['username'],"password"=>$data['password'],"project"=>$this->project);
            }else{
                $revised_array[] = array("username"=>$data['username'],"password"=>$data['password'],"project"=>$data['project']);
            }
        }
        // Save array back to JSON
        saveJSON('users.php',$revised_array);
        // Response
        echo formatJSEND("success",null);
    }

    //////////////////////////////////////////////////////////////////
    // Check Duplicate
    //////////////////////////////////////////////////////////////////

    public function CheckDuplicate(){
        $pass = true;
        foreach($this->users as $user=>$data){
            if($data['username']==$this->username){
                $pass = false;
            }
        }
        return $pass;
    }

    //////////////////////////////////////////////////////////////////
    // Verify Account Exists
    //////////////////////////////////////////////////////////////////

    public function Verify(){
        $pass = 'false';
        foreach($this->users as $user=>$data){
            if($this->username==$data['username']){
                $pass = 'true';
            }
        }
        echo($pass);
    }

    //////////////////////////////////////////////////////////////////
    // Encrypt Password
    //////////////////////////////////////////////////////////////////

    private function EncryptPassword(){
        $this->password = sha1(md5($this->password));
    }

    //////////////////////////////////////////////////////////////////
    // Clean username
    //////////////////////////////////////////////////////////////////

    public static function CleanUsername( $username ){
        return preg_replace('#[^A-Za-z0-9'.preg_quote('-_@. ').']#','', $username);
    }

}
