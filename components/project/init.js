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
                        .append('<ul><li><a id="project-root" data-type="root" class="directory" data-path="/' + projectInfo.path + '">' + projectInfo.name + '</a></li></ul>');
                    codiad.filemanager.index('/' + projectInfo.path);
                    codiad.user.project(projectInfo.path);
                    codiad.message.success('Project Loaded');
                }
            });
        },

        //////////////////////////////////////////////////////////////////
        // Open Project
        //////////////////////////////////////////////////////////////////

        open: function(path) {
            var _this = this;
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
        // Create Project
        //////////////////////////////////////////////////////////////////

        create: function() {
            var _this = this;
            codiad.modal.load(500, this.dialog + '?action=create');
            $('#modal-content form')
                .live('submit', function(e) {
                e.preventDefault();
                var projectName = $('#modal-content form input[name="project_name"]')
                    .val();
                $.get(_this.controller + '?action=create&project_name=' + projectName, function(data) {
                    createResponse = codiad.jsend.parse(data);
                    if (createResponse != 'error') {
                        _this.open(createResponse.path);
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
                        $.get(codiad.filemanager.controller + '?action=delete&path=' + projectPath);
                        _this.list();
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
        // Get Current (Path)
        //////////////////////////////////////////////////////////////////

        getCurrent: function() {
            $.get(this.controller + '?action=current', function(data) {
                currentResponse = codiad.jsend.parse(data);
                if (currentResponse != 'error') {
                    return currentResponse;
                }
            });
        }
    };
})(this, jQuery);
