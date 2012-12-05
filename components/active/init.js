/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function(global, $) {

    var EditSession = require('ace/edit_session')
        .EditSession;
    var UndoManager = require('ace/undomanager')
        .UndoManager;

    var codiad = global.codiad;

    $(function() {
        codiad.active.init();
    });

    //////////////////////////////////////////////////////////////////
    //
    // Active Files Component for Codiad
    // ---------------------------------
    // Track and manage EditSession instaces of files being edited.
    //
    //////////////////////////////////////////////////////////////////

    codiad.active = {

        controller: 'components/active/controller.php',

        // Path to EditSession instance mapping
        sessions: {},

        //////////////////////////////////////////////////////////////////
        //
        // Check if a file is open.
        //
        // Parameters:
        //   path - {String}
        //
        //////////////////////////////////////////////////////////////////

        isOpen: function(path) {
            return !!this.sessions[path];
        },

        open: function(path, content, inBackground) {
            var _this = this;
            if (this.isOpen(path)) {
                this.focus(path);
                return;
            }
            var ext = codiad.filemanager.getExtension(path);
            var mode = codiad.editor.selectMode(ext);
            var _this = this;

            var fn = function() {
                //var Mode = require('ace/mode/' + mode)
                //    .Mode;

                // TODO: Ask for user confirmation before recovering
                // And maybe show a diff
                var draft = _this.checkDraft(path);
                if (draft) {
                    content = draft;
                    codiad.message.success('Recovered unsaved content for : ' + path);
                }

                //var session = new EditSession(content, new Mode());
                var session = new EditSession(content);
                session.setMode("ace/mode/" + mode);
                session.setUndoManager(new UndoManager());

                session.path = path;
                _this.sessions[path] = session;
                if (!inBackground) {
                    codiad.editor.setSession(session);
                }
                _this.add(path, session);
            };

            // Assuming the mode file has no dependencies
            $.loadScript('components/editor/ace-editor/mode-' + mode + '.js',
            fn);

        },

        init: function() {

            var _this = this;

            // Focus
            $('#active-files a')
                .live('click', function() {
                _this.focus($(this)
                    .attr('data-path'));
            });

            // Tab Focus
            $('.tab-list li.tab-item')
                .live('mousedown', function() {
                _this.focus($(this)
                    .attr('data-path'));
            });

            // Remove
            $('#active-files a>span')
                .live('click', function(e) {
                e.stopPropagation();
                /* Get the active editor before removing anything. Remove the
                 * tab, then put back the focus on the previously active
                 * editor if it was not removed. */
                var activePath = _this.getPath();
                var pathToRemove = $(this).parent('a').attr('data-path');
                _this.remove(pathToRemove);
                if (activePath !== null && activePath !== pathToRemove) {
                    _this.focus(activePath);
                }
            });
            
            // Tab Remove
            $('.tab-list a.close')
                .live('click', function(e) {
                e.stopPropagation();
                /* Get the active editor before removing anything. Remove the
                 * tab, then put back the focus on the previously active
                 * editor if it was not removed. */
                var activePath = _this.getPath();
                var pathToRemove = $(this).parent('li').attr('data-path');
                _this.remove(pathToRemove);
                if (activePath !== null && activePath !== pathToRemove) {
                    _this.focus(activePath);
                }
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
            
            // Tab Sortable
            $('.tab-list')
                .sortable({
                items: '> li',
                axis: 'x',
                tolerance: 'intersect',
                start: function(e, ui) {
                    ui.placeholder.css('background', 'transparent');
                }
            });

            // Open saved-state active files on load
            $.get(_this.controller + '?action=list', function(data) {
                var listResponse = codiad.jsend.parse(data);
                if (listResponse !== null) {
                    $.each(listResponse, function(index, value) {
                        codiad.filemanager.openFile(value);
                    });
                    // Run resize command to fix render issues
                    codiad.editor.resize();
                }
            });

            // Run resize on window resize
            $(window)
                .on('resize', function() {
                codiad.editor.resize();
            });

            // Prompt if a user tries to close window without saving all filess
            window.onbeforeunload = function(e) {
                if ($('#active-files a.changed')
                    .length > 0) {
                    var e = e || window.event;
                    var errMsg = 'You have unsaved files.';

                    // For IE and Firefox prior to version 4
                    if (e) {
                        e.returnValue = errMsg;
                    }

                    // For rest
                    return errMsg;
                }
            };
        },

        //////////////////////////////////////////////////////////////////
        // Drafts
        //////////////////////////////////////////////////////////////////

        checkDraft: function(path) {
            var draft = localStorage.getItem(path);
            if (draft !== null) {
                return draft;
            } else {
                return false;
            }
        },

        removeDraft: function(path) {
            localStorage.removeItem(path);
        },

        //////////////////////////////////////////////////////////////////
        // Get active editor path
        //////////////////////////////////////////////////////////////////

        getPath: function() {
            try {
                return codiad.editor.getActive()
                    .getSession()
                    .path;
            } catch (e) {
                return null;
            }
        },

        //////////////////////////////////////////////////////////////////
        // Check if opened by another user
        //////////////////////////////////////////////////////////////////

        check: function(path) {
            $.get(this.controller + '?action=check&path=' + path,

            function(data) {
                var checkResponse = codiad.jsend.parse(data);
            });
        },

        //////////////////////////////////////////////////////////////////
        // Add newly opened file to list
        //////////////////////////////////////////////////////////////////

        add: function(path, session) {
            var thumb = $('<a title="'+path+'" data-path="' + path + '"><span></span><div>' + path.substring(1) + '</div></a>');
            session.thumb = thumb;
            $('#active-files')
                .append($('<li>')
                .append(thumb));
            $.get(this.controller + '?action=add&path=' + path);
            
            var tabThumb = $('<li class="tab-item" data-path="'+path+'"><a class="content" title="'+path+'">' + path.substring(1) + '</a><a class="close">x</a></li>');
            $('.tab-list')
                .append(tabThumb);
            session.tabThumb = tabThumb;
            
            this.focus(path);
            // Mark draft as changed
            if (this.checkDraft(path)) {
                this.markChanged(path);
            }
        },

        //////////////////////////////////////////////////////////////////
        // Focus on opened file
        //////////////////////////////////////////////////////////////////

        focus: function(path) {
            this.highlightEntry(path);
            var session = this.sessions[path];
            codiad.editor.setSession(session);
            this.check(path);
        },

        highlightEntry: function(path){
            $('#active-files a')
                .removeClass('active');
            this.sessions[path].thumb.addClass('active');
            
            $('.tab-list')
                .removeClass('active');
            $('.tab-list li')
                .removeClass('active');
            this.sessions[path].tabThumb.addClass('active');
            this.sessions[path].tabThumb.find('li').addClass('active');
        },

        //////////////////////////////////////////////////////////////////
        // Mark changed
        //////////////////////////////////////////////////////////////////

        markChanged: function(path) {
            this.sessions[path].thumb.addClass('changed');
        },

        //////////////////////////////////////////////////////////////////
        // Save active editor
        //////////////////////////////////////////////////////////////////

        save: function(path) {
            var _this = this;
            if ((path && !this.isOpen(path)) || (!path && !codiad.editor.getActive())) {
                codiad.message.error('No Open Files to save');
                return;
            }
            var session;
            if (path) session = this.sessions[path];
            else session = codiad.editor.getActive()
                .getSession();
            var content = session.getValue();
            var path = session.path;
            codiad.filemanager.saveFile(path, content, {
                success: function() {
                    session.thumb.removeClass('changed');
                    _this.removeDraft(path);
                }
            });
        },

        //////////////////////////////////////////////////////////////////
        // Remove file
        //////////////////////////////////////////////////////////////////

        remove: function(path) {
            if (!this.isOpen(path)) return;
            var session = this.sessions[path];
            var closeFile = true;
            if (session.thumb.hasClass('changed')) {
                codiad.modal.load(450, 'components/active/dialog.php?action=confirm&path=' + path);
                closeFile = false;
            }
            if (closeFile) {
                this.close(path);
            }
        },

        close: function(path) {
            var session = this.sessions[path];
            session.thumb.parent('li')
                .remove();
            session.tabThumb.remove();
            var nextThumb = $('#active-files a[data-path]');
            if (nextThumb.length == 0) {
                codiad.editor.exterminate();
            } else {
                $(nextThumb[0])
                    .addClass('active');
                // TODO : Change this when finilizing tabs.
                 $($('.tab-list li[data-path]')[0])
                    .addClass('active');
                var nextPath = nextThumb.attr('data-path');
                var nextSession = this.sessions[nextPath];
                codiad.editor.removeSession(session, nextSession);
            }
            delete this.sessions[path];
            $.get(this.controller + '?action=remove&path=' + path);
            this.removeDraft(path);
        },

        //////////////////////////////////////////////////////////////////
        // Process rename
        //////////////////////////////////////////////////////////////////

        rename: function(oldPath, newPath) {
            var switchSessions = function(oldPath, newPath) {
                var thumb = this.sessions[oldPath].thumb;
                thumb.attr('data-path', newPath);
                thumb.find('div')
                    .text(newPath.substring(1));
                this.sessions[newPath] = this.sessions[oldPath];
                this.sessions[newPath].path = newPath;
                this.sessions[oldPath] = undefined;
            };
            if (this.sessions[oldPath]) {
                // A file was renamed
                switchSessions.apply(this, [oldPath, newPath]);
                // pass new sessions instance to setactive
                for (var k = 0; k < codiad.editor.instances.length; k++) {
                    if (codiad.editor.instances[k].getSession().path === newPath) {
                        codiad.editor.setActive(codiad.editor.instances[k]);
                    }
                }

                var newSession = this.sessions[newPath];

                // Change Editor Mode
                var ext = codiad.filemanager.getExtension(newPath);
                var mode = codiad.editor.selectMode(ext);

                // handle async mode change
                var fn = function(){
                   codiad.editor.setModeDisplay(newSession);
                   newSession.removeListener('changeMode', fn);                   
                }

                newSession.on("changeMode", fn);
                newSession.setMode("ace/mode/" + mode);

            } else {
                // A folder was renamed
                var newKey;
                for (var key in this.sessions) {
                    newKey = key.replace(oldPath, newPath);
                    if (newKey !== key) {
                        switchSessions.apply(this, [key, newKey]);
                    }
                }
            }
            $.get(this.controller + '?action=rename&old_path=' + oldPath + '&new_path=' + newPath);
        },

        //////////////////////////////////////////////////////////////////
        // Open in Browser
        //////////////////////////////////////////////////////////////////

        openInBrowser: function() {
            var path = this.getPath();
            if (path) {
                codiad.filemanager.openInBrowser(path);
            } else {
                codiad.message.error('No Open Files');
            }
        },

        //////////////////////////////////////////////////////////////////
        // Get Selected Text
        //////////////////////////////////////////////////////////////////

        getSelectedText: function() {
            var path = this.getPath();
            var session = this.sessions[path];

            if (path && this.isOpen(path)) {
                return session.getTextRange(
                codiad.editor.getActive()
                    .getSelectionRange());
            } else {
                codiad.message.error('No Open Files or Selected Text');
            }
        },

        //////////////////////////////////////////////////////////////////
        // Insert Text
        //////////////////////////////////////////////////////////////////

        insertText: function(val) {
            codiad.editor.getActive()
                .insert(val);
        },

        //////////////////////////////////////////////////////////////////
        // Goto Line
        //////////////////////////////////////////////////////////////////

        gotoLine: function(line) {
            codiad.editor.getActive()
                .gotoLine(line, 0, true);
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
                    newActive = $('#active-files li a.active')
                        .parent('li')
                        .prev('li')
                        .children('a')
                        .attr('data-path');
                    if (!newActive) {
                        newActive = $('#active-files li:last-child a')
                            .attr('data-path');
                    }

                } else {
                    // Move down or rotate to top
                    newActive = $('#active-files li a.active')
                        .parent('li')
                        .next('li')
                        .children('a')
                        .attr('data-path');
                    if (!newActive) {
                        newActive = $('#active-files li:first-child a')
                            .attr('data-path');
                    }

                }

                this.focus(newActive);
            }

        }

    };

})(this, jQuery);
