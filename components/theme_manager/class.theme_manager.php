<?php

/*
*  Copyright (c) Codiad & daeks (codiad.com), distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

require_once('../../common.php');

class Theme_manager extends Common {

    //////////////////////////////////////////////////////////////////
    // PROPERTIES
    //////////////////////////////////////////////////////////////////

    public $themes     = '';
    public $market      = 'http://codiad.com/themes.json';

    //////////////////////////////////////////////////////////////////
    // METHODS
    //////////////////////////////////////////////////////////////////

    // -----------------------------||----------------------------- //

    //////////////////////////////////////////////////////////////////
    // Construct
    //////////////////////////////////////////////////////////////////

    public function __construct(){
        $this->themes = getJSON('themes.php');
        $this->market = Common::getConstant('TMURL', $this->market);
    }

    //////////////////////////////////////////////////////////////////
    // Get Market list
    //////////////////////////////////////////////////////////////////

    public function Market(){
        return json_decode(file_get_contents($this->market),true);
    }

    //////////////////////////////////////////////////////////////////
    // Deactivate Theme
    //////////////////////////////////////////////////////////////////

    public function Deactivate($name){
        $revised_array = array();
        foreach($this->themes as $theme){
            if($theme!=$name){
                $revised_array[] = $theme;
            }
        }
        // Save array back to JSON
        saveJSON('themes.php',$revised_array);
        // Response
        echo formatJSEND("success",null);
    }

    //////////////////////////////////////////////////////////////////
    // Activate Theme
    //////////////////////////////////////////////////////////////////

    public function Activate($name){
        $this->themes[] = $name;
        saveJSON('themes.php',$this->themes);
        // Response
        echo formatJSEND("success",null);
    }

    //////////////////////////////////////////////////////////////////
    // Install Theme
    //////////////////////////////////////////////////////////////////

    public function Install($name, $repo){
        if(substr($repo,-4) == '.git') {
            $repo = substr($repo,0,-4);
        }
        $repo .= '/archive/master.zip';
        if(file_put_contents(THEMES.'/'.$name.'.zip', fopen($repo, 'r'))) {
            $zip = new ZipArchive;
            $res = $zip->open(THEMES.'/'.$name.'.zip');
            // open downloaded archive
            if ($res === TRUE) {
              // extract archive
              if($zip->extractTo(THEMES) === true) {
                $zip->close();
              } else {
                die(formatJSEND("error","Unable to open ".$name.".zip"));
              }
            } else {
                die(formatJSEND("error","ZIP Extension not found"));
            }

            unlink(THEMES.'/'.$name.'.zip');
            // Response
            echo formatJSEND("success",null);
        } else {
            die(formatJSEND("error","Unable to download ".$repo));
        }
    }

    //////////////////////////////////////////////////////////////////
    // Remove theme
    //////////////////////////////////////////////////////////////////

    public function Remove($name){
        function rrmdir($path){
            return is_file($path)?
            @unlink($path):
            @array_map('rrmdir',glob($path.'/*'))==@rmdir($path);
        }

        rrmdir(THEMES.'/'.$name);
        $this->Deactivate($name);
    }

    //////////////////////////////////////////////////////////////////
    // Update Theme
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

        if(file_exists(THEMES.'/'.$name.'/plugin.json')) {
            $data = json_decode(file_get_contents(THEMES.'/'.$name.'/plugin.json'),true);
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

            if(file_exists(THEMES.'/_'.session_id()) || mkdir(THEMES.'/_'.session_id())) {
              if(file_put_contents(THEMES.'/_'.session_id().'/'.$name.'.zip', fopen($data[0]['url'], 'r'))) {
                  $zip = new ZipArchive;
                  $res = $zip->open(THEMES.'/_'.session_id().'/'.$name.'.zip');
                  // open downloaded archive
                  if ($res === TRUE) {
                    // extract archive
                    if($zip->extractTo(THEMES.'/_'.session_id().'') === true) {
                      $zip->close();
                      $srcname = $name;
                      if(substr($srcname, -6) != "master") {
                        $srcname = $srcname.'-master';
                      }
                      cpy(THEMES.'/_'.session_id().'/'.$srcname, THEMES.'/'.$name, $ign);
                    } else {
                      die(formatJSEND("error","Unable to open ".$name.".zip"));
                    }
                  } else {
                      die(formatJSEND("error","ZIP Extension not found"));
                  }

                  rrmdir(THEMES.'/_'.session_id());
                  // Response
                  echo formatJSEND("success",null);
              } else {
                  die(formatJSEND("error","Unable to download ".$repo));
              }
            } else {
              die(formatJSEND("error","Unable to create temp dir "));
            }
        } else {
            echo formatJSEND("error","Unable to find theme ".$name);
        }
    }
}
