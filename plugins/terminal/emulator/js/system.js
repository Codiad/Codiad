/*
*  PHP+JQuery Temrinal Emulator by Fluidbyte <http://www.fluidbyte.net>
*
*  This software is released as-is with no warranty and is complete free
*  for use, modification and redistribution
*/

$(function(){ terminal.init(); });

var terminal = {
    
    // Controller
    controller : 'term.php',
    
    // DOM Objects
    command : $('#command input'),
    screen : $('#terminal'),
    output : $('#terminal>#output'),
    
    // Command History
    command_history : [],
    command_counter : -1,
    history_counter : -1,
    
    init : function(){
        terminal.listener();
        // Start with authentication
        terminal.process_command();
    },
    
    listener : function(){
        terminal.command.focus().keydown(function(e){
            var code = (e.keyCode ? e.keyCode : e.which);
            var command = terminal.get_command();
            switch(code){
                // Enter key, process command
                case 13:
                    if(command=='clear'){
                        terminal.clear();
                    }else{
                        terminal.command_history[++terminal.command_counter] = command;
                        terminal.history_counter = terminal.command_counter;
                        terminal.process_command();
                        terminal.command.val('Processing...').focus();
                    }
                    break;
                // Up arrow, reverse history
                case 38:
                    if(terminal.history_counter>=0){
                        $(this).val(terminal.command_history[terminal.history_counter--]);
                    }
                    break;
                // Down arrow, forward history
                case 40:
                    if (terminal.history_counter <= terminal.command_counter) {
                        $(this).val(terminal.command_history[++terminal.history_counter]);
                    }
                    break;
            }
        });
    },
    
    process_command : function(){
        var command = terminal.get_command();
        $.post(terminal.controller,{command:command},function(data){
            terminal.command.val('').focus();
            switch(data){
                case '[CLEAR]':
                    terminal.clear();
                    break;
                case '[CLOSED]':
                    terminal.clear();
                    terminal.process_command();
                    window.parent.codiad.modal.unload();
                    break;
                case '[AUTHENTICATED]':
                    terminal.command_history = [];
                    terminal.command_counter = -1;
                    terminal.history_counter = -1;
                    terminal.clear();
                    break;
                case 'Enter Password:':
                    terminal.clear();
                    terminal.display_output('Authentication Required',data);
                    terminal.command.css({'color':'#333'});
                    break;
                default:
                    terminal.display_output(command,data);
            }
        });
    },
    
    get_command : function(){
        return terminal.command.val();
    },
    
    display_output : function(command,data){
        terminal.output.append('<pre class="command">'+command+'</pre><pre class="data">'+data+'</pre>');
        terminal.screen.scrollTop(terminal.output.height());    
    },
    
    clear : function(){
        terminal.output.html('');
        terminal.command.css({ 'color':'#fff' }).val('');
    }
    
};