/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See 
*  [root]/license.txt for more. This information must remain intact.
*/

var terminal = {
    
    term_width : $(window).outerWidth()-500,
    controller : 'components/terminal/controller.php',
    
    open : function(){
        modal.load(terminal.term_width,'components/terminal/dialog.php');
    },
    
    run_command : function(c){
        cur_terminal = $('#terminal');
        if(c=='clear'){
            cur_terminal.html('');
            $('#term-command').val('').focus();
        }else{
            $('#term-command').val('Processing...');
            $.get(terminal.controller+'?command='+c,function(data){
                cur_terminal.append('<pre class="output-command">&gt;&gt;&nbsp;'+c+'</pre>');
                cur_terminal.append('<pre class="output-data">'+data+'</pre>');
                cur_terminal.scrollTop(
                    cur_terminal[0].scrollHeight - cur_terminal.height() + 20
                );
                $('#term-command').val('').focus();
            });
        }
    }
    
    
    
};