/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

var VirtualRenderer = require('ace/virtual_renderer').VirtualRenderer;
var Editor = require('ace/editor').Editor;

editor_modes = {}; // Loaded modes
cursorpoll = null;

var editor = {

    // Array of all instances - there is one editor instance
    // corresponding to each split.
    instances: [],

    // Editor-wide settings
    settings: {
        theme: 'twilight',
        font_size: 15,
        print_margin: false,
        highlight_line: true,
        indent_guides: true,
        wrap_mode: false
    },

    add_instance: function(session){
        var i  = ace.edit('editor');

        // Apply the current configuration settings:
        i.setTheme('ace/theme/' + this.settings.theme);
        i.setFontSize(this.settings.font_size);
        i.setShowPrintMargin(this.settings.print_margin);
        i.setHighlightActiveLine(this.settings.highlight_line);
        i.setDisplayIndentGuides(this.settings.indent_guides);

        this.change_listener(i);
        this.cursor_tracking(i);
        this.bind_keys(i);

        this.instances.push(i);
        return i;
    },

    exterminate: function(){
        $('#editor-region').html('').append($("<div>").attr('id', 'editor'));
        this.instances = [];
    },

    remove_session: function(session, replacement_session){
        for (var k = 0; k < this.instances.length; k++) {
            if (this.instances[k].getSession() === session) {
                this.instances[k].setSession(replacement_session);
            }
        }
    },

    for_each: function(fn){
        for (var k = 0; k < this.instances.length; k++) {
            fn.call(this, this.instances[k]);
        }
    },

    //////////////////////////////////////////////////////////////////
    // Get the currently active Editor
    //////////////////////////////////////////////////////////////////

    get_active: function() {
        if (this.instances.length > 0)
            // While there is no implementation for splitting
            // there can be at most one editor instance.
            return this.instances[0];
        else
            return null;
    },

    set_session: function(session, i) {
        i = i || this.get_active();
        if (! i) i = this.add_instance(session);
        i.setSession(session);
    },

    //////////////////////////////////////////////////////////////////
    // Select mode from extension
    //////////////////////////////////////////////////////////////////

    select_mode: function(e) {
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

    //////////////////////////////////////////////////////////////////
    // Set editor mode/language
    //////////////////////////////////////////////////////////////////

    set_mode: function(m, i) {
        i = i || this.get_active();
        if (!editor_modes[m]) { // Check if mode is already loaded
            $.loadScript("components/editor/ace-editor/mode-" + m + ".js", function() {
                editor_modes[m] = true; // Mark to not load again
                var EditorMode = require("ace/mode/" + m)
                    .Mode;
                i.getSession()
                    .setMode(new EditorMode());
            }, true);
        } else {
            var EditorMode = require("ace/mode/" + m)
                .Mode;
            i.getSession()
                .setMode(new EditorMode());
        }
    },

    //////////////////////////////////////////////////////////////////
    // Set editor theme
    //////////////////////////////////////////////////////////////////

    set_theme: function(t, i) {
        if (i) {
            // If a specific instance is specified, change the theme for
            // this instance
            i.setTheme("ace/theme/"+t);
        } else {
            // Change the theme for the existing editor instances
            // and make it the default for new instances
            this.settings.theme = t;
            for (var k = 0; k < this.instances.length; k++) {
                this.instances[k].setTheme("ace/theme/"+t);
            }
        }
    },

    //////////////////////////////////////////////////////////////////
    // Set content of editor
    //////////////////////////////////////////////////////////////////

    set_content: function(c, i) {
        i = i || this.get_active();
        i.getSession().setValue(c);
    },

    //////////////////////////////////////////////////////////////////
    // Set Font Size
    //////////////////////////////////////////////////////////////////

    set_font_size: function(s, i) {
        if (i) {
            i.setFontSize(s);
        } else {
            this.settings.font_size = s;
            this.for_each(function(i) {
                i.setFontSize(s);
            });
        }
    },

    //////////////////////////////////////////////////////////////////
    // Highlight active line
    //////////////////////////////////////////////////////////////////

    set_highlight_line: function(h, i) {
        if (i) {
            i.setHighlightActiveLine(h);
        } else {
            this.settings.highlight_line = h;
            this.for_each(function(i) {
                i.setHighlightActiveLine(h);
            });
        }
    },

    //////////////////////////////////////////////////////////////////
    // Show/Hide print margin indicator
    //////////////////////////////////////////////////////////////////

    set_print_margin: function(p, i) {
        if (i) {
            i.setShowPrintMargin(p);
        } else {
            this.settings.print_margin = p;
            this.for_each(function(i) {
                i.setPrintMargin(p);
            });
        }
    },

    //////////////////////////////////////////////////////////////////
    // Show/Hide indent guides
    //////////////////////////////////////////////////////////////////

    set_indent_guides: function(g, i) {
        if (i) {
            i.setDisplayIndentGuides(g);
        } else {
            this.settings.indent_guides = g;
            this.for_each(function(i) {
                i.setDisplayIndentGuides(g);
            });
        }
    },

    //////////////////////////////////////////////////////////////////
    // Code Folding
    //////////////////////////////////////////////////////////////////

    set_code_folding: function(f, i) {
        if (i) {
            i.setFoldStyle(f);
        } else {
            this.for_each(function(i){
                i.setFoldStyle(f);
            });
        }
    },

    //////////////////////////////////////////////////////////////////
    // Set Line Wrapping
    //////////////////////////////////////////////////////////////////

    set_wrap_mode: function(w, i) {
        i = i || this.get_active();
        if (! i) return;
        i.getSession()
            .setUseWrapMode(w);
    },

    //////////////////////////////////////////////////////////////////
    // Get content from editor by ID
    //////////////////////////////////////////////////////////////////

    get_content: function(i) {
        i = i || this.get_active();
        if (! i) return;
        var content = i.getSession().getValue();
        if (!content) {
            content = ' ';
        } // Pass something through
        return content;
    },

    //////////////////////////////////////////////////////////////////
    // Resize
    //////////////////////////////////////////////////////////////////

    resize: function(i) {
        i = i || this.get_active();
        if (! i) return;
        i.resize();
    },

    //////////////////////////////////////////////////////////////////
    // Change Listener
    //////////////////////////////////////////////////////////////////

    change_listener: function(i) {
        var _this = this;
        i.on('change', function() {
            active.mark_changed(_this.get_active().getSession().path);
        });
    },

    //////////////////////////////////////////////////////////////////
    // Get Selected Text
    //////////////////////////////////////////////////////////////////

    get_selected_text: function(i) {
        i = i || this.get_active();
        if (! i) return;
        return i.getCopyText();
    },

    //////////////////////////////////////////////////////////////////
    // Insert text
    //////////////////////////////////////////////////////////////////

    insert_text: function(val, i) {
        i = i || this.get_active();
        if (! i) return;
        i.insert(val);
    },

    //////////////////////////////////////////////////////////////////
    // Goto Line
    //////////////////////////////////////////////////////////////////

    goto_line: function(line, i) {
        i = i || this.get_active();
        if (! i) return;
        i.gotoLine(line, 0, true);
    },

    //////////////////////////////////////////////////////////////////
    // Focus
    //////////////////////////////////////////////////////////////////

    focus: function(i) {
        i = i || this.get_active();
        if (! i) return;
        i.focus();
    },

    //////////////////////////////////////////////////////////////////
    // Cursor Tracking
    //////////////////////////////////////////////////////////////////

    cursor_tracking: function(i) {
        i = i || this.get_active();
        if (! i) return;
        clearInterval(cursorpoll);
        cursorpoll = setInterval(function() {
            $('#cursor-position')
                .html('Ln: '
                      + (i.getCursorPosition().row + 1)
                      + ' &middot; Col: '
                      + i.getCursorPosition().column
                     );
        }, 100);
    },

    //////////////////////////////////////////////////////////////////
    // Bind Keys
    //////////////////////////////////////////////////////////////////

    bind_keys: function(i) {

        // Find
        i.commands.addCommand({
            name: 'Find',
            bindKey: {
                win: 'Ctrl-F',
                mac: 'Command-F'
            },
            exec: function(e) {
                editor.open_search('find');
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
                editor.open_search('replace');
            }
        });

        i.commands.addCommand({
            name: 'Move Up',
            bindKey: {
                win: 'Ctrl-up',
                mac: 'Command-up'
            },
            exec: function(e) {
                active.move('up');
            }
        });

        i.commands.addCommand({
            name: 'Move Down',
            bindKey: {
                win: 'Ctrl-down',
                mac: 'Command-up'
            },
            exec: function(e) {
                active.move('down');
            }
        });

    },

    //////////////////////////////////////////////////////////////////
    // Search (Find + Replace)
    //////////////////////////////////////////////////////////////////

    open_search: function(type) {
        if (this.get_active()) {
            modal.load(400, 'components/editor/dialog.php?action=search&type=' + type);
            modal.hide_overlay();
        } else {
            message.error('No Open Files');
        }
    },

    search: function(action, i) {
        i = i || this.get_active();
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

        case 'replace_all':

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
