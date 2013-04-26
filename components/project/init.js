/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function(global, $){

    var codiad = global.codiad;

    $(function() {
        codiad.project.init();
    });

    codiad.project = {

        controller: 'components/project/controller.php',
        dialog: 'components/project/dialog.php',

        init: function() {
            this.loadCurrent();
            this.loadSide();
            
            var _this = this;
            
            $('#projects-create').click(function(){
                codiad.project.create('true');
            });
            
            $('#projects-collapse').click(function(){
                if (!_this._sideExpanded) {
                    _this.projectsExpand();
                } else {
                    _this.projectsCollapse();
                }
            });
        },

        //////////////////////////////////////////////////////////////////
        // Get Current Project
        //////////////////////////////////////////////////////////////////

        loadCurrent: function() {
            $.get(this.controller + '?action=get_current', function(data) {
                var projectInfo = codiad.jsend.parse(data);
                if (projectInfo != 'error') {
                    $('#file-manager')
                        .html('')
                        .append('<ul><li><a id="project-root" data-type="root" class="directory" data-path="' + projectInfo.path + '">' + projectInfo.name + '</a></li></ul>');
                    codiad.filemanager.index(projectInfo.path);
                    codiad.user.project(projectInfo.path);
                    codiad.message.success(i18n('Project ' + projectInfo.name + ' Loaded'));
                }
            });
        },

        //////////////////////////////////////////////////////////////////
        // Open Project
        //////////////////////////////////////////////////////////////////

        open: function(path) {
            var _this = this;
            codiad.finder.contractFinder();
            $.get(this.controller + '?action=open&path=' + path, function(data) {
                var projectInfo = codiad.jsend.parse(data);
                if (projectInfo != 'error') {
                    _this.loadCurrent();
                    codiad.modal.unload();
                    codiad.user.project(path);
                }
            });
        },

        //////////////////////////////////////////////////////////////////
        // Open the project manager dialog
        //////////////////////////////////////////////////////////////////

        list: function() {
            $('#modal-content form')
                .die('submit'); // Prevent form bubbling
            codiad.modal.load(500, this.dialog + '?action=list');
        },
        
        //////////////////////////////////////////////////////////////////
        // Load and list projects in the sidebar.
        //////////////////////////////////////////////////////////////////
        loadSide: function() {
            $('.sb-projects-content').load(this.dialog + '?action=sidelist');
            this._sideExpanded = true;
        },
        
        projectsExpand: function() {
            this._sideExpanded = true;
            $('#side-projects').css('height', 276+'px');
            $('.project-list-title').css('right', 0);
            $('.sb-left-content').css('bottom', 276+'px');
            $('#projects-collapse')
                .removeClass('icon-up-dir')
                .addClass('icon-down-dir');
        },
        
        projectsCollapse: function() {
            this._sideExpanded = false;
            $('#side-projects').css('height', 33+'px');
            $('.project-list-title').css('right', 0);
            $('.sb-left-content').css('bottom', 33+'px');
            $('#projects-collapse')
                .removeClass('icon-down-dir')
                .addClass('icon-up-dir');
        },
        
        //////////////////////////////////////////////////////////////////
        // Open the project manager dialog
        //////////////////////////////////////////////////////////////////

        list: function() {
            $('#modal-content form')
                .die('submit'); // Prevent form bubbling
            codiad.modal.load(500, this.dialog + '?action=list');
        },

        //////////////////////////////////////////////////////////////////
        // Create Project
        //////////////////////////////////////////////////////////////////

        create: function(close) {
            var _this = this;
            create = true;
            codiad.modal.load(500, this.dialog + '?action=create&close=' + close);
            $('#modal-content form')
                .live('submit', function(e) {
                e.preventDefault();
                var projectName = $('#modal-content form input[name="project_name"]')
                    .val(),
                    projectPath = $('#modal-content form input[name="project_path"]')
                    .val(),
                    gitRepo = $('#modal-content form input[name="git_repo"]')
                    .val(),
                    gitBranch = $('#modal-content form input[name="git_branch"]')
                    .val();
                    if(projectPath.indexOf('/') == 0) {
                        create = confirm('Do you really want to create project with absolute path "' + projectPath + '"?');
                    }
                if(create) {    
                    $.get(_this.controller + '?action=create&project_name=' + projectName + '&project_path=' + projectPath + '&git_repo=' + gitRepo + '&git_branch=' + gitBranch, function(data) {
                        createResponse = codiad.jsend.parse(data);
                        if (createResponse != 'error') {
                            _this.open(createResponse.path);
                            codiad.modal.unload();
                            _this.loadSide();
                        }
                    });
                }
            });
        },
        
        //////////////////////////////////////////////////////////////////
        // Rename Project
        //////////////////////////////////////////////////////////////////

        rename: function(path) {
            var _this = this;
            codiad.modal.load(500, this.dialog + '?action=rename&path=' + escape(path));
            $('#modal-content form')
                .live('submit', function(e) {
                e.preventDefault();
                var projectPath = $('#modal-content form input[name="project_path"]')
                    .val();
                var projectName = $('#modal-content form input[name="project_name"]')
                    .val();    
                $.get(_this.controller + '?action=rename&project_path=' + projectPath + '&project_name=' + projectName, function(data) {
                   renameResponse = codiad.jsend.parse(data);
                    if (renameResponse != 'error') {
                        codiad.message.success(i18n('Project renamed'));
                        _this.loadSide();
                        $('#file-manager a[data-type="root"]').html(projectName);
                        codiad.modal.unload();
                    }
                });
            });
        },
        
        //////////////////////////////////////////////////////////////////
        // Delete Project
        //////////////////////////////////////////////////////////////////

        delete: function(name, path) {
            var _this = this;
            codiad.modal.load(500, this.dialog + '?action=delete&name=' + escape(name) + '&path=' + escape(path));
            $('#modal-content form')
                .live('submit', function(e) {
                e.preventDefault();
                var projectPath = $('#modal-content form input[name="project_path"]')
                    .val();
                $.get(_this.controller + '?action=delete&project_path=' + projectPath, function(data) {
                    deleteResponse = codiad.jsend.parse(data);
                    if (deleteResponse != 'error') {
                        codiad.message.success('Project Deleted');
                        var deletefiles = $('input:checkbox[name="delete"]:checked').val();
                        var followlinks = $('input:checkbox[name="follow"]:checked').val();
                        if( typeof deletefiles !== 'undefined' ) {
                            if( typeof followlinks !== 'undefined' ) {
                                $.get(codiad.filemanager.controller + '?action=delete&follow=true&path=' + projectPath);
                            } else {
                                $.get(codiad.filemanager.controller + '?action=delete&path=' + projectPath);
                            }
                        }
                        _this.list();
                        _this.loadSide();
                        // Remove any active files that may be open
                        $('#active-files a')
                            .each(function() {
                            var curPath = $(this)
                                .attr('data-path');
                            if (curPath.indexOf(projectPath) == 0) {
                                codiad.active.remove(curPath);
                            }
                        });
                    }
                });
            });
        },
        
        //////////////////////////////////////////////////////////////////
        // Check Absolute Path
        //////////////////////////////////////////////////////////////////
        
        isAbsPath: function(path) {
            if ( path.indexOf("/") == 0 ) {
                return true;
            } else {
                return false;
            }
        },

        //////////////////////////////////////////////////////////////////
        // Get Current (Path)
        //////////////////////////////////////////////////////////////////

        getCurrent: function() {
            var _this = this;
            var currentResponse = null;
            $.ajax({
                url: _this.controller + '?action=current',
                async: false,
                success: function(data) {
                    currentResponse = codiad.jsend.parse(data);
                } 
             });
            return currentResponse;
        }
    };
})(this, jQuery);
