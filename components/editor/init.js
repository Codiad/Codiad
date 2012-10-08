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

    active_instance: null,

    // Default Editor-wide settings
    settings: {
        theme: 'twilight',
        font_size: '13px',
        print_margin: false,
        highlight_line: true,
        indent_guides: true,
        wrap_mode: false
    },

    get_settings : function(){
        var bool_val = null;
        var theme = localStorage.getItem('theme'); if(theme!==null){ this.settings.theme = theme; }
        var font_size = localStorage.getItem('font-size'); if(font_size!==null){ this.settings.font_size = font_size; }
        var print_margin = localStorage.getItem('print-margin'); if(print_margin!==null){
            bool_val = (print_margin == "true");
            this.settings.print_margin = bool_val;
        }
        var highlight_line = localStorage.getItem('highlight-line'); if(highlight_line!==null){
            bool_val = (highlight_line == "true");
            this.settings.highlight_line = bool_val;
        }
        var indent_guides = localStorage.getItem('indent-guides'); if(indent_guides!==null){
            bool_val = (indent_guides == "true");
            this.settings.indent_guides = bool_val;
        }
        var wrap_mode = localStorage.getItem('wrap-mode'); if(wrap_mode!==null){
            bool_val = (wrap_mode == "true");
            this.settings.wrap_mode = bool_val;
        }
    },

    add_instance: function(session){
        var i  = ace.edit('editor');

        // Check user-specified settings
        this.get_settings();

        // Apply the current configuration settings:
        i.setTheme('ace/theme/' + this.settings.theme);
        i.setFontSize(this.settings.font_size);
        i.setShowPrintMargin(this.settings.print_margin);
        i.setHighlightActiveLine(this.settings.highlight_line);
        i.setDisplayIndentGuides(this.settings.indent_guides);
        i.getSession().setUseWrapMode(this.settings.wrap_mode);

        this.change_listener(i);
        this.cursor_tracking(i);
        this.bind_keys(i);

        this.instances.push(i);
        return i;
    },

    exterminate: function(){
        $('#editor').remove();
        $('#editor-region').append($("<div>").attr('id', 'editor'));
        $('#current-file').html('');
        this.instances = [];
        this.active_instance = null;
    },

    remove_session: function(session, replacement_session){
        for (var k = 0; k < this.instances.length; k++) {
            if (this.instances[k].getSession() === session) {
                this.instances[k].setSession(replacement_session);
            }
        }
        if ($('#current-file').text() === session.path) {
            $('#current-file').text(replacement_session.path);
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
        return this.active_instance;
    },

    set_active: function(i) {
        if (! i) return;
        this.active_instance = i;
        $('#current-file').text(i.getSession().path);
    },

    set_session: function(session, i) {
        i = i || this.get_active();
        if (! i) i = this.add_instance(session);
        i.setSession(session);
        this.set_active(i);
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
        // LocalStorage
        localStorage.setItem('theme',t);
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
        // LocalStorage
        localStorage.setItem('font-size',s);
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
        // LocalStorage
        localStorage.setItem('highlight-line',h);
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
                i.setShowPrintMargin(p);
            });
        }
        // LocalStorage
        localStorage.setItem('print-margin',p);
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
        // LocalStorage
        localStorage.setItem('indent-guides',g);
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
        if (i) {
            i.getSession().setUseWrapMode(w);
        } else {
            this.for_each(function(i){
                i.getSession().setUseWrapMode(w);
            });
        }
        // LocalStorage
        localStorage.setItem('wrap-mode',w);
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
