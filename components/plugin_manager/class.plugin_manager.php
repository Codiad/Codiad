<?php

/*
*  Copyright (c) Codiad & daeks (codiad.com), distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

require_once('../../common.php');

class Plugin_manager extends Common {

    //////////////////////////////////////////////////////////////////
    // PROPERTIES
    //////////////////////////////////////////////////////////////////

    public $plugins     = '';
    public $market      = 'http://codiad.com/plugins.json';

    //////////////////////////////////////////////////////////////////
    // METHODS
    //////////////////////////////////////////////////////////////////

    // -----------------------------||----------------------------- //

    //////////////////////////////////////////////////////////////////
    // Construct
    //////////////////////////////////////////////////////////////////

    public function __construct(){
        $this->plugins = getJSON('plugins.php');
        $this->market = Common::getConstant('PMURL', $this->market);
    }

    //////////////////////////////////////////////////////////////////
    // Get Market list
    //////////////////////////////////////////////////////////////////

    public function Market(){
        return json_decode(file_get_contents($this->market),true);
    }

    //////////////////////////////////////////////////////////////////
    // Deactivate Plugin
    //////////////////////////////////////////////////////////////////

    public function Deactivate($name){
        $revised_array = array();
        foreach($this->plugins as $plugin){
            if($plugin!=$name){
                $revised_array[] = $plugin;
            }
        }
        // Save array back to JSON
        saveJSON('plugins.php',$revised_array);
        // Response
        echo formatJSEND("success",null);
    }

    //////////////////////////////////////////////////////////////////
    // Activate Plugin
    //////////////////////////////////////////////////////////////////

    public function Activate($name){
        $this->plugins[] = $name;
        saveJSON('plugins.php',$this->plugins);
        // Response
        echo formatJSEND("success",null);
    }

    //////////////////////////////////////////////////////////////////
    // Install Plugin
    //////////////////////////////////////////////////////////////////

    public function Install($name, $repo){
        if(substr($repo,-4) == '.git') {
            $repo = substr($repo,0,-4);
        }
        if(file_put_contents(PLUGINS.'/'.$name.'.zip', fopen($repo.'/archive/master.zip', 'r'))) {
            $zip = new ZipArchive;
            $res = $zip->open(PLUGINS.'/'.$name.'.zip');
            // open downloaded archive
            if ($res === TRUE) {
              // extract archive
              if($zip->extractTo(PLUGINS) === true) {
                $zip->close();
              } else {
                die(formatJSEND("error","Unable to open ".$name.".zip"));
              }
            } else {
                die(formatJSEND("error","ZIP Extension not found"));
            }

            unlink(PLUGINS.'/'.$name.'.zip');
            // Response
            $this->Activate(substr($repo, strrpos($repo, "/") + 1)."-master");
        } else {
            die(formatJSEND("error","Unable to download ".$repo));
        }
    }

    //////////////////////////////////////////////////////////////////
    // Remove Plugin
    //////////////////////////////////////////////////////////////////

    public function Remove($name){
        function rrmdir($path){
            return is_file($path)?
            @unlink($path):
            @array_map('rrmdir',glob($path.'/*'))==@rmdir($path);
        }

        rrmdir(PLUGINS.'/'.$name);
        $this->Deactivate($name);
    }

    //////////////////////////////////////////////////////////////////
    // Update Plugin
    //////////////////////////////////////////////////////////////////

    public function Update($name){
        function rrmdir($path){
            return is_file($path)?
            @unlink($path):
            @array_map('rrmdir',glob($path.'/*'))==@rmdir($path);
        }

        function cpy($source, $dest, $ign){
            if(is_dir($source)) {
                $dir_handle=opendir($source);
                while($file=readdir($dir_handle)){
                    if(!in_array($file, $ign)){
                        if(is_dir($source."/".$file)){
                            if(!file_exists($dest."/".$file)) {
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

        if(file_exists(PLUGINS.'/'.$name.'/plugin.json')) {
            $data = json_decode(file_get_contents(PLUGINS.'/'.$name.'/plugin.json'),true);
            if(substr($data[0]['url'],-4) == '.git') {
                $data[0]['url'] = substr($data[0]['url'],0,-4);
            }
            $data[0]['url'] .= '/archive/master.zip';

            $ign = array(".","..");
            if(isset($data[0]['exclude'])) {
              foreach(explode(",",$data[0]['exclude']) as $exclude) {
                array_push($ign, $exclude);
              }
            }

            if(file_exists(PLUGINS.'/_'.session_id()) || mkdir(PLUGINS.'/_'.session_id())) {
              if(file_put_contents(PLUGINS.'/_'.session_id().'/'.$name.'.zip', fopen($data[0]['url'], 'r'))) {
                  $zip = new ZipArchive;
                  $res = $zip->open(PLUGINS.'/_'.session_id().'/'.$name.'.zip');
                  // open downloaded archive
                  if ($res === TRUE) {
                    // extract archive
                    if($zip->extractTo(PLUGINS.'/_'.session_id().'') === true) {
                      $zip->close();
                      $srcname = $name;
                      if(substr($srcname, -6) != "master") {
                        $srcname = $srcname.'-master';
                      }
                      cpy(PLUGINS.'/_'.session_id().'/'.$srcname, PLUGINS.'/'.$name, $ign);
                    } else {
                      die(formatJSEND("error","Unable to open ".$name.".zip"));
                    }
                  } else {
                      die(formatJSEND("error","ZIP Extension not found"));
                  }

                  rrmdir(PLUGINS.'/_'.session_id());
                  // Response
                  echo formatJSEND("success",null);
              } else {
                  die(formatJSEND("error","Unable to download ".$repo));
              }
            } else {
              die(formatJSEND("error","Unable to create temp dir "));
            }
        } else {
            echo formatJSEND("error","Unable to find plugin ".$name);
        }
    }
}
