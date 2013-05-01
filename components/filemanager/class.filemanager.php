<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

require_once('../../lib/diff_match_patch.php');
require_once('../../common.php');

class Filemanager extends Common {

    //////////////////////////////////////////////////////////////////
    // PROPERTIES
    //////////////////////////////////////////////////////////////////

    public $root          = "";
    public $project       = "";
    public $rel_path      = "";
    public $path          = "";
    public $patch         = "";
    public $type          = "";
    public $new_name      = "";
    public $content       = "";
    public $destination   = "";
    public $upload        = "";
    public $controller    = "";
    public $upload_json   = "";
    public $search_string = "";

    public $query         = "";
    public $foptions     = "";

    // JSEND Return Contents
    public $status        = "";
    public $data          = "";
    public $message       = "";

    //////////////////////////////////////////////////////////////////
    // METHODS
    //////////////////////////////////////////////////////////////////

    // -----------------------------||----------------------------- //

    //////////////////////////////////////////////////////////////////
    // Construct
    //////////////////////////////////////////////////////////////////

    public function __construct($get,$post,$files) {
        $this->rel_path = Filemanager::cleanPath( $get['path'] );

        if($this->rel_path!="/"){ $this->rel_path .= "/"; }
        if(!empty($get['query'])){ $this->query = $get['query']; }
        if(!empty($get['options'])){ $this->foptions = $get['options']; }
        $this->root = $get['root'];
        if($this->isAbsPath($get['path'])) {
            $this->path = Filemanager::cleanPath( $get['path'] );
        } else {
            $this->root .= '/';
            $this->path = $this->root . Filemanager::cleanPath( $get['path'] );
        }
        // Search
        if(!empty($post['search_string'])){ $this->search_string = $post['search_string']; }
        // Create
        if(!empty($get['type'])){ $this->type = $get['type']; }
        // Modify\Create
        if(!empty($get['new_name'])){ $this->new_name = $get['new_name']; }

        foreach(array('content', 'mtime', 'patch') as $key){
            if(!empty($post[$key])){
                if(get_magic_quotes_gpc()){
                    $this->$key = stripslashes($post[$key]);
                }else{
                    $this->$key = $post[$key];
                }
            }
        }
        // Duplicate
        if(!empty($get['destination'])){
            $get['destination'] = Filemanager::cleanPath( $get['destination'] );
            if($this->isAbsPath($get['path'])) {
                $this->destination = $get['destination'];
            } else {
                $this->destination = $this->root . $get['destination'];
            }
        }
    }

    //////////////////////////////////////////////////////////////////
    // INDEX (Returns list of files and directories)
    //////////////////////////////////////////////////////////////////

    public function index(){

        if(file_exists($this->path)){
            $index = array();
            if(is_dir($this->path) && $handle = opendir($this->path)){
                while (false !== ($object = readdir($handle))) {
                    if ($object != "." && $object != ".." && $object != $this->controller) {
                        if(is_dir($this->path.'/'.$object)){ $type = "directory"; $size=count(glob($this->path.'/'.$object.'/*')); }
                        else{ $type = "file"; $size=filesize($this->path.'/'.$object); }
                        $index[] = array(
                            "name"=>$this->rel_path . $object,
                            "type"=>$type,
                            "size"=>$size
                        );
                    }
                }

                $folders = array();
                $files = array();
                foreach($index as $item=>$data){
                    if($data['type']=='directory'){
                        $folders[] = array("name"=>$data['name'],"type"=>$data['type'],"size"=>$data['size']);
                    }
                    if($data['type']=='file'){
                        $files[] = array("name"=>$data['name'],"type"=>$data['type'],"size"=>$data['size']);
                    }
                }

                function sorter($a, $b, $key = 'name') { return strnatcmp($a[$key], $b[$key]); }

                usort($folders,"sorter");
                usort($files,"sorter");

                $output = array_merge($folders,$files);

                $this->status = "success";
                $this->data = '"index":' . json_encode($output);
            }else{
                $this->status = "error";
                $this->message = "Not A Directory";
            }
        }else{
            $this->status = "error";
            $this->message = "Path Does Not Exist";
        }

        $this->respond();
    }

    public function find(){
        if(!function_exists('shell_exec')){
            $this->status = "error";
            $this->message = "Shell_exec() Command Not Enabled.";
        } else {
            chdir($this->path);
            $input = str_replace('"' , '', $this->query);
            $vinput = preg_quote($input);
            $cmd = 'find ';
            if ($this->foptions && $this->foptions['strategy']) {
              switch($this->f_options['strategy']){
              case 'left_prefix': $cmd = "$cmd -iname \"$vinput*\"";  break;
              case 'substring':   $cmd = "$cmd -iname \"*$vinput*\""; break;
              case 'regexp':      $cmd = "$cmd -regex \"$input\"";    break;
              }
            } else {
                $cmd = 'find -iname "' . $input . '*"';
            }
            $cmd = "$cmd  -printf \"%h/%f %y\n\"";
            $output = shell_exec($cmd);
            $file_arr = explode("\n", $output);
            $output_arr = array();

            error_reporting(0);

            foreach ($file_arr as $i => $fentry) {
              $farr = explode(" ", $fentry);
              $fname = trim($farr[0]);
              if ($farr[1] == 'f') {
                $ftype = 'file';
              } else {
                $ftype = 'directory';
              }
              if (strlen($fname) != 0){
                $fname = $this->rel_path . substr($fname, 2);
                $f = array('path' => $fname, 'type' => $ftype );
                array_push( $output_arr, $f);
              }
            }

            if(count($output_arr)==0){
                $this->status = "error";
                $this->message = "No Results Returned";
            } else {
                $this->status = "success";
                $this->data = '"index":' . json_encode($output_arr);
            }
        }
        $this->respond();

    }

    //////////////////////////////////////////////////////////////////
    // SEARCH
    //////////////////////////////////////////////////////////////////

    public function search(){
        if(!function_exists('shell_exec')){
            $this->status = "error";
            $this->message = "Shell_exec() Command Not Enabled.";
        }else{
            if($_GET['type'] == 1) {
                $this->path = WORKSPACE;
            }
            $input = str_replace('"' , '', $this->search_string);
            $input = preg_quote($input);
            $output = shell_exec('grep -i -I -n -R "' . $input . '" ' . $this->path . '/* ');
            $output_arr = explode("\n", $output);
            $return = array();
            foreach($output_arr as $line){
                $data = explode(":", $line);
                $da = array();
                if(count($data) > 2){
                    $da['line'] = $data[1];
                    $da['file'] = str_replace($this->path,'',$data[0]);
                    $da['result'] = str_replace($this->root, '', $data[0]);
                    $da['string'] = str_replace($data[0] . ":" . $data[1] . ':' , '', $line);
                    $return[] = $da;
                }
            }
            if(count($return)==0){
                $this->status = "error";
                $this->message = "No Results Returned";
            }else{
                $this->status = "success";
                $this->data = '"index":' . json_encode($return);
            }
        }
        $this->respond();
    }

    //////////////////////////////////////////////////////////////////
    // OPEN (Returns the contents of a file)
    //////////////////////////////////////////////////////////////////

    public function open(){
        if(is_file($this->path)){
            $output = file_get_contents($this->path);
            
            if(!mb_check_encoding($output, 'UTF-8')) {
                if(mb_check_encoding($output, 'ISO-8859-1')) {
                    $output = utf8_encode($output);
                } else {
                    $output = mb_convert_encoding($content, 'UTF-8');
                }
            }
        
            $this->status = "success";
            $this->data = '"content":' . json_encode($output);
            $mtime = filemtime($this->path);
            $this->data .= ', "mtime":'.$mtime;
        }else{
            $this->status = "error";
            $this->message = "Not A File :".$this->path;
        }

        $this->respond();
    }

    //////////////////////////////////////////////////////////////////
    // OPEN IN BROWSER (Return URL)
    //////////////////////////////////////////////////////////////////

    public function openinbrowser(){
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'];
        $url =  $protocol.WSURL.$this->rel_path;
        $this->status = "success";
        $this->data = '"url":' . json_encode(rtrim($url,"/"));
        $this->respond();
    }

    //////////////////////////////////////////////////////////////////
    // CREATE (Creates a new file or directory)
    //////////////////////////////////////////////////////////////////

    public function create(){

        // Create file
        if($this->type=="file"){
            if(!file_exists($this->path)){
                if($file = fopen($this->path, 'w')){
                    // Write content
                    if($this->content){ fwrite($file, $this->content); }
                    $this->data = '"mtime":'.filemtime($this->path);
                    fclose($file);
                    $this->status = "success";
                }else{
                    $this->status = "error";
                    $this->message = "Cannot Create File";
                }
            }else{
                $this->status = "error";
                $this->message = "File Already Exists";
            }
        }

        // Create directory
        if($this->type=="directory"){
            if(!is_dir($this->path)){
                mkdir($this->path);
                $this->status = "success";
            }else{
                $this->status = "error";
                $this->message = "Directory Already Exists";
            }
        }

        $this->respond();
    }

    //////////////////////////////////////////////////////////////////
    // DELETE (Deletes a file or directory (+contents))
    //////////////////////////////////////////////////////////////////

    public function delete(){

        function rrmdir($path, $follow) { 
           if(is_file($path)) {
               unlink($path);
           } else {
               $files = array_diff(scandir($path), array('.','..')); 
               foreach ($files as $file) {
                  if(is_link("$path/$file")) {
                        if($follow) {
                            rrmdir("$path/$file", $follow);
                        }
                        unlink("$path/$file");
                    } else if(is_dir("$path/$file")) {
                        rrmdir("$path/$file", $follow);
                    } else {
                        unlink("$path/$file");
                    }   
               } 
               return rmdir($path);
           }
        } 

        if(file_exists($this->path)){ 
            if(isset($_GET['follow'])) {
                rrmdir($this->path, true);
            } else {
                rrmdir($this->path, false);
            }
            $this->status = "success";
        }else{
            $this->status = "error";
            $this->message = "Path Does Not Exist ";
        }

        $this->respond();
    }

    //////////////////////////////////////////////////////////////////
    // MODIFY (Modifies a file name/contents or directory name)
    //////////////////////////////////////////////////////////////////

    public function modify(){

        // Change name
        if($this->new_name){
            $explode = explode('/',$this->path);
            array_pop($explode);
            $new_path = implode("/",$explode) . "/" . $this->new_name;
            if(!file_exists($new_path)){
                if(rename($this->path,$new_path)){
                    //unlink($this->path);
                    $this->status = "success";
                }else{
                    $this->status = "error";
                    $this->message = "Could Not Rename";
                }
            }else{
                $this->status = "error";
                $this->message = "Path Already Exists";
            }
        }

        // Change content
        if($this->content || $this->patch){
            if($this->content==' '){
                $this->content=''; // Blank out file
            }
            if ($this->patch && ! $this->mtime){
                $this->status = "error";
                $this->message = "mtime parameter not found";
                $this->respond();
                return;
            }
            if(is_file($this->path)){
                $serverMTime = filemtime($this->path);
                $fileContents = file_get_contents($this->path);

                if ($this->patch && $this->mtime != $serverMTime){
                    $this->status = "error";
                    $this->message = "Client is out of sync";
                    //DEBUG : file_put_contents($this->path.".conflict", "SERVER MTIME :".$serverMTime.", CLIENT MTIME :".$this->mtime);
                    $this->respond();
                    return;
                } else if (strlen(trim($this->patch)) == 0 && ! $this->content ){
                    // Do nothing if the patch is empty and there is no content
                    $this->status = "success";
                    $this->data = '"mtime":'.$serverMTime;
                    $this->respond();
                    return;
                }

                if($file = fopen($this->path, 'w')){
                    if ($this->patch){
                        $dmp = new diff_match_patch();
                        $p = $dmp->patch_apply($dmp->patch_fromText($this->patch), $fileContents);
                        $this->content = $p[0];
                        //DEBUG : file_put_contents($this->path.".orig",$fileContents );
                        //DEBUG : file_put_contents($this->path.".patch", $this->patch);
                    }

                    $writeSuccess = fwrite($file, $this->content);
                    fclose($file);
                    if (! $writeSuccess){
                        $this->status = "error";
                        $this->message = "could not write to file";
                    } else {
                        // Unless stat cache is cleared the pre-cached mtime will be
                        // returned instead of new modification time after editing
                        // the file.
                        clearstatcache();
                        $this->data = '"mtime":'.filemtime($this->path);
                        $this->status = "success";
                    }
                }else{
                   $this->status = "error";
                   $this->message = "Cannot Write to File";
                }
            }else{
                $this->status = "error";
                $this->message = "Not A File";
            }
        }

        $this->respond();
    }

    //////////////////////////////////////////////////////////////////
    // DUPLICATE (Creates a duplicate of the object - (cut/copy/paste)
    //////////////////////////////////////////////////////////////////

    public function duplicate(){

        if(!file_exists($this->path)){
            $this->status = "error";
            $this->message = "Invalid Source";
        }

        function recurse_copy($src,$dst) {
            $dir = opendir($src);
            @mkdir($dst);
            while(false !== ( $file = readdir($dir)) ) {
                if (( $file != '.' ) && ( $file != '..' )) {
                    if ( is_dir($src . '/' . $file) ) {
                        recurse_copy($src . '/' . $file,$dst . '/' . $file);
                    }
                    else {
                        copy($src . '/' . $file,$dst . '/' . $file);
                    }
                }
            }
            closedir($dir);
        }

        if($this->status!="error"){

            if(is_file($this->path)){
                copy($this->path,$this->destination);
                $this->status = "success";
            }else{
                recurse_copy($this->path,$this->destination);
                if(!$this->response){ $this->status = "success"; }
            }

        }

        $this->respond();
    }

    //////////////////////////////////////////////////////////////////
    // UPLOAD (Handles uploads to the specified directory)
    //////////////////////////////////////////////////////////////////

    public function upload(){

        // Check that the path is a directory
        if(is_file($this->path)){
            $this->status = "error";
            $this->message = "Path Not A Directory";
        }else{
            // Handle upload
            $info = array();
            while(list($key,$value) = each($_FILES['upload']['name'])){
                if(!empty($value)){
                    $filename = $value;
                    $add = $this->path."/$filename";
                    if(@move_uploaded_file($_FILES['upload']['tmp_name'][$key], $add)){

                        $info[] = array(
                            "name"=>$value,
                            "size"=>filesize($add),
                            "url"=>$add,
                            "thumbnail_url"=>$add,
                            "delete_url"=>$add,
                            "delete_type"=>"DELETE"
                        );
                    }
                }
            }
            $this->upload_json = json_encode($info);
        }

        $this->respond();
    }

    //////////////////////////////////////////////////////////////////
    // RESPOND (Outputs data in JSON [JSEND] format)
    //////////////////////////////////////////////////////////////////

    public function respond(){

        // Success ///////////////////////////////////////////////
        if($this->status=="success"){
            if($this->data){
                $json = '{"status":"success","data":{'.$this->data.'}}';
            }else{
                $json = '{"status":"success","data":null}';
            }

        // Upload JSON ///////////////////////////////////////////

        }elseif($this->upload_json!=''){
            $json = $this->upload_json;

        // Error /////////////////////////////////////////////////
        }else{
            $json = '{"status":"error","message":"'.$this->message.'"}';
        }

        // Output ////////////////////////////////////////////////
        echo($json);

    }

    //////////////////////////////////////////////////////////////////
    // Clean a path
    //////////////////////////////////////////////////////////////////

    public static function cleanPath( $path ){

        // prevent Poison Null Byte injections
        $path = str_replace(chr(0), '', $path );

        // prevent go out of the workspace
        while (strpos($path , '../') !== false)
            $path = str_replace( '../', '', $path );

        return $path;
    }

}

?>
