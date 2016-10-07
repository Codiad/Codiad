<?php

/*
*  Copyright (c) Codiad & daeks (codiad.com), distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

require_once('../../common.php');

class Market extends Common
{

    //////////////////////////////////////////////////////////////////
    // PROPERTIES
    //////////////////////////////////////////////////////////////////

    public $local       = array();
    public $url         = 'http://market.codiad.com/json';
    public $remote      = null;
    public $tmp         = array();
    public $old         = null;

    //////////////////////////////////////////////////////////////////
    // METHODS
    //////////////////////////////////////////////////////////////////

    // -----------------------------||----------------------------- //

    //////////////////////////////////////////////////////////////////
    // Construct
    //////////////////////////////////////////////////////////////////

    public function __construct()
    {
        // initial setup
        if (!file_exists(DATA.'/cache')) {
            mkdir(DATA.'/cache');
        }
        
        // get existing data
        $this->local['plugins'] = Common::readDirectory(PLUGINS);
        $this->local['themes'] = Common::readDirectory(THEMES);
        $this->url = Common::getConstant('MARKETURL', $this->url);
                
        // load market from server
        if (!file_exists(DATA.'/cache/market.current')) {
            $optout = "";
            foreach ($this->local as $key => $value) {
                foreach ($value as $data) {
                    if (trim($data) != '') {
                        if (file_exists(BASE_PATH.'/'.$key.'/'.$data.'/'.rtrim($key, "s").'.json')) {
                            $tmp = json_decode(file_get_contents(BASE_PATH.'/'.$key.'/'.$data.'/'.rtrim($key, "s").'.json'), true);
                            if (substr($tmp[0]['url'], -4) == '.git') {
                                $tmp[0]['url'] = substr($tmp[0]['url'], 0, -4);
                            }
                            $optout .= rtrim($key, "s").":".array_pop(explode('/', $tmp[0]['url'])).",";
                        }
                    }
                }
            }
            file_put_contents(DATA.'/cache/market.current', file_get_contents($this->url.'/?o='.substr($optout, 0, -1)));
            copy(DATA.'/cache/market.current', DATA.'/cache/market.last');
        } else {
            if (time()-filemtime(DATA.'/cache/market.current') > 24 * 3600) {
                copy(DATA.'/cache/market.current', DATA.'/cache/market.last');
                file_put_contents(DATA.'/cache/market.current', file_get_contents($this->url));
            }
        }
        // get current and last market cache to establish array
        $this->old = json_decode(file_get_contents(DATA.'/cache/market.last'), true);
        $this->remote = json_decode(file_get_contents(DATA.'/cache/market.current'), true);
        
        // internet connection could not be established
        if ($this->remote == '') {
            $this->remote = array();
        }
        
        // check old cache for new ones
        $this->tmp = array();
        foreach ($this->remote as $key => $data) {
            $found = false;
            foreach ($this->old as $key => $old) {
                if ($old['name'] == $data['name']) {
                    $found = true;
                    break;
                }
            }
            if (!$found && !isset($data['folder'])) {
                $data['new'] = '1';
            }
          
          // check if folder exists for that extension
            if (substr($data['url'], -4) == '.git') {
                $data['url'] = substr($data['url'], 0, -4);
            }
            if (file_exists(BASE_PATH.'/'.$data['type'].substr($data['url'], strrpos($data['url'], '/'.rtrim($data['type'], 's').'.json')))) {
                $data['folder'] = substr($data['url'], strrpos($data['url'], '/')+1);
            } else {
                if (file_exists(BASE_PATH.'/'.$data['type'].substr($data['url'], strrpos($data['url'], '/')).'-master/'.rtrim($data['type'], 's').'.json')) {
                    $data['folder'] = substr($data['url'], strrpos($data['url'], '/')+1).'-master';
                }
            }
             
            array_push($this->tmp, $data);
        }
        $this->remote = $this->tmp;
                
        // Scan plugins directory for missing plugins
        foreach (scandir(PLUGINS) as $fname) {
            if ($fname == '.' || $fname == '..') {
                continue;
            }
            if (is_dir(PLUGINS.'/'.$fname)) {
                $found = false;
                foreach ($this->remote as $key => $data) {
                    if (isset($data['folder']) && $data['folder'] == $fname) {
                        $found = true;
                        break;
                    }
                }
                if (!$found && file_exists(PLUGINS . "/" . $fname . "/plugin.json")) {
                    $data = file_get_contents(PLUGINS . "/" . $fname . "/plugin.json");
                    $data = json_decode($data, true);
                    $data[0]['name'] = $fname;
                    $data[0]['folder'] = $fname;
                    $data[0]['type'] = 'plugins';
                    $data[0]['image'] = '';
                    $data[0]['count'] = -1;
                    $data[0]['remote'] = 0;
                    if (!isset($data[0]['description'])) {
                        $data[0]['description'] = 'Manual Installation';
                    }
                    array_push($this->remote, $data[0]);
                }
            }
        }
         
        // Scan theme directory for missing plugins
        foreach (scandir(THEMES) as $fname) {
            if ($fname == '.' || $fname == '..' || $fname == 'default') {
                continue;
            }
            if (is_dir(THEMES.'/'.$fname)) {
                $found = false;
                foreach ($this->remote as $key => $data) {
                    if (isset($data['folder']) && $data['folder'] == $fname) {
                        $found = true;
                        break;
                    }
                }
                if (!$found && file_exists(THEMES . "/" . $fname . "/theme.json")) {
                    $data = file_get_contents(THEMES . "/" . $fname . "/theme.json");
                    $data = json_decode($data, true);
                    $data[0]['name'] = $fname;
                    $data[0]['folder'] = $fname;
                    $data[0]['type'] = 'themes';
                    $data[0]['image'] = '';
                    $data[0]['count'] = -1;
                    $data[0]['remote'] = 0;
                    if (!isset($data[0]['description'])) {
                        $data[0]['description'] = 'Manual Installation';
                    }
                    array_push($this->remote, $data[0]);
                }
            }
        }
         
         // Check for updates
         $this->tmp = array();
        foreach ($this->remote as $key => $data) {
            if (substr($data['url'], -4) == '.git') {
                $data['url'] = substr($data['url'], 0, -4);
            }
          // extension exists locally, so load its metadata
            if (isset($data['folder'])) {
                $local = json_decode(file_get_contents(BASE_PATH.'/'.$data['type'].'/'.$data['folder'].'/'.rtrim($data['type'], 's').'.json'), true);
              
                $remoteurl = str_replace('github.com', 'raw.github.com', $data['url']).'/master/'.rtrim($data['type'], 's').'.json';
              
                if (!file_exists(DATA.'/cache/'.$data['folder'].'.current')) {
                    file_put_contents(DATA.'/cache/'.$data['folder'].'.current', file_get_contents($remoteurl));
                } else {
                    if (time()-filemtime(DATA.'/cache/'.$data['folder'].'.current') > 24 * 3600) {
                        file_put_contents(DATA.'/cache/'.$data['folder'].'.current', file_get_contents($remoteurl));
                    }
                }
              
                  $remote = json_decode(file_get_contents(DATA.'/cache/'.$data['folder'].'.current'), true);
              
                  $data['version'] = $local[0]['version'];
                if ($remote[0]['version'] != $local[0]['version']) {
                    $data['update'] = $remote[0]['version'];
                }
                  $data['remote'] = 0;
            } else {
                $data['remote'] = 1;
            }
            array_push($this->tmp, $data);
        }
        $this->remote = $this->tmp;
    }

    //////////////////////////////////////////////////////////////////
    // Install Plugin
    //////////////////////////////////////////////////////////////////

    public function Install($type, $name, $repo)
    {
        if (substr($repo, -4) == '.git') {
            $repo = substr($repo, 0, -4);
        }
        if ($type == '') {
            $file_headers = @get_headers(str_replace('github.com', 'raw.github.com', $repo.'/master/plugin.json'));
            if ($file_headers[0] != 'HTTP/1.1 404 Not Found') {
                $type = 'plugins';
            } else {
                $file_headers = @get_headers(str_replace('github.com', 'raw.github.com', $repo.'/master/theme.json'));
                if ($file_headers[0] != 'HTTP/1.1 404 Not Found') {
                    $type = 'themes';
                } else {
                    die(formatJSEND("error", "Invalid Repository"));
                }
            }
        } else {
            $reponame = explode('/', $repo);
            $tmp = file_get_contents($this->url.'/?t='.rtrim($type, "s").'&i='.str_replace("-master", "", $reponame[sizeof($repo)-1]));
        }
        if (file_put_contents(BASE_PATH.'/'.$type.'/'.$name.'.zip', fopen($repo.'/archive/master.zip', 'r'))) {
            $zip = new ZipArchive;
            $res = $zip->open(BASE_PATH.'/'.$type.'/'.$name.'.zip');
            // open downloaded archive
            if ($res === true) {
              // extract archive
                if ($zip->extractTo(BASE_PATH.'/'.$type) === true) {
                    $zip->close();
                } else {
                    die(formatJSEND("error", "Unable to open ".$name.".zip"));
                }
            } else {
                die(formatJSEND("error", "ZIP Extension not found"));
            }

            unlink(BASE_PATH.'/'.$type.'/'.$name.'.zip');
            // Response
            echo formatJSEND("success", null);
        } else {
            die(formatJSEND("error", "Unable to download ".$repo));
        }
    }

    //////////////////////////////////////////////////////////////////
    // Remove Plugin
    //////////////////////////////////////////////////////////////////

    public function Remove($type, $name)
    {
        function rrmdir($path)
        {
            return is_file($path)?
            @unlink($path):
            @array_map('rrmdir', glob($path.'/*'))==@rmdir($path);
        }

        rrmdir(BASE_PATH.'/'.$type.'/'.$name);
        echo formatJSEND("success", null);
    }

    //////////////////////////////////////////////////////////////////
    // Update Plugin
    //////////////////////////////////////////////////////////////////

    public function Update($type, $name)
    {
        function rrmdir($path)
        {
            return is_file($path)?
            @unlink($path):
            @array_map('rrmdir', glob($path.'/*'))==@rmdir($path);
        }

        function cpy($source, $dest, $ign)
        {
            if (is_dir($source)) {
                $dir_handle=opendir($source);
                while ($file=readdir($dir_handle)) {
                    if (!in_array($file, $ign)) {
                        if (is_dir($source."/".$file)) {
                            if (!file_exists($dest."/".$file)) {
                                mkdir($dest."/".$file);
                            }
                            cpy($source."/".$file, $dest."/".$file, $ign);
                        } else {
                            copy($source."/".$file, $dest."/".$file);
                        }
                    }
                }
                closedir($dir_handle);
            } else {
                copy($source, $dest);
            }
        }

        if (file_exists(BASE_PATH.'/'.$type.'/'.$name.'/'.rtrim($type, "s").'.json')) {
            $data = json_decode(file_get_contents(BASE_PATH.'/'.$type.'/'.$name.'/'.rtrim($type, "s").'.json'), true);
            if (substr($data[0]['url'], -4) == '.git') {
                $data[0]['url'] = substr($data[0]['url'], 0, -4);
            }
            $data[0]['url'] .= '/archive/master.zip';

            $ign = array(".","..");
            if (isset($data[0]['exclude'])) {
                foreach (explode(",", $data[0]['exclude']) as $exclude) {
                    array_push($ign, $exclude);
                }
            }

            if (file_exists(BASE_PATH.'/'.$type.'/_'.session_id()) || mkdir(BASE_PATH.'/'.$type.'/_'.session_id())) {
                if (file_put_contents(BASE_PATH.'/'.$type.'/_'.session_id().'/'.$name.'.zip', fopen($data[0]['url'], 'r'))) {
                    $zip = new ZipArchive;
                    $res = $zip->open(BASE_PATH.'/'.$type.'/_'.session_id().'/'.$name.'.zip');
                    // open downloaded archive
                    if ($res === true) {
                      // extract archive
                        if ($zip->extractTo(BASE_PATH.'/'.$type.'/_'.session_id().'') === true) {
                            $zip->close();
                            $srcname = $name;
                            if (substr($srcname, -6) != "master") {
                                $srcname = $srcname.'-master';
                            }
                            cpy(BASE_PATH.'/'.$type.'/_'.session_id().'/'.$srcname, BASE_PATH.'/'.$type.'/'.$name, $ign);
                        } else {
                            die(formatJSEND("error", "Unable to open ".$name.".zip"));
                        }
                    } else {
                        die(formatJSEND("error", "ZIP Extension not found"));
                    }

                      rrmdir(BASE_PATH.'/'.$type.'/_'.session_id());
                      // Response
                      echo formatJSEND("success", null);
                } else {
                    die(formatJSEND("error", "Unable to download ".$repo));
                }
            } else {
                die(formatJSEND("error", "Unable to create temp dir "));
            }
        } else {
            echo formatJSEND("error", "Unable to find ".$name);
        }
    }
}
