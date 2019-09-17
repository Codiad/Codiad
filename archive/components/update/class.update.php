<?php

/*
*  Copyright (c) Codiad & daeks (codiad.com), distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

class Update
{

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

    public function __construct()
    {
        ini_set("user_agent", "Codiad");
        $this->remote = "http://update.codiad.com/?v={VER}&o={OS}&p={PHP}&w={WEB}&a={ACT}";
        $this->commits = "https://api.github.com/repos/Codiad/Codiad/commits";
        $this->archive = "https://github.com/Codiad/Codiad/archive/master.zip";
    }

    //////////////////////////////////////////////////////////////////
    // Set Initial Version
    //////////////////////////////////////////////////////////////////

    public function Init()
    {
        $version = array();
        if (!file_exists(DATA ."/version.php")) {
            if (file_exists(BASE_PATH."/.git/HEAD")) {
                $remote = $this->getRemoteVersion("install_git");
                $local = $this->getLocalVersion();
                $version[] = array("version"=>$local[0]['version'],"time"=>time(),"optout"=>"true","name"=>"");
                saveJSON('version.php', $version);
            } else {
                $remote = $this->getRemoteVersion("install_man");
                $version[] = array("version"=>$remote[0]["commit"]["sha"],"time"=>time(),"optout"=>"true","name"=>"");
                saveJSON('version.php', $version);
            }
        } else {
            $local = $this->getLocalVersion();
                    
            if (file_exists(BASE_PATH."/.git/HEAD")) {
                $current = getJSON('version.php');
                if ($local[0]['version'] != $current[0]['version']) {
                    $remote = $this->getRemoteVersion("update_git", $local[0]['version']);
                    $version[] = array("version"=>$local[0]['version'],"time"=>time(),"optout"=>"true","name"=>"");
                    saveJSON('version.php', $version);
                }
            } else {
                if ($local[0]['version'] == '' && $local[0]['name'] == $_SESSION['user']) {
                    $remote = $this->getRemoteVersion("update_man", $local[0]['version']);
                    $version[] = array("version"=>$remote[0]["commit"]["sha"],"time"=>time(),"optout"=>"true","name"=>$_SESSION['user']);
                    saveJSON('version.php', $version);
                }
            }
            
            $local = $this->getLocalVersion();
            if (!isset($local[0]['optout'])) {
                $remote = $this->getRemoteVersion("optout", $local[0]['version']);
                $this->OptOut();
            }
        }
    }

    //////////////////////////////////////////////////////////////////
    // Clear Version
    //////////////////////////////////////////////////////////////////

    public function Clear()
    {
        $version[] = array("version"=>"","time"=>time(),"optout"=>"true","name"=>$_SESSION['user']);
        saveJSON('version.php', $version);
    }
    
    //////////////////////////////////////////////////////////////////
    // Clear Version
    //////////////////////////////////////////////////////////////////

    public function OptOut()
    {
        $current = getJSON('version.php');
        $version[] = array("version"=>$current[0]['version'],"time"=>$current[0]['time'],"optout"=>"true","name"=>$current[0]['name']);
        saveJSON('version.php', $version);
    }

    //////////////////////////////////////////////////////////////////
    // Check Version
    //////////////////////////////////////////////////////////////////

    public function Check()
    {
        $local = $this->getLocalVersion();
        $remote = $this->getRemoteVersion("check", $local[0]['version']);
        
        $nightly = true;
        $archive = Common::getConstant('ARCHIVEURL', $this->archive);
        $latest = '';
        
        foreach ($remote as $tag) {
            if ($latest == '') {
                $latest = $tag["name"];
                $archive = $tag["zipball_url"];
            }
            if ($local[0]['version'] == $tag["commit"]["sha"]) {
                $local[0]['version'] = $tag["name"];
                $nightly = false;
                break;
            }
        }
                
        $search = array("\r\n", "\n", "\r");
        $replace = array(" ", " ", " ");

        $message = '';
        $merge = '';
        $commits = json_decode(file_get_contents(Common::getConstant('COMMITURL', $this->commits)), true);
        foreach ($commits as $commit) {
            if ($local[0]['version'] != $commit["sha"]) {
                if (strpos($commit["commit"]["message"], "Merge") === false) {
                    $message .= '- '.str_replace($search, $replace, $commit["commit"]["message"]).'<br/>';
                } else {
                    $merge .= '- '.str_replace($search, $replace, $commit["commit"]["message"]).'<br/>';
                }
            } else {
                break;
            }
        }

        if ($message == '') {
            $message = $merge;
        }

        return "[".formatJSEND("success", array("currentversion"=>$local[0]['version'],"remoteversion"=>$latest,"message"=>$message,"archive"=>$archive,"nightly"=>$nightly,"name"=>$local[0]['name']))."]";
    }
        
    //////////////////////////////////////////////////////////////////
    // Get Local Version
    //////////////////////////////////////////////////////////////////
    
    public function getLocalVersion()
    {
        if (file_exists(BASE_PATH."/.git/HEAD")) {
            $tmp = file_get_contents(BASE_PATH."/.git/HEAD");
            if (strpos($tmp, "ref:") === false) {
                $data[0]['version'] = trim($tmp);
            } else {
                $data[0]['version'] = trim(file_get_contents(BASE_PATH."/.git/".trim(str_replace('ref: ', '', $tmp))));
            }
            $data[0]['name'] = "";
            if (file_exists(DATA ."/version.php")) {
                $data[0]['optout'] = "true";
            }
        } else {
            $data = getJSON('version.php');
        }
        return $data;
    }
        
    //////////////////////////////////////////////////////////////////
    // Get Remote Version
    //////////////////////////////////////////////////////////////////
        
    public function getRemoteVersion($action, $localversion = "")
    {
        $remoteurl = Common::getConstant('UPDATEURL', $this->remote);
        $remoteurl = str_replace("{OS}", PHP_OS, $remoteurl);
        $remoteurl = str_replace("{PHP}", phpversion(), $remoteurl);
        $remoteurl = str_replace("{VER}", $localversion, $remoteurl);
        $remoteurl = str_replace("{WEB}", urlencode($_SERVER['SERVER_SOFTWARE']), $remoteurl);
        $remoteurl = str_replace("{ACT}", $action, $remoteurl);
        
        return json_decode(file_get_contents($remoteurl), true);
    }
}
