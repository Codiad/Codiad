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

        controller: 'components/collaborative/controller.php',

        filenamesAndVersion: {},

        init: function () {
            this.$onDocumentChange = this.onDocumentChange.bind(this);
            this.$onCursorChange = this.onCursorChange.bind(this);
        },

        registerAsCollaboratorOfActiveFile: function () {
            var filename = codiad.active.getPath();
            if (!(filename in this.filenamesAndVersion)) {
                /* If the current file has not already been edited, initialize
                 * its version to 0. */
                this.filenamesAndVersion[filename] = 0;
            }

            $.post(this.controller,
                    { action: 'register', filename: filename },
                    function (data) {
                    console.log('complete registering');
                    console.log(data);
                    codiad.jsend.parse(data);
                });
        },

        unregisterAsCollaboratorOfActiveFile: function () {
            $.post(this.controller,
                    { action: 'unregister', filename: codiad.active.getPath() },
                    function (data) {
                    console.log('complete unregistering');
                    console.log(data);
                    codiad.jsend.parse(data);
                });
        },

        addListeners: function () {
            this.registerAsCollaboratorOfActiveFile();
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
            /* Increment the current document version and send the change to
             * the server along with the version number. */
            var filename = codiad.active.getPath();
            ++this.filenamesAndVersion[filename];
            console.log('document change');
            var post = { action: 'documentChange',
                filename: codiad.active.getPath(),
                change: JSON.stringify(e.data),
                version: this.filenamesAndVersion[filename]
            };
            console.log(post);

            $.post(this.controller, post, function (data) {
                    console.log('complete doc change');
                    console.log(data);
                });
        },

        onCursorChange: function (e) {
            console.log('cursor change');
            var post = { action: 'cursorChange',
                filename: codiad.active.getPath(),
                selection: JSON.stringify(this._getSelection().getRange()) };
            console.log(post);

            $.post(this.controller, post, function (data) {
                    console.log('complete cursor change');
                    console.log(data);
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


