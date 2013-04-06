<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
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
        $this->remote = 'https://api.github.com/repos/Codiad/Codiad/commits';
        $this->archive = 'https://github.com/Codiad/Codiad/archive/master.zip';
    }
    
    //////////////////////////////////////////////////////////////////
    // Set Initial Version
    //////////////////////////////////////////////////////////////////
    
    public function Init() {
        $version = array();
        if($this->remote != '' && !file_exists("version.json") && !file_exists("../../data/version.php")) {
            $remote = json_decode(file_get_contents($this->remote),true);
            $version[] = array("version"=>$remote[0]["sha"],"time"=>time(),"name"=>"");
            if(!file_put_contents("version.json",json_encode($version))) {
                saveJSON('version.php',$version);
            }
        } else {
            if(file_exists("../../data/version.php")) {
                $app = getJSON('version.php');
                if($app[0]['version'] == '' && $app[0]['name'] == $_SESSION['user']) {
                    $remote = json_decode(file_get_contents($this->remote),true);
                    $version[] = array("version"=>$remote[0]["sha"],"time"=>time(),"name"=>$_SESSION['user']);
                    saveJSON('version.php',$version);
                }
            } else if(file_exists("version.json")) {
                $app = json_decode(file_get_contents("version.json"),true);
                if($app[0]['version'] == '' && $app[0]['name'] == $_SESSION['user']) {
                    $remote = json_decode(file_get_contents($this->remote),true);
                    $version[] = array("version"=>$remote[0]["sha"],"time"=>time(),"name"=>$_SESSION['user']);
                    file_put_contents("version.json",json_encode($version));
                }
            }
        }
    }
    
    //////////////////////////////////////////////////////////////////
    // Clear Version
    //////////////////////////////////////////////////////////////////
    
    public function Clear() {
        $version[] = array("version"=>"","time"=>time(),"name"=>$_SESSION['user']);
        if(file_exists("../../data/version.php")) {
            saveJSON('version.php',$version);
        } else if(file_exists("version.json")) {
            file_put_contents("version.json",json_encode($version));
        }
    }   
    
    //////////////////////////////////////////////////////////////////
    // Check Version
    //////////////////////////////////////////////////////////////////
    
    public function Check() {
        if(file_get_contents("version.json")) {
            $app = json_decode(file_get_contents("version.json"),true);
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
