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
    public $commits = "";
    public $archive = "";

    //////////////////////////////////////////////////////////////////
    // METHODS
    //////////////////////////////////////////////////////////////////

    // -----------------------------||----------------------------- //

    //////////////////////////////////////////////////////////////////
    // Construct
    //////////////////////////////////////////////////////////////////

    public function __construct(){
        ini_set("user_agent" , "Codiad");
        $this->remote = "https://api.github.com/repos/Codiad/Codiad/tags";
        //$this->remote = "http://update.codiad.com/?v={VER}&o={OS}&p={PHP}&i={IP}";
        $this->commits = "https://api.github.com/repos/Codiad/Codiad/commits";
        $this->archive = "https://github.com/Codiad/Codiad/archive/master.zip";
    }

    //////////////////////////////////////////////////////////////////
    // Set Initial Version
    //////////////////////////////////////////////////////////////////

    public function Init() {
        $version = array();
        if(!file_exists(DATA ."/version.php")) {
            $remote = $this->getRemoteVersion();
            if(file_exists(BASE_PATH."/.git/HEAD")) {
                $local = $this->getLocalVersion();
                $version[] = array("version"=>$local[0]['version'],"time"=>time(),"name"=>"");
                saveJSON('version.php',$version);
            } else {
                $version[] = array("version"=>$remote[0]["commit"]["sha"],"time"=>time(),"name"=>"");
                saveJSON('version.php',$version);
            }
        } else {
            $local = $this->getLocalVersion();
            if($local[0]['version'] == '' && $local[0]['name'] == $_SESSION['user']) {
                $remote = json_decode(file_get_contents($this->remote.'/HEAD'),true);
                $version[] = array("version"=>$remote["sha"],"time"=>time(),"name"=>$_SESSION['user']);
                saveJSON('version.php',$version);
            }
            if($local[0]['name'] == "git") {
                $app = getJSON('version.php');
                if($app[0]['version'] != $local[0]['version']) {
                    $version[] = array("version"=>$local[0]['version'],"time"=>time(),"name"=>"");
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
        $local = $this->getLocalVersion();
        $remote = $this->getRemoteVersion($local[0]['version']);
        
        $nightly = true;
        $archive = Common::getConstant('ARCHIVEURL', $this->archive);
        $latest = '';
        
        foreach($remote as $tag) {
            if($latest == '') {
                $latest = $tag["name"];
                $archive = $tag["zipball_url"];
            }
            if($local[0]['version'] == $tag["commit"]["sha"]) {
                $local[0]['version'] = $tag["name"];
                $nightly = false;
                break;
            }
        }
                
        $search = array("\r\n", "\n", "\r");
        $replace = array(" ", " ", " ");

        $message = '';
        $merge = '';
        $commits = json_decode(file_get_contents(Common::getConstant('COMMITURL', $this->commits)),true);
        foreach($commits as $commit) {
            if($local[0]['version'] != $commit["sha"]) {
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

        return "[".formatJSEND("success",array("currentversion"=>$local[0]['version'],"remoteversion"=>$latest,"message"=>$message,"archive"=>$archive,"nightly"=>$nightly,"name"=>$local[0]['name']))."]";
    }
        
    //////////////////////////////////////////////////////////////////
    // Get Local Version
    //////////////////////////////////////////////////////////////////
    
    public function getLocalVersion() {
        if(file_exists(BASE_PATH."/.git/HEAD")) {
            $tmp = file_get_contents(BASE_PATH."/.git/HEAD");
            if (strpos($tmp,"ref:") === false) {
                $data[0]['version'] = trim($tmp);
            } else {
                $data[0]['version'] = trim(file_get_contents(BASE_PATH."/.git/".trim(str_replace('ref: ', '', $tmp))));
            }
            $data[0]['name'] = "git";
        } else {
            $data = getJSON('version.php');
        }
        return $data;
    }
        
    //////////////////////////////////////////////////////////////////
    // Get Remote Version
    //////////////////////////////////////////////////////////////////
        
    public function getRemoteVersion($localversion = "") {
        $remoteurl = Common::getConstant('UPDATEURL', $this->remote);
        $remoteurl = str_replace("{OS}", PHP_OS);
        $remoteurl = str_replace("{PHP}", phpversion());
        $remoteurl = str_replace("{IP}", $_SERVER['REMOTE_ADDR']);
        $remoteurl = str_replace("{VER}", $localversion);     
        
        return json_decode(file_get_contents($remoteurl),true);
    }

}
