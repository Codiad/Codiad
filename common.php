<?php
    /*
    *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */
    
    include BASE_PATH."/languages/en.php"; //english is the main language
    include BASE_PATH."/languages/{$_SESSION['lang']}.php";
    
    function i18n($key, $output = true) {
        global $lang;
        $key = ucwords(strtolower($key)); //Test, test TeSt and tESt are exacly the same
        if(isset($lang[$key]))
            $return = $lang[$key];
        else
            $return = $key;
        if($output)
            echo $return;
        return $return;
    }
    function get_i18n($key) {
        return i18n($key, false);
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

    function getJSON($file){
        $json = file_get_contents(BASE_PATH . "/data/" . $file);
        $json = str_replace("|*/?>","",str_replace("<?php/*|","",$json));
        $json = json_decode($json,true);
        return $json;
    }

    //////////////////////////////////////////////////////////////////
    // Save JSON
    //////////////////////////////////////////////////////////////////

    function saveJSON($file,$data){
        $data = "<?php/*|" . json_encode($data) . "|*/?>";
        $write = fopen(BASE_PATH . "/data/" . $file, 'w') or die("can't open file");
        fwrite($write, $data);
        fclose($write);
    }

    //////////////////////////////////////////////////////////////////
    // Format JSEND Response
    //////////////////////////////////////////////////////////////////

    function formatJSEND($status,$data=false){

        // Success ///////////////////////////////////////////////
        if($status=="success"){
            if($data){
                $jsend = '{"status":"success","data":'.json_encode($data).'}';
            }else{
                $jsend = '{"status":"success","data":null}';
            }

        // Error /////////////////////////////////////////////////
        }else{
            $jsend = '{"status":"error","message":"'.$data.'"}';
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
