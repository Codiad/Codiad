/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function(global, $){
    global.codiad.terminal = {

        termWidth: $(window)
            .outerWidth() - 500,
        controller: 'components/terminal/controller.php',

        open: function() {
            codiad.modal.load(this.termWidth, 'components/terminal/dialog.php');
        },

        runCommand: function(c) {
            curTerminal = $('#terminal');
            if (c == 'clear') {
                curTerminal.html('');
                $('#term-command')
                    .val('')
                    .focus();
            } else {
                $('#term-command')
                    .val('Processing...');
                $.get(this.controller + '?command=' + escape(c), function(data) {
                    curTerminal.append('<pre class="output-command">&gt;&gt;&nbsp;' + c + '</pre>');
                    curTerminal.append('<pre class="output-data">' + data + '</pre>');
                    curTerminal.scrollTop(
                    curTerminal[0].scrollHeight - curTerminal.height() + 20);
                    $('#term-command')
                        .val('')
                        .focus();
                });
            }
        }
    };
})(this, jQuery);
