<?php
	//FIXME Support of windows, use command 'cd' instead of 'pwd'

    /*
    *  PHP+JQuery Temrinal Emulator by Fluidbyte <http://www.fluidbyte.net>
    *
    *  This software is released as-is with no warranty and is complete free
    *  for use, modification and redistribution
    */
    
    //////////////////////////////////////////////////////////////////
    // Password
    //////////////////////////////////////////////////////////////////
    
    define('PASSWORD','terminal');
    
    //////////////////////////////////////////////////////////////////
    // Core Stuff
    //////////////////////////////////////////////////////////////////
    
    require_once('../../../common.php');
    
    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////
    
    checkSession();

    //////////////////////////////////////////////////////////////////
    // Globals
    //////////////////////////////////////////////////////////////////
    
    define('ROOT',WORKSPACE . '/' . $_SESSION['project']);
    define('BLOCKED','ssh,telnet');
    
    //////////////////////////////////////////////////////////////////
    // Terminal Class
    //////////////////////////////////////////////////////////////////
    
    class Terminal{
        
        ////////////////////////////////////////////////////
        // Properties
        ////////////////////////////////////////////////////
        
        public $command          = '';
        public $output           = '';
        public $directory        = '';
        
        // Holder for commands fired by system
        public $command_exec     = '';
        
        ////////////////////////////////////////////////////
        // Constructor
        ////////////////////////////////////////////////////
        
        public function __construct(){
            if(!isset($_SESSION['dir']) || empty($_SESSION['dir'])){
                if(ROOT==''){
                    $this->command_exec = 'pwd';
                    $this->Execute();
                    $_SESSION['dir'] = $this->output;
                }else{
                    $this->directory = ROOT;
                    $this->ChangeDirectory();
                }
            }else{
                $this->directory = $_SESSION['dir'];
                $this->ChangeDirectory();
            }
        }
        
        ////////////////////////////////////////////////////
        // Primary call
        ////////////////////////////////////////////////////
        
        public function Process(){
            $this->ParseCommand();
            $this->Execute();
            return $this->output;
        }
        
        ////////////////////////////////////////////////////
        // Parse command for special functions, blocks
        ////////////////////////////////////////////////////
        
        public function ParseCommand(){
            
            // Explode command
            $command_parts = explode(" ",$this->command);
            
            // Handle 'cd' command
            if(in_array('cd',$command_parts)){
                $cd_key = array_search('cd', $command_parts);
                $cd_key++;
                $this->directory = $command_parts[$cd_key];
                $this->ChangeDirectory();
                // Remove from command
                $this->command = str_replace('cd '.$this->directory,'',$this->command);
            }
            
            // Replace text editors with cat
            $editors = array('vim','vi','nano');
            $this->command = str_replace($editors,'cat',$this->command);
            
            // Handle blocked commands
            $blocked = explode(',',BLOCKED);
            if(in_array($command_parts[0],$blocked)){
                $this->command = 'echo ERROR: Command not allowed';
            }
            
            // Update exec command
            $this->command_exec = $this->command . ' 2>&1';
        }
        
        ////////////////////////////////////////////////////
        // Chnage Directory
        ////////////////////////////////////////////////////
        
        public function ChangeDirectory(){
            chdir($this->directory);
            // Store new directory
            $_SESSION['dir'] = exec('pwd');
        }
        
        ////////////////////////////////////////////////////
        // Execute commands
        ////////////////////////////////////////////////////
        
        public function Execute(){
            //system
            if(function_exists('system')){
                ob_start();
                system($this->command_exec);
                $this->output = ob_get_contents();
                ob_end_clean();
            }
            //passthru
            else if(function_exists('passthru')){
                ob_start();
                passthru($this->command_exec);
                $this->output = ob_get_contents();
                ob_end_clean();
            }
            //exec
            else if(function_exists('exec')){
                exec($this->command_exec , $this->output);
                $this->output = implode("\n" , $output);
            }
            //shell_exec
            else if(function_exists('shell_exec')){
                $this->output = shell_exec($this->command_exec);
            }
            // no support
            else{
                $this->output = 'Command execution not possible on this system';
            }
        }        
        
    }
    
    //////////////////////////////////////////////////////////////////
    // Processing
    //////////////////////////////////////////////////////////////////
    
    $command = '';
    if(!empty($_POST['command'])){ $command = $_POST['command']; }
    
    if(strtolower($command=='exit')){
        
        //////////////////////////////////////////////////////////////
        // Exit
        //////////////////////////////////////////////////////////////
        
        $_SESSION['term_auth'] = 'false';
        $output = '[CLOSED]';
        
    }else if(! isset($_SESSION['term_auth']) || $_SESSION['term_auth']!='true'){
        
        //////////////////////////////////////////////////////////////
        // Authentication
        //////////////////////////////////////////////////////////////
        
        if($command==PASSWORD){
            $_SESSION['term_auth'] = 'true';
            $output = '[AUTHENTICATED]';
        }else{
            $output = 'Enter Password:';
        }
        
    }else{
    
        //////////////////////////////////////////////////////////////
        // Execution
        //////////////////////////////////////////////////////////////
        
        // Split &&
        $Terminal = new Terminal();
        $output = '';
        $command = explode("&&", $command);
        foreach($command as $c){
            $Terminal->command = $c;
            $output .= $Terminal->Process();
        }
    
    }

    
    echo(htmlentities($output));



?>
