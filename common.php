<?php
    /*
    *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */
    
    /* The stack of debug messages. */
    $debugMessageStack = array();
    
    //////////////////////////////////////////////////////////////////
    // Log debug message
    // They will be displayed in the consol when the respons is make
    // with the formatJSEND command.
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
