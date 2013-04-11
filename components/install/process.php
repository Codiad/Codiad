<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See 
*  [root]/license.txt for more. This information must remain intact.
*/

//////////////////////////////////////////////////////////////////////
// Paths
//////////////////////////////////////////////////////////////////////

    $path = $_POST['path'];
    
    $workspace = $path . "/workspace";
    $users = $path . "/data/users.php";
    $projects = $path . "/data/projects.php";
    $active = $path . "/data/active.php";
    $config = $path . "/config.php";

//////////////////////////////////////////////////////////////////////
// Functions
//////////////////////////////////////////////////////////////////////
    
    function saveFile($file,$data){
        $write = fopen($file, 'w') or die("can't open file");
        fwrite($write, $data);
        fclose($write);
    }
     
    function saveJSON($file,$data){
        $data = "<?php/*|[" . json_encode($data) . "]|*/?>";
        saveFile($file,$data);
    }
    
    function encryptPassword($p){
        return sha1(md5($p));
    }
    
    function cleanUsername($username){
        return preg_replace('#[^A-Za-z0-9'.preg_quote('-_@. ').']#','', $username);
    }
       
    function isAbsPath( $path ) {
        if ( preg_match('/^[A-Za-z]:\\/', $path) || $path[0] === '\\' || $path[0] === '/') {
            return true;
        } else {
            return false;
        }
    }

    function cleanPath( $path ){

        // prevent Poison Null Byte injections
        $path = str_replace(chr(0), '', $path );

        // prevent go out of the workspace
        while (strpos($path , '../') !== false)
            $path = str_replace( '../', '', $path );

        return $path;
    }

//////////////////////////////////////////////////////////////////////
// Verify no overwrites
//////////////////////////////////////////////////////////////////////

if(!file_exists($users) && !file_exists($projects) && !file_exists($active)){

    //////////////////////////////////////////////////////////////////
    // Get POST responses
    //////////////////////////////////////////////////////////////////
    
    $username = cleanUsername($_POST['username']);
    $password = encryptPassword($_POST['password']);
    $project_name = $_POST['project_name'];
    $project_path = $_POST['project_path'];
    $timezone = $_POST['timezone'];
    
    //////////////////////////////////////////////////////////////////
    // Create Projects files
    //////////////////////////////////////////////////////////////////
    
    $project_path = cleanPath($project_path);   
    
    if(!isAbsPath($project_path)) {
        $project_path = str_replace(" ","_",preg_replace('/[^\w-]/', '', $project_path));   
        mkdir($workspace . "/" . $project_path);
    } else {
        if(substr($project_path, -1) == '/') {
            $project_path = substr($project_path,0, strlen($project_path)-1);
        }  
        if(!file_exists($project_path)) {
            if(!mkdir($project_path.'/', 0755, true)) {
                die("Unable to create Absolute Path");
            }
        } else {
            if(!is_writable($project_path) || !is_readable($project_path)) {
                die("No Read/Write Permission");
            }
        }
    }
    $project_data = array("name"=>$project_name,"path"=>$project_path);
    saveJSON($projects,$project_data);
    
    
    //////////////////////////////////////////////////////////////////
    // Create Users file
    //////////////////////////////////////////////////////////////////
    
    $user_data = array("username"=>$username,"password"=>$password,"project"=>$project_path);
    saveJSON($users,$user_data);
    
    //////////////////////////////////////////////////////////////////
    // Create Active file
    //////////////////////////////////////////////////////////////////
    
    saveJSON($active,'');
    
    //////////////////////////////////////////////////////////////////
    // Create Config
    //////////////////////////////////////////////////////////////////
    
    
    
    $config_data = '<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See 
*  [root]/license.txt for more. This information must remain intact.
*/

//////////////////////////////////////////////////////////////////
// PATH
//////////////////////////////////////////////////////////////////

$rel = "' . $path . '";
define("BASE_PATH",$rel);
define("COMPONENTS",BASE_PATH . "/components");
define("THEMES",BASE_PATH . "/themes");
define("DATA",BASE_PATH . "/data");
define("WORKSPACE",BASE_PATH . "/workspace");
define("WSURL",$_SERVER["HTTP_HOST"] . $rel . "/workspace");

//////////////////////////////////////////////////////////////////
// THEME
//////////////////////////////////////////////////////////////////

define("THEME", "default");

//////////////////////////////////////////////////////////////////
// ABSOLUTE PATH
//////////////////////////////////////////////////////////////////

define("WHITEPATHS", $_SERVER["DOCUMENT_ROOT"].",/home");

//////////////////////////////////////////////////////////////////
// SESSIONS
//////////////////////////////////////////////////////////////////

ini_set("session.cookie_lifetime","0");

//////////////////////////////////////////////////////////////////
// TIMEZONE
//////////////////////////////////////////////////////////////////

date_default_timezone_set("' . $timezone . '");

?>';

    saveFile($config,$config_data);
    
    echo("success");

}

?>
