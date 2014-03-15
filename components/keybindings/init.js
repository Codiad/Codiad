/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function(global, $){

    var codiad = global.codiad;

    //////////////////////////////////////////////////////////////////////
    // CTRL Key Bind
    //////////////////////////////////////////////////////////////////////

    $.ctrl = function(key, callback, args) {
        $(document)
            .keydown(function(e) {
            if (!args) args = [];
            if (e.keyCode == key && (e.ctrlKey || e.metaKey)) {
                if (!(e.ctrlKey && e.altKey)) {
                    callback.apply(this, args);
                    return false;
                }
            }
        });
    };

    $(function() {
        codiad.keybindings.init();
    });

    //////////////////////////////////////////////////////////////////////
    // Bindings
    //////////////////////////////////////////////////////////////////////

    codiad.keybindings = {

        init: function() {

            // Close Modals //////////////////////////////////////////////
            $(document)
                .keyup(function(e) {
                if (e.keyCode == 27) {
                    codiad.modal.unload();
                }
            });

            // Save [CTRL+S] /////////////////////////////////////////////
            $.ctrl('83', function() {
                codiad.active.save();
            });

            // Open in browser [CTRL+O] //////////////////////////////////
            $.ctrl('79', function() {
                codiad.active.openInBrowser();
            });

            // Find [CTRL+F] /////////////////////////////////////////////
            $.ctrl('70', function() {
                codiad.editor.openSearch('find');
            });

            // Replace [CTRL+R] //////////////////////////////////////////
            $.ctrl('82', function() {
                codiad.editor.openSearch('replace');
            });

            // Active List Previous [CTRL+UP] ////////////////////////////
            $.ctrl('38', function() {
                codiad.active.move('up');
            });

            // Active List Next [CTRL+DOWN] //////////////////////////////
            $.ctrl('40', function() {
                codiad.active.move('down');
            });

            // Autocomplete [CTRL+SPACE] /////////////////////////////////
            $.ctrl('32', function() {
                codiad.autocomplete.suggest();
            });

            $.ctrl('71', function(){
                if (codiad.finder) {
                    codiad.finder.expandFinder();
                }
            });
        }
    };

})(this, jQuery);
