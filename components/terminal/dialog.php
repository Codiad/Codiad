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
        exit('<label style="padding: 50px 0 30px 0; text-align: center;">Terminal is currently disabled. Enable via /components/terminal/config.php</label><button onclick="codiad.modal.unload();">Close</button>');
    }
    
    //////////////////////////////////////////////////////////////////
    // Init terminal path
    //////////////////////////////////////////////////////////////////
    
    $_SESSION['terminal_path'] = WORKSPACE . '/' . $_SESSION['project'];
    
    //////////////////////////////////////////////////////////////////
    // Check Terminal Password
    //////////////////////////////////////////////////////////////////
    
    $auth_fail = false;
    
    if(isset($_GET['p'])){
        if(urldecode($_GET['p'])==$terminal_password){
            $_SESSION['terminal_auth'] = true;
        }else{
            $auth_fail = true;
        }
    }
    
    if($terminal_password!=''){
        if(empty($_SESSION['terminal_auth'])){
            $_SESSION['terminal_auth'] = false;
        }
    }else{
        $_SESSION['terminal_auth'] = true;
    }
    
    if(!$_SESSION['terminal_auth']){
        
    ?>
    
    <form id="terminal-auth">
    <label>Terminal Password</label>
    <input type="password" name="terminal_password">
    <button class="btn-left">Authenticate</button><button class="btn-right" onclick="codiad.modal.unload(); return false;">Close</button>
    </form>
    
    <script>
    $(function(){
        $('#terminal-auth input').focus();
        // Set Terminal Width
        $('#modal').css({ 'width':'350px','margin-left':'-175px' });
        // Submit Password
        $('#terminal-auth').on('submit',function(e){
            e.preventDefault();
            $('#modal-content').load('components/terminal/dialog.php?p='+escape($('input[name="terminal_password"]').val()));
        });
        <?php if($auth_fail){ ?>
        // Show auth fail message
        codiad.message.error('Incorrect Password');
        <?php } ?>
    });
    </script>
    
    <?php }else{ ?>
    
    <label>Terminal</label>
    <div id="terminal-container">
        <div id="terminal"></div>
        <input type="text" id="term-command">
        <div id="term-command-icon">&gt;&gt;</div>
    </div>
    <button onclick="codiad.modal.unload();">Close Terminal</button>
    <script>
    
    $(function(){
        
        
        command_history = [];
        command_counter = -1;
        history_counter = -1;
        
        // Set Terminal Width
        $('#modal').css({'width':codiad.terminal.termWidth+'px','margin-left':'-'+Math.round(codiad.terminal.termWidth/2)+'px'});
        
        // Set Terminal Height
        var new_height = $(window).height()-350;
        $('#terminal').css({ 'height':new_height+'px' });
        
        $('#term-command').focus();
        $('#term-command').keydown(function(e){
            code = (e.keyCode ? e.keyCode : e.which);
            // Enter key - fire command
            if(code == 13){
                var command = $(this).val();
                if (command.trim() === '' || command.trim()== 'Processing...'){ return; }
                command_history[++command_counter] = command;
                history_counter = command_counter;
                codiad.terminal.runCommand(command);
            // Up arrow - traverse history (reverse)
            }else if(code == 38){
                if(history_counter>=0){
                    $(this).val(command_history[history_counter--]);
                }
            // Down arrow - travers history (forward)
            } else if (code == 40) {
                if (history_counter <= command_counter) {
                    $(this).val(command_history[++history_counter]);
                }
            }
            
            
        });
    
    })
    
    </script>
    
    <?php } ?>