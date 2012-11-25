/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function(global, $){
    global.codiad.terminal = {

        termWidth: $(window)
            .outerWidth() - 500,

        open: function() {
            codiad.modal.load(this.termWidth, 'components/terminal/dialog.php');
            codiad.modal.hideOverlay();
        }
        
    };
})(this, jQuery);