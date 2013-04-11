<?php
    /*
    *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */
    
    if(strpos($_SERVER['SCRIPT_FILENAME'], "components")) {
        foreach(explode("/", substr($_SERVER['SCRIPT_FILENAME'],strpos($_SERVER['SCRIPT_FILENAME'], "components") + 11)) as $part) {
            if(!isset($path)){
            	$path = '../';
            }else{
            	$path .= '../';
            }
        }
        if(file_exists($path.'config.php')){ require_once($path.'config.php'); }
    } else {
        if(file_exists('config.php')){ require_once('config.php'); }
    }
    
    if(!defined('BASE_PATH')) {
        define('BASE_PATH', dirname(__FILE__));
    }
    
    if(!defined('COMPONENTS')) {
        define('COMPONENTS', dirname(__FILE__) . '/components');
    }
    
    // Ensure theme vars are present (upgrade with legacy config.php)
    if(!defined('THEMES')){
    	define("THEMES", BASE_PATH . "/themes");
    }
    
    if(!defined('THEME')){
    	define("THEME", "default");
    }
    
    
    
    //////////////////////////////////////////////////////////////////
    // SESSIONS
    //////////////////////////////////////////////////////////////////

    session_start();
    
    /* The stack of debug messages. */
    $debugMessageStack = array();
    
    //////////////////////////////////////////////////////////////////
    // Log debug message
    // Messages will be displayed in the console when the response is 
    // made with the formatJSEND function.
    //////////////////////////////////////////////////////////////////
    
    function debug($message) {
        global $debugMessageStack;
        $debugMessageStack[] = $message;
    }
    
    //////////////////////////////////////////////////////////////////
    // Localization
    //////////////////////////////////////////////////////////////////
    
    if (isset($_SESSION['lang'])) {
        include BASE_PATH."/languages/{$_SESSION['lang']}.php";
    } else {  
        include BASE_PATH."/languages/en.php";
    }
    
    function i18n($key) {
        echo get_i18n($key);
    }
    
    function get_i18n($key) {
        global $lang;
        $key = ucwords(strtolower($key)); //Test, test TeSt and tESt are exacly the same
        return isset($lang[$key]) ? $lang[$key] : $key;
    }
    
    //////////////////////////////////////////////////////////////////
    // Check Session / Key
    //////////////////////////////////////////////////////////////////

    function checkSession(){
        // Set any API keys
        $api_keys = array();
        // Check API Key or Session Authentication
        $key = "";
        if(isset($_GET['key'])){ $key = $_GET['key']; }
        if(!isset($_SESSION['user']) && !in_array($key,$api_keys)){
            exit('{"status":"error","message":"Authentication Error"}');
        }
    }

    //////////////////////////////////////////////////////////////////
    // Get JSON
    //////////////////////////////////////////////////////////////////

    function getJSON($file,$namespace=""){
        $path = BASE_PATH . "/data/";
        if($namespace != ""){
            $path = $path . $namespace . "/";
            $path = preg_replace('#/+#','/',$path);
        }
        
        $json = file_get_contents($path . $file);
        $json = str_replace("|*/?>","",str_replace("<?php/*|","",$json));
        $json = json_decode($json,true);
        return $json;
    }

    //////////////////////////////////////////////////////////////////
    // Save JSON
    //////////////////////////////////////////////////////////////////

    function saveJSON($file,$data,$namespace=""){
        $path = BASE_PATH . "/data/";
        if($namespace != ""){
            $path = $path . $namespace . "/";
            $path = preg_replace('#/+#','/',$path);
            if(!is_dir($path)) mkdir($path);
        }
        
        $data = "<?php/*|" . json_encode($data) . "|*/?>";
        $write = fopen($path . $file, 'w') or die("can't open file");
        fwrite($write, $data);
        fclose($write);
    }

    //////////////////////////////////////////////////////////////////
    // Format JSEND Response
    //////////////////////////////////////////////////////////////////

    function formatJSEND($status,$data=false){

        /// Debug /////////////////////////////////////////////////
        global $debugMessageStack;
        $debug = "";
        if(count($debugMessageStack) > 0) {
            $debug .= ',"debug":';
            $debug .= json_encode($debugMessageStack);
        }

        // Success ///////////////////////////////////////////////
        if($status=="success"){
            if($data){
                $jsend = '{"status":"success","data":'.json_encode($data).$debug.'}';
            }else{
                $jsend = '{"status":"success","data":null'.$debug.'}';
            }

        // Error /////////////////////////////////////////////////
        }else{
            $jsend = '{"status":"error","message":"'.$data.'"'.$debug.'}';
        }

        // Return ////////////////////////////////////////////////
        return $jsend;

    }
    
    //////////////////////////////////////////////////////////////////
    // Check Function Availability
    //////////////////////////////////////////////////////////////////

    function checkAccess() {
        return !file_exists(BASE_PATH . "/data/" . $_SESSION['user'] . '_acl.php');
    }
    
    //////////////////////////////////////////////////////////////////
    // Check Function Availability
    //////////////////////////////////////////////////////////////////

    function isAvailable($func) {
        if (ini_get('safe_mode')) return false;
        $disabled = ini_get('disable_functions');
        if ($disabled) {
            $disabled = explode(',', $disabled);
            $disabled = array_map('trim', $disabled);
            return !in_array($func, $disabled);
        }
        return true;
    }
?>
