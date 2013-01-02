/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function (global, $) {

    var codiad = global.codiad;

    $(function () {
        codiad.collaborative.init();
    });

    //////////////////////////////////////////////////////////////////
    //
    // Collaborative Component for Codiad
    // ---------------------------------
    // Displays in real time the cursor position and
    // the changes when concurrently editing files.
    //
    //////////////////////////////////////////////////////////////////

    codiad.collaborative = {

        controller: 'components/collaborative/controler.php',

        init: function () {
            this.$onDocumentChange = this.onDocumentChange.bind(this);
            this.$onCursorChange = this.onCursorChange.bind(this);
        },

        addListeners: function () {
            this.addListenerToOnDocumentChange();
            this.addListenerToOnCursorChange();
        },

        addListenerToOnDocumentChange: function () {
            var session = this._getEditSession();
            session.addEventListener('change', this.$onDocumentChange);
        },

        removeListenerToOnDocumentChange: function () {
            var session = this._getEditSession();
            session.removeEventListener('change', this.$onDocumentChange);
        },

        addListenerToOnCursorChange: function () {
            var selection = this._getSelection();
            selection.addEventListener('changeCursor', this.$onCursorChange);
        },

        removeListenerToOnCursorChange: function () {
            var selection = this._getSelection();
            selection.removeEventListener('changeCursor', this.$onCursorChange);
        },

        onDocumentChange: function (e) {
            console.log('document change');
            var post = { change: JSON.stringify(e.data) };
            console.log(post);

            $.ajax({
                type: 'POST',
                url: this.controller,
                data: post,
                complete: function (data) {
                    console.log('complete doc change');
                    console.log(data);
                }
            });
        },

        onCursorChange: function (e) {
            console.log('cursor change');
            var post = { cursor: JSON.stringify(this._getSelection().getRange()) };
            console.log(post);

            $.ajax({
                type: 'POST',
                url: this.controller,
                data: post,
                complete: function (data) {
                    console.log('complete cursor');
                    console.log(data);
                }
            });
        },

        /* Set of helper methods to manipulate the editor. */
        _getEditor: function () {
            return codiad.editor.getActive();
        },

        _getEditSession: function () {
            return codiad.editor.getActive().getSession();
        },

        _getSelection: function () {
            return codiad.editor.getActive().getSelection();
        },

        _getDocument: function () {
            return codiad.editor.getActive().getSession().getDocument();
        }

    };

})(this, jQuery);


