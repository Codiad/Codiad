/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function(global, $) {

    // Classes from Ace
    var VirtualRenderer = require('ace/virtual_renderer').VirtualRenderer;
    var Editor = require('ace/editor').Editor;

    // Editor modes that have been loaded
    var editorModes = {};

    var codiad = global.codiad;
    codiad._cursorPoll = null;

    //////////////////////////////////////////////////////////////////
    //
    // Editor Component for Codiad
    // ---------------------------
    // Manage the lifecycle of Editor instances
    //
    //////////////////////////////////////////////////////////////////

    codiad.editor = {

        // Editor instances - One instance corresponds to an editor
        // pane in the user interface. Different EditSessions (ace/edit_session)
        instances: [],

        // Currently focussed editor
        activeInstance: null,

        // Settings for Editor instances
        settings: {
            theme: 'twilight',
            fontSize: '13px',
            printMargin: false,
            highlightLine: true,
            indentGuides: true,
            wrapMode: false
        },

        //////////////////////////////////////////////////////////////////
        //
        // Retrieve editor settings from localStorage
        //
        //////////////////////////////////////////////////////////////////

        getSettings: function() {
            var boolVal = null;
            var theme = localStorage.getItem('codiad.editor.theme');

            var _this = this;

            $.each(['theme', 'fontSize'], function(idx, key) {
                var localValue = localStorage.getItem('codiad.editor.' + key);
                if (localValue !== null) {
                    _this.settings[key] = localValue;
                }
            });

            $.each(['printMargin', 'highlightLine', 'indentGuides', 'wrapMode'],
                   function(idx, key) {
                       var localValue =
                           localStorage.getItem('codiad.editor.' + key);
                       if (localValue === null) {
                           return;
                       }
                       _this.settings[key] = (localValue == 'true');
                   });
        },

        //////////////////////////////////////////////////////////////////
        //
        // Create a new editor instance attached to given session
        //
        //////////////////////////////////////////////////////////////////

        addInstance: function(session) {
            var i = ace.edit('editor');

            // Check user-specified settings
            this.getSettings();

            // Apply the current configuration settings:
            i.setTheme('ace/theme/' + this.settings.theme);
            i.setFontSize(this.settings.fontSize);
            i.setShowPrintMargin(this.settings.printMargin);
            i.setHighlightActiveLine(this.settings.highlightLine);
            i.setDisplayIndentGuides(this.settings.indentGuides);
            i.getSession().setUseWrapMode(this.settings.wrapMode);

            this.changeListener(i);
            this.cursorTracking(i);
            this.bindKeys(i);

            this.instances.push(i);
            return i;
        },

        //////////////////////////////////////////////////////////////////
        //
        // Remove all Editor instances
        //
        //////////////////////////////////////////////////////////////////

        exterminate: function() {
            $('#editor').remove();
            $('#editor-region').append($('<div>').attr('id', 'editor'));
            $('#current-file').html('');
            this.instances = [];
            this.activeInstance = null;
        },

        //////////////////////////////////////////////////////////////////
        //
        // Detach EditSession session from all Editor instances replacing
        // them with replacementSession
        //
        //////////////////////////////////////////////////////////////////

        removeSession: function(session, replacementSession) {
            for (var k = 0; k < this.instances.length; k++) {
                if (this.instances[k].getSession() === session) {
                    this.instances[k].setSession(replacementSession);
                }
            }
            if ($('#current-file').text() === session.path) {
                $('#current-file').text(replacementSession.path);
            }
        },

        /////////////////////////////////////////////////////////////////
        //
        // Convenience function to iterate over Editor instances
        //
        // Parameters:
        //   fn - {Function} callback called with each member as an
        //        argument
        //
        /////////////////////////////////////////////////////////////////

        forEach: function(fn) {
            for (var k = 0; k < this.instances.length; k++) {
                fn.call(this, this.instances[k]);
            }
        },

        /////////////////////////////////////////////////////////////////
        //
        // Get the currently active Editor instance
        //
        // In a multi-pane setup this would correspond to the
        // editor pane user is currently working on.
        //
        /////////////////////////////////////////////////////////////////

        getActive: function() {
            return this.activeInstance;
        },

        /////////////////////////////////////////////////////////////////
        //
        // Set an editor instance as active
        //
        // Parameters:
        //   i - {Editor}
        //
        /////////////////////////////////////////////////////////////////

        setActive: function(i) {
            if (! i) return;
            this.activeInstance = i;
            $('#current-file').text(i.getSession().path);
        },

        /////////////////////////////////////////////////////////////////
        //
        // Change the EditSession of Editor instance
        //
        // Parameters:
        //   session - {EditSession}
        //   i - {Editor}
        //
        /////////////////////////////////////////////////////////////////

        setSession: function(session, i) {
            i = i || this.getActive();
            if (! i) {
                i = this.addInstance(session);
            }
            i.setSession(session);
            this.setActive(i);
        },

        /////////////////////////////////////////////////////////////////
        //
        // Select file mode by extension
        //
        // Parameters:
        //   e - {String} File extension
        //
        /////////////////////////////////////////////////////////////////

        selectMode: function(e) {
            switch (e) {
            case 'html':
            case 'htm':
            case 'tpl':
                return 'html';
            case 'js':
                return 'javascript';
            case 'css':
                return 'css';
            case 'scss':
            case 'sass':
                return 'scss';
            case 'less':
                return 'less';
            case 'php':
            case 'php5':
                return 'php';
            case 'json':
                return 'json';
            case 'xml':
                return 'xml';
            case 'sql':
                return 'sql';
            case 'md':
                return 'markdown';
            default:
                return 'text';
            }
        },

        /////////////////////////////////////////////////////////////////
        //
        // Set the editor mode
        //
        // Parameters:
        //   m - {TextMode} mode
        //   i - {Editor} Editor (Defaults to active editor)
        //
        /////////////////////////////////////////////////////////////////

        setMode: function(m, i) {
            i = i || this.getActive();

            // Check if mode is already loaded
            if (! editorModes[m]) {

                // Load the Mode
                var modeFile = 'components/editor/ace-editor/mode-' + m + '.js';
                $.loadScript(modeFile, function() {

                    // Mark the mode as loaded
                    editorModes[m] = true;
                    var EditorMode = require('ace/mode/' + m).Mode;
                    i.getSession().setMode(new EditorMode());
                }, true);
            } else {

                var EditorMode = require('ace/mode/' + m).Mode;
                i.getSession().setMode(new EditorMode());

            }
        },

        /////////////////////////////////////////////////////////////////
        //
        // Set the editor theme
        //
        // Parameters:
        //   t - {String} theme eg. twilight, cobalt etc.
        //   i - {Editor} Editor instance (If omitted, Defaults to all editors)
        //
        // For a list of themes supported by Ace - refer :
        //   https://github.com/ajaxorg/ace/tree/master/lib/ace/theme
        //
        // TODO: Provide support for custom themes
        //
        /////////////////////////////////////////////////////////////////

        setTheme: function(t, i) {
            if (i) {
                // If a specific instance is specified, change the theme for
                // this instance
                i.setTheme('ace/theme/'+ t);
            } else {
                // Change the theme for the existing editor instances
                // and make it the default for new instances
                this.settings.theme = t;
                for (var k = 0; k < this.instances.length; k++) {
                    this.instances[k].setTheme('ace/theme/'+ t);
                }
            }
            // LocalStorage
            localStorage.setItem('codiad.editor.theme', t);
        },

        /////////////////////////////////////////////////////////////////
        //
        // Set contents of the editor
        //
        // Parameters:
        //   c - {String} content
        //   i - {Editor} (Defaults to active editor)
        //
        /////////////////////////////////////////////////////////////////

        setContent: function(c, i) {
            i = i || this.getActive();
            i.getSession().setValue(c);
        },

        /////////////////////////////////////////////////////////////////
        //
        // Set Font Size
        //
        // Set the font for all Editor instances and remember
        // the value for Editor instances to be created in
        // future
        //
        // Parameters:
        //   s - {Number} font size
        //   i - {Editor} Editor instance  (If omitted, Defaults to all editors)
        //
        /////////////////////////////////////////////////////////////////

        setFontSize: function(s, i) {
            if (i) {
                i.setFontSize(s);
            } else {
                this.settings.fontSize = s;
                this.forEach(function(i) {
                    i.setFontSize(s);
                });
            }
            // LocalStorage
            localStorage.setItem('codiad.editor.fontSize', s);
        },


        /////////////////////////////////////////////////////////////////
        //
        // Enable/disable Highlighting of active line
        //
        // Parameters:
        //   h - {Boolean}
        //   i - {Editor} Editor instance ( If left out, setting is
        //                    applied to all editors )
        //
        /////////////////////////////////////////////////////////////////

        setHighlightLine: function(h, i) {
            if (i) {
                i.setHighlightActiveLine(h);
            } else {
                this.settings.highlightLine = h;
                this.forEach(function(i) {
                    i.setHighlightActiveLine(h);
                });
            }
            // LocalStorage
            localStorage.setItem('codiad.editor.highlightLine', h);
        },

        //////////////////////////////////////////////////////////////////
        //
        // Show/Hide print margin indicator
        //
        // Parameters:
        //   p - {Number} print margin column
        //   i - {Editor}  (If omitted, Defaults to all editors)
        //
        //////////////////////////////////////////////////////////////////

        setPrintMargin: function(p, i) {
            if (i) {
                i.setShowPrintMargin(p);
            } else {
                this.settings.printMargin = p;
                this.forEach(function(i) {
                    i.setShowPrintMargin(p);
                });
            }
            // LocalStorage
            localStorage.setItem('codiad.editor.printMargin', p);
        },

        //////////////////////////////////////////////////////////////////
        //
        // Show/Hide indent guides
        //
        // Parameters:
        //   g - {Boolean}
        //   i - {Editor}  (If omitted, Defaults to all editors)
        //
        //////////////////////////////////////////////////////////////////

        setIndentGuides: function(g, i) {
            if (i) {
                i.setDisplayIndentGuides(g);
            } else {
                this.settings.indentGuides = g;
                this.forEach(function(i) {
                    i.setDisplayIndentGuides(g);
                });
            }
            // LocalStorage
            localStorage.setItem('codiad.editor.indentGuides', g);
        },

        //////////////////////////////////////////////////////////////////
        //
        // Enable/Disable Code Folding
        //
        // Parameters:
        //   f - {Boolean}
        //   i - {Editor}  (If omitted, Defaults to all editors)
        //
        //////////////////////////////////////////////////////////////////

        setCodeFolding: function(f, i) {
            if (i) {
                i.setFoldStyle(f);
            } else {
                this.forEach(function(i) {
                    i.setFoldStyle(f);
                });
            }
        },

        //////////////////////////////////////////////////////////////////
        //
        // Enable/Disable Line Wrapping
        //
        // Parameters:
        //   w - {Boolean}
        //   i - {Editor}  (If omitted, Defaults to all editors)
        //
        //////////////////////////////////////////////////////////////////

        setWrapMode: function(w, i) {
            if (i) {
                i.getSession().setUseWrapMode(w);
            } else {
                this.forEach(function(i) {
                    i.getSession().setUseWrapMode(w);
                });
            }
            // LocalStorage
            localStorage.setItem('codiad.editor.wrapMode', w);
        },

        //////////////////////////////////////////////////////////////////
        //
        // Get content from editor
        //
        // Parameters:
        //   i - {Editor} (Defaults to active editor)
        //
        //////////////////////////////////////////////////////////////////

        getContent: function(i) {
            i = i || this.getActive();
            if (! i) return;
            var content = i.getSession().getValue();
            if (!content) {
                content = ' ';
            } // Pass something through
            return content;
        },

        //////////////////////////////////////////////////////////////////
        //
        // Resize the editor - Trigger the editor to readjust its layout
        // esp if the container has been resized manually.
        //
        // Parameters:
        //   i - {Editor} (Defaults to active editor)
        //
        //////////////////////////////////////////////////////////////////

        resize: function(i) {
            i = i || this.getActive();
            if (! i) return;
            i.resize();
        },

        //////////////////////////////////////////////////////////////////
        //
        // Mark the instance as changed (in the user interface)
        // upon change in the document content.
        //
        // Parameters:
        //   i - {Editor}
        //
        //////////////////////////////////////////////////////////////////

        changeListener: function(i) {
            var _this = this;
            i.on('change', function() {
                codiad.active.markChanged(_this.getActive().getSession().path);
            });
        },

        //////////////////////////////////////////////////////////////////
        //
        // Get Selected Text
        //
        // Parameters:
        //   i - {Editor} (Defaults to active editor)
        //
        //////////////////////////////////////////////////////////////////

        getSelectedText: function(i) {
            i = i || this.getActive();
            if (! i) return;
            return i.getCopyText();
        },

        //////////////////////////////////////////////////////////////////
        //
        // Insert text
        //
        // Parameters:
        //   val - {String} Text to be inserted
        //   i - {Editor} (Defaults to active editor)
        //
        //////////////////////////////////////////////////////////////////

        insertText: function(val, i) {
            i = i || this.getActive();
            if (! i) return;
            i.insert(val);
        },

        //////////////////////////////////////////////////////////////////
        //
        // Move the cursor to a particular line
        //
        // Parameters:
        //   line - {Number} Line number
        //   i - {Editor} Editor instance
        //
        //////////////////////////////////////////////////////////////////

        gotoLine: function(line, i) {
            i = i || this.getActive();
            if (! i) return;
            i.gotoLine(line, 0, true);
        },

        //////////////////////////////////////////////////////////////////
        //
        // Focus an editor
        //
        // Parameters:
        //   i - {Editor} Editor instance (Defaults to current editor)
        //
        //////////////////////////////////////////////////////////////////

        focus: function(i) {
            i = i || this.getActive();
            this.setActive(i);
            if (! i) return;
            i.focus();
        },

        //////////////////////////////////////////////////////////////////
        //
        // Setup Cursor Tracking
        //
        // Parameters:
        //   i - {Editor} (Defaults to active editor)
        //
        //////////////////////////////////////////////////////////////////

        cursorTracking: function(i) {
            i = i || this.getActive();
            if (! i) return;
            clearInterval(codiad._cursorPoll);
            codiad._cursorPoll = setInterval(function() {
                $('#cursor-position')
                    .html('Ln: '
                          + (i.getCursorPosition().row + 1)
                          + ' &middot; Col: '
                          + i.getCursorPosition().column
                         );
            }, 100);
        },

        //////////////////////////////////////////////////////////////////
        //
        // Setup Key bindings
        //
        // Parameters:
        //   i - {Editor}
        //
        //////////////////////////////////////////////////////////////////

        bindKeys: function(i) {

            var _this = this;

            // Find
            i.commands.addCommand({
                name: 'Find',
                bindKey: {
                    win: 'Ctrl-F',
                    mac: 'Command-F'
                },
                exec: function(e) {
                    _this.openSearch('find');
                }
            });

            // Find + Replace
            i.commands.addCommand({
                name: 'Replace',
                bindKey: {
                    win: 'Ctrl-R',
                    mac: 'Command-R'
                },
                exec: function(e) {
                    _this.openSearch('replace');
                }
            });

            i.commands.addCommand({
                name: 'Move Up',
                bindKey: {
                    win: 'Ctrl-up',
                    mac: 'Command-up'
                },
                exec: function(e) {
                    codiad.active.move('up');
                }
            });

            i.commands.addCommand({
                name: 'Move Down',
                bindKey: {
                    win: 'Ctrl-down',
                    mac: 'Command-up'
                },
                exec: function(e) {
                    codiad.active.move('down');
                }
            });

        },

        //////////////////////////////////////////////////////////////////
        //
        // Present the Search (Find + Replace) dialog box
        //
        //////////////////////////////////////////////////////////////////

        openSearch: function(type) {
            if (this.getActive()) {
                codiad.modal.load(400,
                           'components/editor/dialog.php?action=search&type=' +
                           type);
                codiad.modal.hideOverlay();
            } else {
                codiad.message.error('No Open Files');
            }
        },

        //////////////////////////////////////////////////////////////////
        //
        // Perform Search (Find + Replace) operation
        //
        //////////////////////////////////////////////////////////////////

        search: function(action, i) {
            i = i || this.getActive();
            if (! i) return;
            var find = $('#modal input[name="find"]')
                .val();
            var replace = $('#modal input[name="replace"]')
                .val();
            switch (action) {
            case 'find':

                i.find(find, {
                    backwards: false,
                    wrap: true,
                    caseSensitive: false,
                    wholeWord: false,
                    regExp: false
                });

                break;

            case 'replace':

                i.find(find, {
                    backwards: false,
                    wrap: true,
                    caseSensitive: false,
                    wholeWord: false,
                    regExp: false
                });
                i.replace(replace);

                break;

            case 'replaceAll':

                i.find(find, {
                    backwards: false,
                    wrap: true,
                    caseSensitive: false,
                    wholeWord: false,
                    regExp: false
                });
                i.replaceAll(replace);

                break;
            }
        }

    };

})(this, jQuery);
