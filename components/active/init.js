/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

var EditSession = require('ace/edit_session').EditSession;
var UndoManager = require("ace/undomanager").UndoManager;

$(function() {
    active.init();
});

var active = {

    controller: 'components/active/controller.php',

    // Path to EditSession instance mapping
    sessions: {},

    is_open: function(path){
        return !! this.sessions[path];
    },

    open: function(path, content, in_background){
        if (this.is_open(path)) {
            this.focus(path);
            return;
        }
        var ext = filemanager.get_extension(path);
        var mode = editor.select_mode(ext);
        var _this = this;

        var fn = function(){
            var Mode = require('ace/mode/'+mode).Mode;

            // TODO: Ask for user confirmation before recovering
            // And maybe show a diff
            var draft = active.check_draft(path);
            if (draft) {
                content = draft;
                message.success('Recovered unsaved content for : ' + path );
            }

            var session = new EditSession(content, new Mode());
            session.setUndoManager(new UndoManager());

            session.path = path;
            _this.sessions[path] = session;
            if (! in_background) {
                editor.set_session(session);
            }
            _this.add(path, session);
        }

        $.loadScript('components/editor/ace-editor/mode-' + mode + '.js',
                    fn );

    },

    init: function() {

        // Focus
        $('#active-files a')
            .live('click', function() {
            active.focus($(this)
                .attr('data-path'));
        });

        // Remove
        $('#active-files a>span')
            .live('click', function(e) {
            e.stopPropagation();
            active.remove($(this)
                .parent('a')
                .attr('data-path'));
        });

        // Sortable
        $('#active-files')
            .sortable({
            placeholder: 'active-sort-placeholder',
            tolerance: 'intersect',
            start: function(e, ui) {
                ui.placeholder.height(ui.item.height());
            }
        });

        // Open saved-state active files on load
        $.get(active.controller + '?action=list', function(data) {
            var list_response = jsend.parse(data);
            if (list_response !== null) {
                $.each(list_response, function(index, value) {
                    filemanager.open_file(value);
                });
                // Run resize command to fix render issues
                active.resize();
            }
        });

        // Run resize on window resize
        $(window)
            .on('resize', function() {
            active.resize();
        });

        // Prompt if a user tries to close window without saving all filess
        window.onbeforeunload = function(e) {
            if ($('#active-files a.changed')
                .length > 0) {
                var e = e || window.event;
                var err_msg = "You have unsaved files."

                // For IE and Firefox prior to version 4
                if (e) {
                    e.returnValue = err_msg;
                }

                // For rest
                return err_msg;
            }
        };
    },

    //////////////////////////////////////////////////////////////////
    // Drafts
    //////////////////////////////////////////////////////////////////

    check_draft: function(path) {
        var draft = localStorage.getItem(path);
        if (draft !== null) {
            return draft;
        } else {
            return false;
        }
    },

    remove_draft: function(path) {
        localStorage.removeItem(path);
    },

    //////////////////////////////////////////////////////////////////
    // Get active editor path
    //////////////////////////////////////////////////////////////////

    get_path: function() {
        try {
            return editor.get_active().getSession().path;
        } catch(e) {
            return null;
        }
    },

    //////////////////////////////////////////////////////////////////
    // Check if opened by another user
    //////////////////////////////////////////////////////////////////

    check: function(path) {
        $.get(active.controller + '?action=check&path=' + path, function(data) {
            var check_response = jsend.parse(data);
        });
    },

    //////////////////////////////////////////////////////////////////
    // Add newly opened file to list
    //////////////////////////////////////////////////////////////////

    add: function(path, session) {
        var thumb = $('<a data-path="' +
                      path +
                      '"><span></span><div>' +
                      path +
                      '</div></a>');
        session.thumb = thumb;
        $('#active-files').append($('<li>').append(thumb));
        $.get(active.controller + '?action=add&path=' + path);
        this.focus(path);
        // Mark draft as changed
        if (active.check_draft(path)) {
            active.mark_changed(path);
        }
    },

    //////////////////////////////////////////////////////////////////
    // Focus on opened file
    //////////////////////////////////////////////////////////////////

    focus: function(path) {
        $('#active-files a')
            .removeClass('active');
        this.sessions[path].thumb.addClass('active');
        var session = this.sessions[path];
        editor.get_active().setSession(session);
        active.check(path);
    },

    //////////////////////////////////////////////////////////////////
    // Mark changed
    //////////////////////////////////////////////////////////////////

    mark_changed: function(path) {
        this.sessions[path].thumb.addClass('changed');
    },

    //////////////////////////////////////////////////////////////////
    // Save active editor
    //////////////////////////////////////////////////////////////////

    save: function(path) {
        if ((path && ! this.is_open(path)) || (!path && ! editor.get_active())){
            message.error('No Open Files to save');
            return;
        }
        var session;
        if (path) session = this.sessions[path];
        else session = editor.get_active().getSession();
        var content = session.getValue();
        var path = session.path;
        filemanager.save_file(path, content, {
            success: function(){
                session.thumb.removeClass('changed');
                active.remove_draft(path);
            }
        });
    },

    //////////////////////////////////////////////////////////////////
    // Remove file
    //////////////////////////////////////////////////////////////////

    remove: function(path) {
        if (! this.is_open(path)) return;
        var session = this.sessions[path];
        var close_file = true;
        if (session.thumb.hasClass('changed')) {
            modal.load(450, 'components/active/dialog.php?action=confirm&path=' + path);
            close_file = false;
        }
        if (close_file) {
            active.close(path);
        }
    },

    close: function(path) {
        var session = this.sessions[path];
        session.thumb.parent('li').remove();
        var next_thumb = $('#active-files a[data-path]');
        if (next_thumb.length == 0) {
            editor.exterminate();
        } else {
            $(next_thumb[0]).addClass('active');
            var next_path = next_thumb.attr('data-path');
            var next_session = this.sessions[next_path];
            editor.remove_session(session, next_session);
        }
        delete this.sessions[path];
        /*if ((session.thumb).hasClass('active')) {
            $('#current-file')
                .html('');
            clearInterval(cursorpoll);
            $('#cursor-position')
                .html('Ln: 0 &middot; Col: 0');
        }*/
        $.get(active.controller + '?action=remove&path=' + path);
        // Remove any draft content
        active.remove_draft(path);
    },

    //////////////////////////////////////////////////////////////////
    // Process rename
    //////////////////////////////////////////////////////////////////

    rename: function(old_path, new_path) {
        if ($('#current-file')
            .html() == old_path) {
            $('#current-file')
                .html(new_path);
        }
        $.get(active.controller + '?action=rename&old_path=' + old_path + '&new_path=' + new_path);
        $('#active-files a')
            .each(function() {
            cur_path = $(this)
                .attr('data-path');
            change_path = cur_path.replace(old_path, new_path);
            // Active file object
            $(this)
                .attr('data-path', change_path)
                .children('div')
                .html(change_path);
            // Associated editor
        });
    },

    //////////////////////////////////////////////////////////////////
    // Resize
    //////////////////////////////////////////////////////////////////

    resize: function() {
        $('#active-files a')
            .each(function() {
            cur_path = $(this)
                .attr('data-path');
            editor.resize();
        });
    },

    //////////////////////////////////////////////////////////////////
    // Open in Browser
    //////////////////////////////////////////////////////////////////

    open_in_browser: function() {
        var path = this.get_path();
        if (path) {
            filemanager.open_in_browser(path);
        } else {
            message.error('No Open Files');
        }
    },

    //////////////////////////////////////////////////////////////////
    // Get Selected Text
    //////////////////////////////////////////////////////////////////

    get_selected_text: function() {
        var path = this.get_path();

        // var id = this.get_id();
        //if (path && id) {
        if (path && this.is_open(path)) {
            return this.sessions[path].getSelection();
            //return editor.get_selected_text(active.get_id());
        } else {
            message.error('No Open Files or Selected Text');
        }
    },

    //////////////////////////////////////////////////////////////////
    // Insert Text
    //////////////////////////////////////////////////////////////////

    insert_text: function(val) {
        editor.get_active().insert(val);
        //editor.insert_text(active.get_id(), val);
    },

    //////////////////////////////////////////////////////////////////
    // Goto Line
    //////////////////////////////////////////////////////////////////

    goto_line: function(line) {
        editor.get_active().gotoLine(line, 0, true);
        //editor.goto_line(active.get_id(), line);
    },

    //////////////////////////////////////////////////////////////////
    // Move Up (Key Combo)
    //////////////////////////////////////////////////////////////////

    move: function(dir) {

        var num = $('#active-files a')
            .length;
        if (num > 1) {
            if (dir == 'up') {
                // Move Up or rotate to bottom
                new_active = $('#active-files li a.active')
                    .parent('li')
                    .prev('li')
                    .children('a')
                    .attr('data-path');
                if (!new_active) {
                    new_active = $('#active-files li:last-child a')
                        .attr('data-path');
                }

            } else {
                // Move down or rotate to top
                new_active = $('#active-files li a.active')
                    .parent('li')
                    .next('li')
                    .children('a')
                    .attr('data-path');
                if (!new_active) {
                    new_active = $('#active-files li:first-child a')
                        .attr('data-path');
                }

            }

            active.focus(new_active);
        }

    }

};
