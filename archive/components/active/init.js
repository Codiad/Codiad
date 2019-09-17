/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function(global, $) {

    var EditSession = ace.require('ace/edit_session')
        .EditSession;
    var UndoManager = ace.require('ace/undomanager')
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
        
        // History of opened files
        history: [],

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

        open: function(path, content, mtime, inBackground, focus) {
            if (focus === undefined) {
                focus = true;
            }
            
            var _this = this;
            
            if (this.isOpen(path)) {
                if(focus) this.focus(path);
                return;
            }
            var ext = codiad.filemanager.getExtension(path);
            var mode = codiad.editor.selectMode(ext);

            var fn = function() {
                //var Mode = require('ace/mode/' + mode)
                //    .Mode;

                // TODO: Ask for user confirmation before recovering
                // And maybe show a diff
                var draft = _this.checkDraft(path);
                if (draft) {
                    content = draft;
                    codiad.message.success(i18n('Recovered unsaved content for: ') + path);
                }

                //var session = new EditSession(content, new Mode());
                var session = new EditSession(content);
                session.setMode("ace/mode/" + mode);
                session.setUndoManager(new UndoManager());

                session.path = path;
                session.serverMTime = mtime;
                _this.sessions[path] = session;
                session.untainted = content.slice(0);
                if (!inBackground && focus) {
                    codiad.editor.setSession(session);
                }
                _this.add(path, session, focus);
                /* Notify listeners. */
                amplify.publish('active.onOpen', path);
            };

            // Assuming the mode file has no dependencies
            $.loadScript('components/editor/ace-editor/mode-' + mode + '.js',
            fn);
        },

        init: function() {

            var _this = this;

            _this.initTabDropdownMenu();
            _this.updateTabDropdownVisibility();

            // Focus from list.
            $('#list-active-files a')
                .live('click', function(e) {
                    e.stopPropagation();
                    _this.focus($(this).parent('li').attr('data-path'));
            });

            // Focus on left button click from dropdown.
            $('#dropdown-list-active-files a')
                .live('click', function(e) {
                    if(e.which == 1) {
                        /* Do not stop propagation of the event,
                         * it will be catch by the dropdown menu
                         * and close it. */
                        _this.focus($(this).parent('li').attr('data-path'));
                    }
            });

            // Focus on left button mousedown from tab.
            $('#tab-list-active-files li.tab-item>a.label')
                .live('mousedown', function(e) {
                    if(e.which == 1) {
                        e.stopPropagation();
                        _this.focus($(this).parent('li').attr('data-path'));
                    }
            });

            // Remove from list.
            $('#list-active-files a>span')
                .live('click', function(e) {
                e.stopPropagation();
                _this.remove($(this)
                    .parent('a')
                    .parent('li')
                    .attr('data-path'));
            });

            // Remove from dropdown.
            $('#dropdown-list-active-files a>span')
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
            $('#tab-list-active-files a.close')
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

            // Remove from middle button click on dropdown.
            $('#dropdown-list-active-files li')
                .live('mouseup', function(e) {
                    if (e.which == 2) {
                        e.stopPropagation();
                        /* Get the active editor before removing anything. Remove the
                         * tab, then put back the focus on the previously active
                         * editor if it was not removed. */
                        var activePath = _this.getPath();
                        var pathToRemove = $(this).attr('data-path');
                        _this.remove(pathToRemove);
                        if (activePath !== null && activePath !== pathToRemove) {
                            _this.focus(activePath);
                        }
                        _this.updateTabDropdownVisibility();
                    }
            });

            // Remove from middle button click on tab.
            $('.tab-item')
                .live('mouseup', function(e) {
                    if (e.which == 2) {
                        e.stopPropagation();
                        /* Get the active editor before removing anything. Remove the
                         * tab, then put back the focus on the previously active
                         * editor if it was not removed. */
                        var activePath = _this.getPath();
                        var pathToRemove = $(this).attr('data-path');
                        _this.remove(pathToRemove);
                        if (activePath !== null && activePath !== pathToRemove) {
                            _this.focus(activePath);
                        }
                        _this.updateTabDropdownVisibility();
                    }
            });

            // Make list sortable
            $('#list-active-files')
                .sortable({
                placeholder: 'active-sort-placeholder',
                tolerance: 'intersect',
                start: function(e, ui) {
                    ui.placeholder.height(ui.item.height());
                }
            });

            // Make dropdown sortable.
            $('#dropdown-list-active-files')
                .sortable({
                axis: 'y',
                tolerance: 'pointer',
                start: function(e, ui) {
                    ui.placeholder.height(ui.item.height());
                }
            });

            // Make tabs sortable.
            $('#tab-list-active-files')
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
            $('#tab-list-active-files').data('sortable').floating = true;

            // Open saved-state active files on load
            $.get(_this.controller + '?action=list', function(data) {
                var listResponse = codiad.jsend.parse(data);
                if (listResponse !== null) {
                    $.each(listResponse, function(index, data) {
                        codiad.filemanager.openFile(data.path, data.focused);
                    });
                }
            });

            // Prompt if a user tries to close window without saving all filess
            window.onbeforeunload = function(e) {
                if ($('#list-active-files li.changed')
                    .length > 0) {
                    var e = e || window.event;
                    var errMsg = i18n('You have unsaved files.');

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
            $.get(this.controller + '?action=check&path=' + encodeURIComponent(path),

            function(data) {
                var checkResponse = codiad.jsend.parse(data);
            });
        },

        //////////////////////////////////////////////////////////////////
        // Add newly opened file to list
        //////////////////////////////////////////////////////////////////

        add: function(path, session, focus) {
            if (focus === undefined) {
                focus = true;
            }

            var listThumb = this.createListThumb(path);
            session.listThumb = listThumb;
            $('#list-active-files').append(listThumb);

            /* If the tab list would overflow with the new tab. Move the
             * first tab to dropdown, then add a new tab. */
            if (this.isTabListOverflowed(true)) {
                var tab = $('#tab-list-active-files li:first-child');
                this.moveTabToDropdownMenu(tab);
            }

            var tabThumb = this.createTabThumb(path);
            $('#tab-list-active-files').append(tabThumb);
            session.tabThumb = tabThumb;

            this.updateTabDropdownVisibility();

            $.get(this.controller + '?action=add&path=' + encodeURIComponent(path));

            if(focus) {
                this.focus(path);
            }
            
            // Mark draft as changed
            if (this.checkDraft(path)) {
                this.markChanged(path);
            }
        },

        //////////////////////////////////////////////////////////////////
        // Focus on opened file
        //////////////////////////////////////////////////////////////////

        focus: function(path, moveToTabList) {
            if (moveToTabList === undefined) {
                moveToTabList = true;
            }
            
            this.highlightEntry(path, moveToTabList);
            
            if(path != this.getPath()) {
                codiad.editor.setSession(this.sessions[path]);
                this.history.push(path);
                $.get(this.controller, {'action':'focused', 'path':path});
            }
            
            /* Check for users registered on the file. */
            this.check(path);

            /* Notify listeners. */
            amplify.publish('active.onFocus', path);
        },

        highlightEntry: function(path, moveToTabList) {
            if (moveToTabList === undefined) {
                moveToTabList = true;
            }
            
            $('#list-active-files li')
                .removeClass('active');

            $('#tab-list-active-files li')
                .removeClass('active');

            $('#dropdown-list-active-files li')
                .removeClass('active');

            var session = this.sessions[path];

            if($('#dropdown-list-active-files').has(session.tabThumb).length > 0) {
                if(moveToTabList) {
                     /* Get the menu item as a tab, and put the last tab in
                     * dropdown. */
                    var menuItem = session.tabThumb;
                    this.moveDropdownMenuItemToTab(menuItem, true);
    
                    var tab = $('#tab-list-active-files li:last-child');
                    this.moveTabToDropdownMenu(tab);
                } else {
                    /* Show the dropdown menu if needed */
                    this.showTabDropdownMenu();
                }
            }
            else if(this.history.length > 0) {
                var prevPath = this.history[this.history.length-1];
                var prevSession = this.sessions[prevPath];
                if($('#dropdown-list-active-files').has(prevSession.tabThumb).length > 0) {
                    /* Hide the dropdown menu if needed */
                    this.hideTabDropdownMenu();
                }
            }

            session.tabThumb.addClass('active');
            session.listThumb.addClass('active');
        },

        //////////////////////////////////////////////////////////////////
        // Mark changed
        //////////////////////////////////////////////////////////////////

        markChanged: function(path) {
            this.sessions[path].listThumb.addClass('changed');
            this.sessions[path].tabThumb.addClass('changed');
        },

        //////////////////////////////////////////////////////////////////
        // Save active editor
        //////////////////////////////////////////////////////////////////

        save: function(path) {
            /* Notify listeners. */
            amplify.publish('active.onSave', path);

            var _this = this;
            if ((path && !this.isOpen(path)) || (!path && !codiad.editor.getActive())) {
                codiad.message.error(i18n('No Open Files to save'));
                return;
            }
            var session;
            if (path) session = this.sessions[path];
            else session = codiad.editor.getActive()
                .getSession();
            var content = session.getValue();
            var path = session.path;
            var handleSuccess = function(mtime){
                var session = codiad.active.sessions[path];
                if(typeof session != 'undefined') {
                    session.untainted = newContent;
                    session.serverMTime = mtime;
                    if (session.listThumb) session.listThumb.removeClass('changed');
                    if (session.tabThumb) session.tabThumb.removeClass('changed');
                }
                _this.removeDraft(path);
            }
            // Replicate the current content so as to avoid
            // discrepancies due to content changes during
            // computation of diff

            var newContent = content.slice(0);
            if (session.serverMTime && session.untainted){
                codiad.workerManager.addTask({
                    taskType: 'diff',
                    id: path,
                    original: session.untainted,
                    changed: newContent
                }, function(success, patch){
                    if (success) {
                        codiad.filemanager.savePatch(path, patch, session.serverMTime, {
                            success: handleSuccess
                        });
                    } else {
                        codiad.filemanager.saveFile(path, newContent, {
                            success: handleSuccess
                        });
                    }
                }, this);
            } else {
                codiad.filemanager.saveFile(path, newContent, {
                    success: handleSuccess
                });
            }
        },
        
        //////////////////////////////////////////////////////////////////
        // Save all files
        //////////////////////////////////////////////////////////////////
        
        saveAll: function() {
            var _this = this;
            for(var session in _this.sessions) {
                if (_this.sessions[session].listThumb.hasClass('changed')) {
                    codiad.active.save(session);
                }
            }
        },

        //////////////////////////////////////////////////////////////////
        // Remove file
        //////////////////////////////////////////////////////////////////

        remove: function(path) {
            if (!this.isOpen(path)) return;
            var session = this.sessions[path];
            var closeFile = true;
            if (session.listThumb.hasClass('changed')) {
                codiad.modal.load(450, 'components/active/dialog.php?action=confirm&path=' + encodeURIComponent(path));
                closeFile = false;
            }
            if (closeFile) {
                this.close(path);
            }
        },
        
        removeAll: function(discard) {
            discard = discard || false;
            /* Notify listeners. */
            amplify.publish('active.onRemoveAll');

            var _this = this;
            var changed = false;
            
            var opentabs = new Array();
            for(var session in _this.sessions) {
               opentabs[session] = session;
               if (_this.sessions[session].listThumb.hasClass('changed')) {
                    changed = true;
               }
            }
            if(changed && !discard) {
                codiad.modal.load(450, 'components/active/dialog.php?action=confirmAll');
                return;
            } 
            
            for(var tab in opentabs) {
                var session = this.sessions[tab]; 

                session.tabThumb.remove();
                _this.updateTabDropdownVisibility();

                session.listThumb.remove();

                /* Remove closed path from history */
                var history = [];
                $.each(this.history, function(index) {
                    if(this != tab) history.push(this);
                })
                this.history = history
                
                delete this.sessions[tab];
                this.removeDraft(tab);
            }
            codiad.editor.exterminate();
            $('#list-active-files').html('');
            $.get(this.controller + '?action=removeall');
        },

        close: function(path) {
            /* Notify listeners. */
            amplify.publish('active.onClose', path);

            var _this = this;
            var session = this.sessions[path];

            /* Animate only if the tabThumb if a tab, not a dropdown item. */
            if(session.tabThumb.hasClass('tab-item')) {
                session.tabThumb.css({'z-index': 1});
                session.tabThumb.animate({
                    top: $('#editor-top-bar').height() + 'px'
                }, 300, function() {
                    session.tabThumb.remove();
                    _this.updateTabDropdownVisibility();
                });
            } else {
                session.tabThumb.remove();
                _this.updateTabDropdownVisibility();
            }

            session.listThumb.remove();

            /* Remove closed path from history */
            var history = [];
            $.each(this.history, function(index) {
                if(this != path) history.push(this);
            })
            this.history = history
            
            /* Select all the tab tumbs except the one which is to be removed. */
            var tabThumbs = $('#tab-list-active-files li[data-path!="' + path + '"]');

            if (tabThumbs.length == 0) {
                codiad.editor.exterminate();
            } else {
                
                var nextPath = '';
                if(this.history.length > 0) {
                    nextPath = this.history[this.history.length - 1];
                } else {
                    nextPath = $(tabThumbs[0]).attr('data-path');
                }
                var nextSession = this.sessions[nextPath];
                codiad.editor.removeSession(session, nextSession);

                this.focus(nextPath);
            }
            delete this.sessions[path];
            $.get(this.controller + '?action=remove&path=' + encodeURIComponent(path));
            this.removeDraft(path);
        },

        //////////////////////////////////////////////////////////////////
        // Process rename
        //////////////////////////////////////////////////////////////////

        rename: function(oldPath, newPath) {
            var switchSessions = function(oldPath, newPath) {
                var tabThumb = this.sessions[oldPath].tabThumb;
                tabThumb.attr('data-path', newPath);
                var title = newPath;
                if (codiad.project.isAbsPath(newPath)) {
                    title = newPath.substring(1);
                }
                tabThumb.find('.label')
                    .text(title);
                this.sessions[newPath] = this.sessions[oldPath];
                this.sessions[newPath].path = newPath;
                delete this.sessions[oldPath];
                //Rename history
                for (var i = 0; i < this.history.length; i++) {
                    if (this.history[i] === oldPath) {
                        this.history[i] = newPath;
                    }
                }
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
            $.get(this.controller + '?action=rename&old_path=' + encodeURIComponent(oldPath) + '&new_path=' + encodeURIComponent(newPath), function() {
                /* Notify listeners. */
                amplify.publish('active.onRename', {"oldPath": oldPath, "newPath": newPath});
            });
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
                codiad.message.error(i18n('No Open Files or Selected Text'));
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

            var num = $('#tab-list-active-files li').length;
            if (num === 0) return;
            
            var newActive = null;
            var active = null;
            
            if (dir == 'up') {
                
                // If active is in the tab list
                active = $('#tab-list-active-files li.active');
                if(active.length > 0) {
                    // Previous or rotate to the end
                    newActive = active.prev('li');
                    if (newActive.length === 0) {
                        newActive = $('#dropdown-list-active-files li:last-child')
                        if (newActive.length === 0) {
                            newActive = $('#tab-list-active-files li:last-child')
                        }
                    }
                }
                
                // If active is in the dropdown list
                active = $('#dropdown-list-active-files li.active');
                if(active.length > 0) {
                    // Previous
                    newActive = active.prev('li');
                    if (newActive.length === 0) {
                        newActive = $('#tab-list-active-files li:last-child')
                    }
                }

            } else {
                
                // If active is in the tab list
                active = $('#tab-list-active-files li.active');
                if(active.length > 0) {
                     // Next or rotate to the beginning
                    newActive = active.next('li');
                    if (newActive.length === 0) {
                        newActive = $('#dropdown-list-active-files li:first-child');
                        if (newActive.length === 0) {
                            newActive = $('#tab-list-active-files li:first-child')
                        }
                    }
                }
                
                // If active is in the dropdown list
                active = $('#dropdown-list-active-files li.active');
                if(active.length > 0) {
                    // Next or rotate to the beginning
                    newActive = active.next('li');
                    if (newActive.length === 0) {
                        newActive = $('#tab-list-active-files li:first-child')
                    }
                }

            }

            if(newActive) this.focus(newActive.attr('data-path'), false);
        },

        //////////////////////////////////////////////////////////////////
        // Dropdown Menu
        //////////////////////////////////////////////////////////////////

        initTabDropdownMenu: function() {
            var _this = this;
            
            var menu = $('#dropdown-list-active-files');
            var button = $('#tab-dropdown-button');
            var closebutton = $('#tab-close-button');
            
            menu.appendTo($('body'));

            button.click(function(e) {
                e.stopPropagation();
                _this.toggleTabDropdownMenu();
            });
                        
            closebutton.click(function(e) {
                e.stopPropagation();
                _this.removeAll();
            });
        },
        
        showTabDropdownMenu: function() {
            var menu = $('#dropdown-list-active-files');
            if(!menu.is(':visible')) this.toggleTabDropdownMenu();
        },
        
        hideTabDropdownMenu: function() {
            var menu = $('#dropdown-list-active-files');
            if(menu.is(':visible')) this.toggleTabDropdownMenu();
        },
        
        toggleTabDropdownMenu: function() {
            var _this = this;
            var menu = $('#dropdown-list-active-files');
            
            menu.css({
                top: $("#editor-top-bar").height() + 'px',
                right: '20px',
                width: '200px'
            });
            
            menu.slideToggle('fast');

            if(menu.is(':visible')) {
                // handle click-out autoclosing
                var fn = function() {
                    menu.hide();
                    $(window).off('click', fn)
                }
                $(window).on('click', fn);
            }
        },

        moveTabToDropdownMenu: function(tab, prepend) {
            if (prepend === undefined) {
                prepend = false;
            }

            tab.remove();
            path = tab.attr('data-path');

            var tabThumb = this.createMenuItemThumb(path);
            if(prepend) $('#dropdown-list-active-files').prepend(tabThumb);
            else $('#dropdown-list-active-files').append(tabThumb);

            if(tab.hasClass("changed")) {
                tabThumb.addClass("changed");
            }

            if(tab.hasClass("active")) {
                tabThumb.addClass("active");
            }

            this.sessions[path].tabThumb = tabThumb;
        },

        moveDropdownMenuItemToTab: function(menuItem, prepend) {
            if (prepend === undefined) {
                prepend = false;
            }

            menuItem.remove();
            path = menuItem.attr('data-path');

            var tabThumb = this.createTabThumb(path);
            if(prepend) $('#tab-list-active-files').prepend(tabThumb);
            else $('#tab-list-active-files').append(tabThumb);

            if(menuItem.hasClass("changed")) {
                tabThumb.addClass("changed");
            }

            if(menuItem.hasClass("active")) {
                tabThumb.addClass("active");
            }

            this.sessions[path].tabThumb = tabThumb;
        },

        isTabListOverflowed: function(includeFictiveTab) {
            if (includeFictiveTab === undefined) {
                includeFictiveTab = false;
            }

            var tabs = $('#tab-list-active-files li');
            var count = tabs.length
            if (includeFictiveTab) count += 1;
            if (count <= 1) return false;

            var width = 0;
            tabs.each(function(index) {
                width += $(this).outerWidth(true);
            })
            if (includeFictiveTab) {
                width += $(tabs[tabs.length-1]).outerWidth(true);
            }

            /* If we subtract the width of the left side bar, of the right side
             * bar handle and of the tab dropdown handle to the window width,
             * do we have enough room for the tab list? Its kind of complicated
             * to handle all the offsets, so afterwards we add a fixed offset
             * just t be sure. */
            var lsbarWidth = $(".sidebar-handle").width();
            if (codiad.sidebars.isLeftSidebarOpen) {
                lsbarWidth = $("#sb-left").width();
            }

            var rsbarWidth = $(".sidebar-handle").width();
            if (codiad.sidebars.isRightSidebarOpen) {
                rsbarWidth = $("#sb-right").width();
            }

            var tabListWidth = $("#tab-list-active-files").width();
            var dropdownWidth = $('#tab-dropdown').width();
            var closeWidth = $('#tab-close').width();
            var room = window.innerWidth - lsbarWidth - rsbarWidth - dropdownWidth - closeWidth - width - 30;
            return (room < 0);
        },

        updateTabDropdownVisibility: function() {
            while(this.isTabListOverflowed()) {
                var tab = $('#tab-list-active-files li:last-child');
                if (tab.length == 1) this.moveTabToDropdownMenu(tab, true);
                else break;
            }

            while(!this.isTabListOverflowed(true)) {
                var menuItem = $('#dropdown-list-active-files li:first-child');
                if (menuItem.length == 1) this.moveDropdownMenuItemToTab(menuItem);
                else break;
            }

            if ($('#dropdown-list-active-files li').length > 0) {
                $('#tab-dropdown').show();
            } else {
                $('#tab-dropdown').hide();
                // Be sure to hide the menu if it is opened.
                $('#dropdown-list-active-files').hide();
            }
            if ($('#tab-list-active-files li').length > 1) {
                $('#tab-close').show();
            } else {
                $('#tab-close').hide();
            }
        },

        //////////////////////////////////////////////////////////////////
        // Factory
        //////////////////////////////////////////////////////////////////
        
        splitDirectoryAndFileName: function(path) {
            var index = path.lastIndexOf('/');
            return {
                fileName: path.substring(index + 1),
                directory: (path.indexOf('/') == 0)? path.substring(1, index + 1):path.substring(0, index + 1)
            }
        },
        
        createListThumb: function(path) {
            return $('<li data-path="' + path + '"><a title="'+path+'"><span></span><div>' + path + '</div></a></li>');
        },

        createTabThumb: function(path) {
            split = this.splitDirectoryAndFileName(path);
            return $('<li class="tab-item" data-path="' + path + '"><a class="label" title="' + path + '">' 
                    + split.directory + '<span class="file-name">' + split.fileName + '</span>' 
                    + '</a><a class="close">x</a></li>');
        },

        createMenuItemThumb: function(path) {
            split = this.splitDirectoryAndFileName(path);
            return $('<li data-path="' + path + '"><a title="' + path + '"><span class="label"></span><div class="label">' 
                    + split.directory + '<span class="file-name">' + split.fileName + '</span>'
                    + '</div></a></li>');
        },

    };

})(this, jQuery);
