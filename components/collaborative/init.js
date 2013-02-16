/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function (global, $) {

    /* FIXME Dynamically load diff match patch lib. Is there any better way? */
    $.getScript('lib/diff_match_patch.js');

    var codiad = global.codiad;

    $(function () {
        codiad.collaborative.init();
    });

    //////////////////////////////////////////////////////////////////
    //
    // Collaborative Component for Codiad
    // ---------------------------------
    // Displays in real time the selection position and
    // the changes when concurrently editing files.
    //
    //////////////////////////////////////////////////////////////////

    codiad.collaborative = {

        controller: 'components/collaborative/controller.php',

        /* Store the filenames and their corresponding local revisions. */
        filenamesAndRevision: {},

        /* The filename of the file to wich we are currently registered as a
         * collaborator. Might be null if we are not collaborating to any file. */
        currentFilename: null,

        /* Store the text shadows for every edited files.
         * {'filename': shadowString, ... } */
        shadows: {},

        /* Store the currently displayed usernames and their corresponding
         * current selection.
         * [username: {start: {row: 12, column: 14}, end: {row: 14, column: 19}}, ... ] */
        displayedSelections: [],

        /* Time interval in milisecond to send an heartbeat to the server. */
        heartbeatInterval: 5000,

        init: function () {
            var _this = this;

            /* Make sure to start clean by unregistering from any file first. */
            this.unregisterAsCollaboratorFromAllFiles();
            this.removeSelectionAndChangesForAllFiles();

            /* TODO For debug only, remove this for production. */
            //this.removeServerTextForAllFiles();

            this.$onDocumentChange = this.onDocumentChange.bind(this);
            this.$onSelectionChange = this.onSelectionChange.bind(this);

            this.$updateCollaboratorsSelections = this.updateCollaboratorsSelections.bind(this);
            this.$displaySelections = this.displaySelections.bind(this);

            this.$applyCollaboratorsChanges = this.applyCollaboratorsChanges.bind(this);

            this.$synchronizeText = this.synchronizeText.bind(this);

            this.$sendHeartbeat = this.sendHeartbeat.bind(this);

            /* Subscribe to know when a file is being closed. */
            amplify.subscribe('active.onClose', function (path) {
                if (_this.currentFilename === path) {
                    _this.unregisterAsCollaboratorOfCurrentFile();
                }
            });

            /* Subscribe to know when a file become active. */
            amplify.subscribe('active.onFocus', function (path) {
                _this.unregisterAsCollaboratorOfCurrentFile();
                _this.registerAsCollaboratorOfActiveFile();

                if (!(_this.currentFilename in _this.shadows)) {
                    /* Create the initial shadow for the current file. */
                    _this.shadows[_this.currentFilename] = _this._getCurrentFileText();
                    _this.sendAsShadow(_this.currentFilename, _this.shadows[_this.currentFilename]);
                }

                _this.addListeners();
            });

            /* Start to ask periodically for the potential other collaborators
             * selection. */
            setInterval(this.$updateCollaboratorsSelections, 500);

            /* Start to ask periodically for the potential other collaborators
             * changes. */
            // setInterval(this.$applyCollaboratorsChanges, 1000);
            setInterval(this.$synchronizeText, 500);

            /* Start to send an heartbeat to notify the server that we are
             * alive. */
            setInterval(this.$sendHeartbeat, this.heartbeatInterval);

            $(".collaborative-selection").live({
                mouseenter: function() {
                        $(this).parent().find(".collaborative-selection-tooltip").fadeIn('fast');
                    },
                mouseleave: function() {
                        var $this = $(this);
                        setTimeout(function() {
                            $this.parent().find(".collaborative-selection-tooltip").fadeOut('fast');
                        }, 300);
                    }
            });

        },

        removeSelectionAndChangesForAllFiles: function () {
            $.post(this.controller,
                    { action: 'removeSelectionAndChangesForAllFiles' },
                    function (data) {
                    // console.log('complete unregistering from all');
                    // console.log(data);
                    codiad.jsend.parse(data);
                });
        },

        removeServerTextForAllFiles: function () {
            $.post(this.controller,
                    { action: 'removeServerTextForAllFiles' },
                    function (data) {
                    // console.log('complete unregistering from all');
                    // console.log(data);
                    codiad.jsend.parse(data);
                });
        },

        unregisterAsCollaboratorFromAllFiles: function () {
            $.post(this.controller,
                    { action: 'unregisterFromAllFiles' },
                    function (data) {
                    // console.log('complete unregistering from all');
                    // console.log(data);
                    codiad.jsend.parse(data);
                });
        },

        registerAsCollaboratorOfActiveFile: function () {
            var filename = codiad.active.getPath();
            if (!(filename in this.filenamesAndRevision)) {
                /* If the current file has not already been edited, initialize
                 * its revision to 0. */
                this.filenamesAndRevision[filename] = 0;
            }

            this.currentFilename = filename;

            $.post(this.controller,
                    { action: 'registerToFile', filename: filename },
                    function (data) {
                    // console.log('complete registering');
                    // console.log(data);
                    codiad.jsend.parse(data);
                });
        },

        unregisterAsCollaboratorOfCurrentFile: function () {
            // console.log('unregister ' + this.currentFilename);
            if (this.currentFilename !== null) {
                $.post(this.controller,
                        { action: 'unregisterFromFile', filename: this.currentFilename },
                        function (data) {
                            // console.log('complete unregistering');
                            // console.log(data);
                            codiad.jsend.parse(data);
                        });

                this.currentFilename = null;
            }
        },

        sendHeartbeat: function () {
            $.post(this.controller,
                    { action: 'sendHeartbeat' },
                    function (data) {
                        // console.log('complete sendHeartbeat');
                        // console.log(data);
                        codiad.jsend.parse(data);
                    });
        },

        /* Add appropriate listeners to the current EditSession. */
        addListeners: function () {
            this.addListenerToOnDocumentChange();
            this.addListenerToOnSelectionChange();
        },

        /* Remove listeners from the current EditSession. */
        removeListeners: function () {
            this.removeListenerToOnDocumentChange();
            this.removeListenerToOnSelectionChange();
        },

        addListenerToOnDocumentChange: function () {
            var session = this._getEditSession();
            session.addEventListener('change', this.$onDocumentChange);
        },

        removeListenerToOnDocumentChange: function () {
            var session = this._getEditSession();
            session.removeEventListener('change', this.$onDocumentChange);
        },

        addListenerToOnSelectionChange: function () {
            var selection = this._getSelection();
            selection.addEventListener('changeCursor', this.$onSelectionChange);
            selection.addEventListener('changeSelection', this.$onSelectionChange);
        },

        removeListenerToOnSelectionChange: function () {
            var selection = this._getSelection();
            selection.removeEventListener('changeCursor', this.$onSelectionChange);
            selection.removeEventListener('changeSelection', this.$onSelectionChange);
        },

        onDocumentChange: function (e) {
            /* Increment the current document revision and send the change to
             * the server along with the revision number. */
            var filename = codiad.active.getPath();
            ++this.filenamesAndRevision[filename];
            // console.log('document change');
            var post = { action: 'sendDocumentChange',
                filename: codiad.active.getPath(),
                change: JSON.stringify(e.data),
                revision: this.filenamesAndRevision[filename]
            };
            // console.log(post);

            $.post(this.controller, post, function (data) {
                // console.log('complete doc change');
                // console.log(data);
                codiad.jsend.parse(data);
            });
        },

        onSelectionChange: function (e) {
            // console.log('selection change');
            var post = { action: 'sendSelectionChange',
                filename: codiad.active.getPath(),
                selection: JSON.stringify(this._getSelection().getRange()) };
            // console.log(post);

            $.post(this.controller, post, function (data) {
                // console.log('complete selection change');
                // console.log(data);
                codiad.jsend.parse(data);
            });
        },

        /* Request the server for the collaborators selections for the current
         * file. */
        updateCollaboratorsSelections: function () {
            var _this = this;
            if (this.currentFilename !== null) {
                $.post(this.controller,
                        { action: 'getUsersAndSelectionsForFile', filename: this.currentFilename },
                        function (data) {
                            // console.log('complete getUsersAndSelectionsForFile');
                            // console.log(data);
                            var selections = codiad.jsend.parse(data);
                            _this.$displaySelections(selections);

                            /* Nobody is registered, remove every displayed selections. */
                            if (_this.displayedSelections !== null) {
                                for (var username in _this.displayedSelections) {
                                    if (_this.displayedSelections.hasOwnProperty(username)) {
                                        if (selections === null || !(username in selections)) {
                                            _this.removeSelection(username);
                                        }
                                    }
                                }
                            }

                            _this.displayedSelections = selections;
                        });
            }
        },

        /* Displays a selection in the current file for the given user.
         * The expected selection object is compatible with what is returned
         * from the getUsersAndSelectionsForFile action on the server
         * controller.
         * Selection object example:
         * {username: {start: {row: 12, column: 14}, end: {row: 14, column: 19}}} */
        displaySelections: function (selections) {
            // console.log('displaySelection');
            for (var username in selections) {
                if (selections.hasOwnProperty(username)) {
                    var markup = $('#selection-' + username);
                    if (markup.length === 0) {
                        /* The markup for the selection of this user does not
                         * exist yet. Append it to the dom. */
                        markup = $(this.getSelectionMarkupForUser(username));
                        $('body').append(markup);
                    }

                    var screenCoordinates = this._getEditor().renderer
                        .textToScreenCoordinates(selections[username].selection.start.row,
                                                selections[username].selection.start.column);

                    /* Check if the selection has changed. */
                    if (markup.css('left').slice(0, -2) !== String(screenCoordinates.pageX) ||
                        markup.css('top').slice(0, -2) !== String(screenCoordinates.pageY)) {

                        markup.css({
                            left: screenCoordinates.pageX,
                            top: screenCoordinates.pageY
                        });
                        
                        markup.children('.collaborative-selection').css('background-color', selections[username].color);
                        markup.children('.collaborative-selection-tooltip').css('background-color', selections[username].color);
                        
                        var timeoutRef = markup.attr('hideTooltipTimeoutRef');
                        if (timeoutRef !== undefined) {
                            clearTimeout(timeoutRef);
                            markup.removeAttr('hideTooltipTimeoutRef');
                        }
                        
                        markup.children('.collaborative-selection-tooltip').fadeIn('fast');
                        timeoutRef = setTimeout(this._hideTooltipForBoundMarkup.bind(markup), 2000);
                        markup.attr('hideTooltipTimeoutRef', timeoutRef);
                    }
                }
            }
        },
        
        /* This function must be bound with the markup which contains
         * the tooltip to hide. */
        _hideTooltipForBoundMarkup: function() {
            this.children('.collaborative-selection-tooltip').fadeOut('fast');
            this.removeAttr('hideTooltipTimeoutRef');
        },

        /* Remove the selection corresponding to the given username. */
        removeSelection: function (username) {
            console.log('remove ' + username);
            $('#selection-' + username).remove();
        },

        /* Request the server for the collaborators changes and apply them if
         * any. */
        applyCollaboratorsChanges: function () {
            var _this = this;
            if (this.currentFilename !== null) {
                // console.log( { action: 'getUsersAndChangesForFile',
                            // filename: this.currentFilename,
                            // fromRevision: this.filenamesAndRevision[this.currentFilename]  });

                $.post(this.controller,
                        { action: 'getUsersAndChangesForFile',
                            filename: this.currentFilename,
                            fromRevision: this.filenamesAndRevision[this.currentFilename] },
                        function (data) {
                            // console.log('complete getUsersAndChangesForFile');
                            // console.log(data);
                            var changes = codiad.jsend.parse(data);
                            // _this.$applyChanges(changes);
                        });
            }
        },

        /* Make a diff of the current file text with the shadow and send it to
         * the server. */
        synchronizeText: function () {
            var _this = this;
            var currentFilename = this.currentFilename;

            /* Do not send any request if no file is focused. */
            if (currentFilename === null) {
                return;
            }

            /* Save the current text state, because it can be modified by the
             * user on the UI thread. */
            var currentText = this._getCurrentFileText();

            /* Make a diff between the current text and the previously saved
             * shadow. */
            codiad.workerManager.addTask({
                taskType: 'diff',
                id: 'collaborative_' + currentFilename,
                original: _this.shadows[currentFilename],
                changed: currentText
            }, function (success, patch) {
                if (success) {
                    /* Send our edits to the server, and get in response a
                     * patch of the edits in the server text. */
                    // console.log(patch);
                    _this.shadows[currentFilename] = currentText;

                    var post = { action: 'synchronizeText',
                        filename: currentFilename,
                        patch: patch };
                    // console.log(post);

                    $.post(this.controller, post, function (data) {
                        // console.log('complete synchronizeText');
                        // console.log(data);
                        patchFromServer = codiad.jsend.parse(data);
                        // console.log(patchFromServer);

                        /* Apply the patch from the server text to the shadow
                         * and the current text. */
                        var dmp = new diff_match_patch();
                        var patchedShadow = dmp.patch_apply(dmp.patch_fromText(patchFromServer), _this.shadows[currentFilename]);
                        // console.log(patchedShadow);
                        _this.shadows[currentFilename] = patchedShadow[0];

                        /* Update the current text. */
                        currentText = _this._getCurrentFileText();
                        var patchedCurrentText = dmp.patch_apply(dmp.patch_fromText(patchFromServer), currentText)[0];

                        var diff = dmp.diff_main(currentText, patchedCurrentText);
                        var deltas = _this.diffToAceDeltas(diff, currentText);

                        _this._getDocument().applyDeltas(deltas);
                    });
                } else {
                    console.log('problem diffing');
                    console.log(patch);
                }
            }, this);
        },

        /* Send the string 'shadow' as server shadow for 'filename'. */
        sendAsShadow: function (filename, shadow) {
            $.post(this.controller,
                    { action: 'sendShadow',
                    filename: filename,
                    shadow: shadow },
                function (data) {
                    // console.log('complete sendShadow');
                    // console.log(data);
                    codiad.jsend.parse(data);
                });
        },

        /* Helper method that return a Ace editor delta change from a
         * diff_match_patch diff object and the original text that was
         * used to compute the diff. */
        diffToAceDeltas: function (diff, originalText) {
            var dmp = new diff_match_patch();
            var deltas = dmp.diff_toDelta(diff).split('\t');

            /*
             * Code deeply inspired by chaoscollective / Space_Editor
             */
            var offset = 0;
            var row = 1;
            var col = 1;
            var aceDeltas = [];
            for (var i = 0; i < deltas.length; ++i) {
                var type = deltas[i].charAt(0);
                var data = decodeURI(deltas[i].substring(1));

                switch (type) {
                    case "=":
                        /* The new text is equal to the original text for a
                        * number of characters. */
                        var unchangedCharactersCount = parseInt(data, 10);
                        for (var j = 0; j < unchangedCharactersCount; ++j) {
                            if (originalText.charAt(offset + j) == "\n") {
                                ++row;
                                col = 1;
                            } else {
                                col++;
                            }
                        }
                        offset += unchangedCharactersCount;
                        break;

                case "+":
                    /* Some characters were added. */
                    var aceDelta = {
                        action: "insertText",
                        range: {
                            start: {row: (row - 1), column: (col - 1)},
                            end: {row: (row - 1), column: (col - 1)}
                        },
                        text: data
                    };
                    aceDeltas.push(aceDelta);

                    var innerRows = data.split("\n");
                    var innerRowsCount = innerRows.length - 1;
                    row += innerRowsCount;
                    if (innerRowsCount <= 0) {
                        col += data.length;
                    } else {
                        col = innerRows[innerRowsCount].length + 1;
                    }
                    break;

                case "-":
                    /* Some characters were subtracted. */
                    var deletedCharactersCount = parseInt(data, 10);
                    var removedData = originalText.substring(offset, offset + deletedCharactersCount);
                    
                    var removedRows = removedData.split("\n");
                    var removedRowsCount = removedRows.length - 1;
                    
                    var endRow = row + removedRowsCount;
                    var endCol = col;
                    if (removedRowsCount <= 0) {
                        endCol = col + deletedCharactersCount;
                    } else {
                        endCol = removedRows[removedRowsCount].length + 1;
                    }

                    var aceDelta = {
                        action: "removeText",
                        range: {
                            start: {row: (row - 1), column: (col - 1)},
                            end: {row: (endRow - 1), column: (endCol - 1)}
                        },
                        text: data
                    };
                    aceDeltas.push(aceDelta);

                    offset += deletedCharactersCount;
                    break;

                  default:
                    /* Return an innofensive empty list of Ace deltas. */
                    console.log("Unhandled case '" + type + "' while building Ace deltas.");
                    return [];
              }
            }
            return aceDeltas;
        },

        getSelectionMarkupForUser: function (username) {
            return '<div id="selection-' + username + '" class="collaborative-selection-wrapper">' +
                '<div class="collaborative-selection"></div>' +
                '<div class="collaborative-selection-tooltip">' + username + '</div>' +
                '</div>';
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
        },

        _getCurrentFileText: function () {
            return codiad.editor.getActive().getSession().getValue();
        }


    };

})(this, jQuery);

