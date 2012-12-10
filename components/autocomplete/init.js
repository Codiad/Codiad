(function(global, $) {

    var codiad = global.codiad;

    $(function() {
        codiad.autocomplete.init();
    });

    //////////////////////////////////////////////////////////////////
    //
    // Autocomplete Component for Codiad
    // ---------------------------------
    // Show a popover with word completion suggestions.
    //
    //////////////////////////////////////////////////////////////////

    codiad.autocomplete = {

        init: function() {
            var _this = this;

            $('#autocomplete').popover({
                title: 'autocomplete',
                content: 'pipipopo',
                horizontalOffset: _this._computeHorizontalOffset,
                verticalOffset: _this._computeVerticalOffset,
                trigger: 'manual'
            });
        },

        suggest: function() {
            $('#autocomplete').popover('show');
            // codiad.editor.getActive()
                          // + (i.getCursorPosition().row + 1)
                          // + ' &middot; Col: '
                          // + i.getCursorPosition().column
        },

        complete: function() {
            alert('Not implemented.');
        },

        _computeHorizontalOffset: function() {
            /* FIXME How to handle multiple cursors? */
            var cursor = $('.ace_cursor');
            if(cursor.length > 0) {
                cursor = $(cursor[0]);
                var left = cursor.offset().left;
                console.log('horizontalOffset');
                console.log(left);
                return left;
            }
        },

        _computeVerticalOffset: function() {
            var editor = codiad.editor.getActive();
            if(editor != null) {
                var position = editor.getCursorPositionScreen();
                console.log('verticalOffset');
                console.log(position);
            }
        }
    };

})(this, jQuery);

