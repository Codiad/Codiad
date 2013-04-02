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

    //////////////////////////////////////////////////////////////////
    // METHODS
    //////////////////////////////////////////////////////////////////

    // -----------------------------||----------------------------- //

    //////////////////////////////////////////////////////////////////
    // Construct
    //////////////////////////////////////////////////////////////////

    public function __construct(){
        $this->remote = 'https://raw.github.com/Codiad/Codiad/master/components/update/version.json';
        $this->archive = 'https://github.com/Codiad/Codiad/archive/master.zip';
    }
    
    public function Check() {
        $app = json_decode(file_get_contents("version.json"),true);
        if($this->remote != '') {
            $remote = json_decode(file_get_contents($this->remote),true);
        }
        
        echo formatJSEND("success",array("currentversion"=>$app['version'],"remoteversion"=>$remote['version'],"message"=>$remote['message'],"archive"=>$this->archive));
    }

}
