<?php
   
   /*
    *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See 
    *  [root]/license.txt for more. This information must remain intact.
    */


    require_once('../../config.php');
    require_once('config.php');
    
    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////
    
    checkSession();
    
    //////////////////////////////////////////////////////////////////
    // Verify Terminal Enabled
    //////////////////////////////////////////////////////////////////
    
    if(!$terminal_enabled){
        exit('Terminal commands disabled');
    }
    
    //////////////////////////////////////////////////////////////////
    // Init terminal path
    //////////////////////////////////////////////////////////////////
    
    if(!isset($_SESSION['terminal_path'])){
        $_SESSION['terminal_path'] = WORKSPACE . '/' . $_SESSION['project'];
    }
    
    chdir($_SESSION['terminal_path']);

    //////////////////////////////////////////////////////////////////
    // Terminal Functions
    //////////////////////////////////////////////////////////////////

    function terminal($command,$restricted){
        
        if(!function_exists('exec')){
            // Exec is not enabled, print out error
            $output = 'Command execution not possible on this system';
        	$return_var = 1;
        }else{
            
            // Exec is good! Run through the process!
            
            $command_pop = explode(' ',$command);
            $first = $command_pop[0];
            
            // Handle change directory
            if($first=='cd'){
                chdir($command_pop[1]);
                $_SESSION['terminal_path']=exec('pwd');
            }
            
            // No text editors, change to 'cat' for output
            if($first=='vi' || $first=='vim' || $first=='nano'){
                $command = str_replace($first,'cat',$command);
            }
            
            // Handle restricted functions
            if(in_array($first,$restricted)){
                $command = 'echo "ERROR: Command not allowed"';
            }
            
    		exec($command , $output , $return_var);
    		$output = implode("\n" , $output);
        
        	return array('output' => $output , 'status' => $return_var);
            
        }
    }
    
    $command_response = terminal($_GET['command'],$restricted);
    
    echo($command_response['output']);
    
?>