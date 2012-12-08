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

            _this.createTabDropdownMenu();
            _this.updateTabDropdownVisibility();

            // Focus from dropdown.
            $('#tab-dropdown-menu a')
                .live('click', function() {
                _this.focus($(this).parent('li').attr('data-path'));
            });

            // Focus from tab.
            $('#tab-list li.tab-item>a.label')
                .live('mousedown', function() {
                _this.focus($(this).parent('li').attr('data-path'));
            });

            // Remove from dropdown.
            $('#tab-dropdown-menu a>span')
                .live('click', function(e) {
                e.stopPropagation();
                /* Get the active editor before removing anything. Remove the
                 * tab, then put back the focus on the previously active
                 * editor if it was not removed. */
                var activePath = _this.getPath();
                var pathToRemove = $(this).parents('li').attr('data-path');
                _this.remove(pathToRemove);
                if (activePath !== null && activePath !== pathToRemove) {
                    _this.focus(activePath);
                }
                _this.updateTabDropdownVisibility();
            });

            // Remove from tab.
            $('#tab-list a.close')
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
                _this.updateTabDropdownVisibility();
            });

            // Make dropdown sortable.
            $('#tab-dropdown-menu')
                .sortable({
                placeholder: 'active-sort-placeholder',
                tolerance: 'pointer',
                start: function(e, ui) {
                    ui.placeholder.height(ui.item.height());
                }
            });

            // Make tabs sortable.
            $('#tab-list')
                .sortable({
                items: '> li',
                axis: 'x',
                tolerance: 'pointer',
                containment: 'parent',
                start: function(e, ui) {
                    ui.placeholder.css('background', 'transparent');
                    ui.helper.css('width', '200px');
                },
                stop: function(e, ui) {
                    // Reset css
                    ui.item.css('z-index', '')
                    ui.item.css('position', '')
                }
            });
            /* Woaw, so tricky! At initialization, the tab-list is empty, so
             * it is not marked as float so it is not detected as an horizontal
             * list by the sortable plugin. Workaround is to mark it as
             * floating at initialization time. See bug report
             * http://bugs.jqueryui.com/ticket/6702. */
            $('#tab-list').data('sortable').floating = true;

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
            $(window).resize(function() {
                codiad.editor.resize();
                _this.updateTabDropdownVisibility();
            });
            
            // FIXME : Run resize on editor-region h-resize
            //$('#editor-region').bind('h-resize', function() {
            //    _this.updateTabDropdownVisibility();
            //});

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
            /* If the tab list would overflow with the new tab. Move the
             * first tab to dropdown, then add a new tab. */
            if (this.isTabListOverflowed(true)) {
                var tab = $('#tab-list li:first-child');
                this.moveTabToDropdownMenu(tab);
            }

            var thumb = this.createTabThumb(path);
            $('#tab-list').append(thumb);
            session.thumb = thumb;

            this.updateTabDropdownVisibility();

            $.get(this.controller + '?action=add&path=' + path);

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

        highlightEntry: function(path) {
            
            $('#tab-list li')
                .removeClass('active');
                
            var session = this.sessions[path];
            
            if($('#tab-dropdown-menu').has(session.thumb).length > 0) {
                 /* Get the menu item as a tab, and put the last tab in
                 * dropdown. */
                var menuItem = session.thumb;
                this.moveDropdownMenuItemToTab(menuItem, true);
    
                var tab = $('#tab-list li:last-child');
                this.moveTabToDropdownMenu(tab);
            }
                           
            
            session.thumb.addClass('active');
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
            session.thumb.remove();
            var nextThumb = $('#tab-list li[data-path]');
            if (nextThumb.length == 0) {
                codiad.editor.exterminate();
            } else {
                $(nextThumb[0])
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
                thumb.find('.label')
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
                var fn = function() {
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

        },

        //////////////////////////////////////////////////////////////////
        // Dropdown Menu
        //////////////////////////////////////////////////////////////////

        initMenuHandler: function(button, menu) {
            var _this = this;
            var thisButton = button;
            var thisMenu = menu;

            thisMenu.appendTo($('body'));

            thisButton.click(function(e) {
                var wh = $(window).height();

                e.stopPropagation();

                thisMenu.css({
                    top: $("#editor-top-bar").height() + 'px',
                    right: '20px',
                    width: '200px'
                });

                thisMenu.slideToggle('fast');

                // handle click-out autoclosing
                var fn = function() {
                    thisMenu.hide();
                    $(window).off('click', fn)
                }
                $(window).on('click', fn);
            });
        },

        createTabDropdownMenu: function() {
            var _tabMenu = $('#tab-dropdown-menu');

            this.initMenuHandler($('#tab-dropdown-button'), _tabMenu);
        },

        moveTabToDropdownMenu: function(tab) {        
            tab.remove();
            path = tab.attr('data-path');

            var thumb = this.createMenuItemThumb(path);
            $('#tab-dropdown-menu').append(thumb);
            
            if(tab.hasClass("changed")) {
                thumb.addClass("changed");
            }
            
            this.sessions[path].thumb = thumb;
        },

        moveDropdownMenuItemToTab: function(menuItem, prepend) {
            if (typeof prepend == 'undefined') {
                prepend = false;
            }
            
            menuItem.remove();
            path = menuItem.attr('data-path');

            var thumb = this.createTabThumb(path);
            if(prepend) $('#tab-list').prepend(thumb);
            else $('#tab-list').append(thumb);

            if(menuItem.hasClass("changed")) {
                thumb.addClass("changed");
            }
            
            this.sessions[path].thumb = thumb;
        },

        isTabListOverflowed: function(includeFictiveTab) {
            if (typeof includeFictiveTab == 'undefined') {
                includeFictiveTab = false;
            }

            var tab = $('#tab-list li');
            if (tab.length <= 1) return false;
            var count = tab.length;

            if (includeFictiveTab) count += 1;

            return ($('#tab-list').position().left + count * tab.outerWidth() >= $('#tab-list').width() - 300);
        },

        updateTabDropdownVisibility: function() {
            while(this.isTabListOverflowed()) {
                var tab = $('#tab-list li:last-child');
                if (tab.length == 1) this.moveTabToDropdownMenu(tab);
                else break;
            }
            
            while(!this.isTabListOverflowed(true)) {
                var menuItem = $('#tab-dropdown-menu li:first-child');
                if (menuItem.length == 1) this.moveDropdownMenuItemToTab(menuItem);
                else break;
            }
            
            if ($('#tab-dropdown-menu li').length > 0) {
                $('#tab-dropdown').show();
            } else {
                $('#tab-dropdown').hide();
                // Be sure to hide the menu if it is opened.
                $('#tab-dropdown-menu').hide();
            }
        },

        //////////////////////////////////////////////////////////////////
        // Factory
        //////////////////////////////////////////////////////////////////

        createTabThumb: function(path) {
            return $('<li class="tab-item" data-path="' + path + '"><a class="label" title="' + path + '">' + path.substring(1) + '</a><a class="close">x</a></li>');
        },

        createMenuItemThumb: function(path) {
            return $('<li data-path="' + path + '"><a title="' + path + '"><span></span><div class="label">' + path.substring(1) + '</div></a></li>');
        },

    };

})(this, jQuery);
