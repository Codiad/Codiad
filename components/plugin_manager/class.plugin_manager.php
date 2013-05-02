<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

require_once('../../common.php');

class Plugin_manager extends Common {

    //////////////////////////////////////////////////////////////////
    // PROPERTIES
    //////////////////////////////////////////////////////////////////

    public $plugins     = '';

    //////////////////////////////////////////////////////////////////
    // METHODS
    //////////////////////////////////////////////////////////////////

    // -----------------------------||----------------------------- //

    //////////////////////////////////////////////////////////////////
    // Construct
    //////////////////////////////////////////////////////////////////

    public function __construct(){
        $this->plugins = getJSON('plugins.php');
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

}
