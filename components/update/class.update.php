<?php

/*
*  Copyright (c) Codiad & daeks (codiad.com), distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

class Update {

    //////////////////////////////////////////////////////////////////
    // PROPERTIES
    //////////////////////////////////////////////////////////////////
    
    public $remote = "";
    public $archive = "";
    public $commit = "";

    //////////////////////////////////////////////////////////////////
    // METHODS
    //////////////////////////////////////////////////////////////////

    // -----------------------------||----------------------------- //

    //////////////////////////////////////////////////////////////////
    // Construct
    //////////////////////////////////////////////////////////////////

    public function __construct(){
        ini_set("user_agent" , "Codiad");
        $this->remote = 'https://api.github.com/repos/Codiad/Codiad/commits';
        $this->archive = 'https://github.com/Codiad/Codiad/archive/master.zip';
    }
    
    //////////////////////////////////////////////////////////////////
    // Set Initial Version
    //////////////////////////////////////////////////////////////////
    
    public function Init() {
        $version = array();
        if(!file_exists(DATA ."/version.php")) {
            if(file_exists(BASE_PATH."/.git/FETCH_HEAD")) {
                $data = file(BASE_PATH."/.git/FETCH_HEAD");
                foreach($data as $line) {
                    $branch = explode("	", $line);
                    if(strpos($branch[2], "master") !== false) {
                        break;
                    }
                }
                $version[] = array("version"=>$branch[0],"time"=>time(),"name"=>"");
                saveJSON('version.php',$version);
            } else {
                $remote = json_decode(file_get_contents($this->remote.'/HEAD'),true);
                $version[] = array("version"=>$remote["sha"],"time"=>time(),"name"=>"");
                saveJSON('version.php',$version);
            }
        } else {
            $app = getJSON('version.php');
            if($app[0]['version'] == '' && $app[0]['name'] == $_SESSION['user']) {
                $remote = json_decode(file_get_contents($this->remote.'/HEAD'),true);
                $version[] = array("version"=>$remote["sha"],"time"=>time(),"name"=>$_SESSION['user']);
                saveJSON('version.php',$version);
            }
            if(file_exists(BASE_PATH."/.git/FETCH_HEAD")) {
                $data = file(BASE_PATH."/.git/FETCH_HEAD");
                foreach($data as $line) {
                    $branch = explode("	", $line);
                    if(strpos($branch[2], "master") !== false) {
                        break;
                    }
                }
                if($app[0]['version'] != $branch[0]) {
                    $version[] = array("version"=>$branch[0],"time"=>time(),"name"=>"");
                    saveJSON('version.php',$version);
                }
            }
        }
    }
    
    //////////////////////////////////////////////////////////////////
    // Clear Version
    //////////////////////////////////////////////////////////////////
    
    public function Clear() {
        $version[] = array("version"=>"","time"=>time(),"name"=>$_SESSION['user']);
        saveJSON('version.php',$version);
    }   
    
    //////////////////////////////////////////////////////////////////
    // Check Version
    //////////////////////////////////////////////////////////////////
    
    public function Check() {
        if(file_exists(BASE_PATH."/.git/FETCH_HEAD")) {
            $data = file(BASE_PATH."/.git/FETCH_HEAD");
            foreach($data as $line) {
                $branch = explode("	", $line);
                if(strpos($branch[2], "master") !== false) {
                    break;
                }
            }
            $app[0]['version'] = $branch[0];
            $app[0]['name'] = "";
        } else {
            $app = getJSON('version.php');
        }
        
        if($this->remote != '') {
            $remote = json_decode(file_get_contents($this->remote),true);
        }
        
        $search = array("\r\n", "\n", "\r");
        $replace = array(" ", " ", " ");
        
        $message = '';
        $merge = '';
        foreach($remote as $commit) {
            if($app[0]['version'] != $commit["sha"]) {
                if(strpos($commit["commit"]["message"],"Merge") === false) {
                    $message .= '- '.str_replace($search,$replace,$commit["commit"]["message"]).'<br/>';
                } else {
                    $merge .= '- '.str_replace($search,$replace,$commit["commit"]["message"]).'<br/>';
                }
            } else {
                break;
            }
        }
        
        if($message == '') {
            $message = $merge;
        }
                
        return "[".formatJSEND("success",array("currentversion"=>$app[0]['version'],"remoteversion"=>$remote[0]["sha"],"message"=>$message,"archive"=>$this->archive,"name"=>$app[0]['name']))."]";
    }
    
}
