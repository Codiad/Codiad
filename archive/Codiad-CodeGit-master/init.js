/*
 * Copyright (c) Codiad & Andr3as, distributed
 * as-is and without warranty under the MIT License.
 * See http://opensource.org/licenses/MIT for more information. 
 * This information must remain intact.
 */

(function(global, $){
    
    var codiad = global.codiad,
        scripts = document.getElementsByTagName('script'),
        path = scripts[scripts.length-1].src.split('?')[0],
        curpath = path.split('/').slice(0, -1).join('/')+'/';

    $(function() {
        codiad.CodeGit.init();
    });

    codiad.CodeGit = {
        
        path    : curpath,
        location: '',
        line    : 0,
        files   : [],
        network_graph : {},
        
        init: function() {
            var _this = this;
            $.getScript(this.path + "network_graph.js");
            $.getScript(this.path + "raphael.min.js");
            //Check if directories has git repo
            amplify.subscribe('filemanager.onIndex', function(obj){
                setTimeout(function(){
                    $.each(obj.files, function(i, item){
                        if (_this.basename(item.name) == '.git') {
                            $('.directory[data-path="'+_this.dirname(item.name)+'"]').addClass('repo');
                        } else if (item.type == 'directory') {
                            //Deeper inspect
                            $.getJSON(_this.path + 'controller.php?action=checkRepo&path=' + item.name, function(result){
                                if (result.status) {
                                    $('.directory[data-path="'+item.name+'"]').addClass('repo');
                                }
                            });
                        }
                    });

                    // Repo status
                    _this.showRepoStatus();

                    // clear an old poller
                    if (_this._poller) {
                      clearInterval(_this._poller);
                      delete _this._poller;
                    }
                    

                },0);
            });
            //Handle context-menu
            amplify.subscribe('context-menu.onShow', function(obj){
                var path = $(obj.e.target).attr('data-path'),
                    root = $('#project-root').attr('data-path'),
                    counter = 0;
                if ($(obj.e.target).hasClass('directory')) {
                    $('#context-menu').append('<hr class="directory-only code_git">');
                    if ($(obj.e.target).hasClass('repo')) {
                        $('#context-menu').append('<a class="directory-only code_git" onclick="codiad.CodeGit.showDialog(\'overview\', $(\'#context-menu\').attr(\'data-path\'));"><span class="icon-flow-branch"></span>Open CodeGit</a>');
                        // $('#context-menu').append('<a class="directory-only code_git" onclick="codiad.CodeGit.submoduleDialog(\'' + path + '\', $(\'#context-menu\').attr(\'data-path\'));"><span class="icon-flow-branch"></span>Add Submodule</a>');
                    } else {
                        $('#context-menu').append('<a class="directory-only code_git" onclick="codiad.CodeGit.gitInit($(\'#context-menu\').attr(\'data-path\'));"><span class="icon-flow-branch"></span>Git Init</a>');
                        $('#context-menu').append('<a class="directory-only code_git" onclick="codiad.CodeGit.clone($(\'#context-menu\').attr(\'data-path\'));"><span class="icon-flow-branch"></span>Git Clone</a>');
                        
                        //Git Submodule
                        while (path != root) {
                            path = _this.dirname(path);
                            if ($('.directory[data-path="' + path + '"]').hasClass('repo')) {
                                $('#context-menu').append('<a class="directory-only code_git" onclick="codiad.CodeGit.submoduleDialog(\'' + path + '\', $(\'#context-menu\').attr(\'data-path\'));"><span class="icon-flow-branch"></span>Add Submodule</a>');
                                break;
                            }
                            if (counter >= 10) break;
                            counter++;
                        }
                    }
                } else {
                    var file = path;
                    while (path != root) {
                        path = _this.dirname(path);
                        if ($('.directory[data-path="' + path + '"]').hasClass('repo')) {
                            $('#context-menu').append('<hr class="file-only code_git">');
                            // $('#context-menu').append('<a class="file-only code_git" onclick="codiad.CodeGit.contextMenuDiff($(\'#context-menu\').attr(\'data-path\'), \''+path+'\');"><span class="icon-flow-branch"></span>Git Diff</a>');
                            // $('#context-menu').append('<a class="file-only code_git" onclick="codiad.CodeGit.blame($(\'#context-menu\').attr(\'data-path\'), \''+path+'\');"><span class="icon-flow-branch"></span>Git Blame</a>');
                            $('#context-menu').append('<a class="file-only code_git" onclick="codiad.CodeGit.history($(\'#context-menu\').attr(\'data-path\'), \''+path+'\');"><span class="icon-flow-branch"></span>Git History</a>');
                            //Git rename
                            $('#context-menu a[onclick="codiad.filemanager.renameNode($(\'#context-menu\').attr(\'data-path\'));"]')
                                .attr("onclick", "codiad.CodeGit.rename($(\'#context-menu\').attr(\'data-path\'))");
                            //Init Submodules
                            if (_this.basename(file) == '.gitmodules') {
                                $('#context-menu').append('<a class="directory-only code_git" onclick="codiad.CodeGit.initSubmodule(\'' + _this.dirname(file) + '\', $(\'#context-menu\').attr(\'data-path\'));"><span class="icon-flow-branch"></span>Init Submodule</a>');
                            }
                            break;
                        }
                        if (counter >= 10) break;
                        counter++;
                    }
                }
            });
            amplify.subscribe("context-menu.onHide", function(){
                $('.code_git').remove();
            });
            //repo status
            $('#file-manager').before('<div id="git-repo-stat-wrapper" class="hidden">Commit Status: <span id="git-repo-stat"></span></div>');
            _this.addStatusIcon();
            // clicking on it brings up the commit box
            $("#git-repo-stat-wrapper").click(function(){
              codiad.CodeGit.showDialog('overview', codiad.project.getCurrent());
            });
            //File stats
            $('#current-file').after('<div class="divider"></div><div id="git-stat"></div>');
            amplify.subscribe('active.onFocus', function(path){
                _this.numstat(path);
                _this.repostat();
            });
            amplify.subscribe('active.onSave', function(path){
                setTimeout(function(){
                    _this.numstat(path);
                    _this.repostat();
                }, 50);
            });
            amplify.subscribe('active.onClose', function(path){
                _this.repostat();
                $('#git-stat').html("");
            });
            amplify.subscribe('active.onRemoveAll', function(){
                _this.repostat();
                $('#git-stat').html("");
            });
            amplify.subscribe('settings.changed', function(){
                _this.showRepoStatus();
            });
            amplify.subscribe('settings.loaded', function(){
                _this.showRepoStatus();
            });
            //Live features
            $('.git_area #check_all').live("click", function(e){
                if ($('.git_area #check_all').attr("checked") == "checked") {
                    $('.git_area input:checkbox').attr("checked", "checked");
                } else {
                    $('.git_area input:checkbox').removeAttr("checked");
                }
            });
            $('.git_area input:checkbox:not(#check_all)').live("click", function(e){
                if ($(this).attr("checked") != "checked") {
                    //One gets unchecked, remove all_input checking
                    if ($('.git_area #check_all').attr("checked") == "checked") {
                        $('.git_area #check_all').removeAttr("checked");
                    }
                } else {
                    var all = true;
                    $('.git_area input:checkbox:not(#check_all)').each(function(i, item){
                        all = all && ($(this).attr("checked") == "checked");
                    });
                    if (all) {
                        $('.git_area #check_all').attr("checked", "checked");
                    }
                }
            });
            $('.commit_hash').live('click', function(){
                var commit;
                if (typeof($(this).attr('data-hash')) != 'undefined') {
                    commit = $(this).attr('data-hash');
                } else {
                    commit = $(this).text();
                }
                commit = commit.replace("commit", "").trim();
                codiad.CodeGit.showCommit(codiad.CodeGit.location, commit);
            });
            //Button Click listener
            $('.git_area .git_diff').live("click", function(e){
                e.preventDefault();
                var line = $(this).attr('data-line');
                var path = $('.git_area .git_list .file[data-line="'+line+'"]').text();
                _this.files = [];
                _this.files.push(path);
                _this.showDialog('diff', _this.location);
            });
            $('.git_area .git_undo').live("click", function(e){
                e.preventDefault();
                var line = $(this).attr('data-line');
                var path = $('.git_area .git_list .file[data-line="'+line+'"]').text();
                _this.checkout(path, _this.location);
                _this.showDialog('overview', _this.location);
            });
            $('.git_diff_area .git_undo').live("click", function(e){
                e.preventDefault();
                _this.checkout(_this.files[0], _this.location);
                _this.showDialog('overview', _this.location);
            });
        },

        //Check if directories has git repo
        showRepoStatus: function () {
            var _this = this;
            if ($('#project-root').hasClass('repo') && _this.isEnabledRepoStatus()) {
                // add a poller
                _this._poller = setInterval(function(){
                    _this.repostat();
                }, 10000);
                _this.addStatusIcon();
                // only show stat-wrapper if not configured
                if (_this.isEnabledWrapper()) {
                    $("#git-repo-stat-wrapper").show();
                } else {
                    $("#git-repo-stat-wrapper").hide();
                }
                $("#git-repo-status-icon").show();
                _this.repostat();
            } else {
                $("#git-repo-stat-wrapper").hide();
                $("#git-repo-status-icon").hide();
            }
        },

        
        showSidebarDialog: function() {
            if (!$('#project-root').hasClass('repo')) {
                codiad.message.error('Project root has no repository. Use the context menu!');
                return;
            }
            codiad.CodeGit.showDialog('overview', $('#project-root').attr('data-path'));
        },
        
        showDialog: function(type, path) {
            this.location = path || this.location;
            codiad.modal.load(600, this.path + 'dialog.php?action=' + type);
        },
        
        showCommitDialog: function(path) {
            var _this = this;
            path      = _this.getPath(path);
            $.getJSON(this.path + 'controller.php?action=getSettings&path=' + path, function(data){
                if (data.status == "success") {
                    if (data.data.email === ""){
                        codiad.message.notice("Please tell git who you are:");
                        _this.showDialog('userConfig', _this.location);
                    } else {
                        var files = [], line = 0, file = "";
                        $('.git_area .git_list input:checkbox[checked="checked"]').each(function(i, item){
                            line = $(item).attr('data-line');
                            file = $('.git_area .git_list .file[data-line="'+line+'"]').text();
                            files.push(file);
                        });
                        _this.files = files;
                        _this.showDialog('commit', _this.location);
                    }
                } else {
                    codiad.message.error(data.message);
                }
            });
        },
        
        gitInit: function(path) {
            $.getJSON(this.path + 'controller.php?action=init&path=' + path, function(result){
                codiad.message[result.status](result.message);
                if (result.status == 'success') {
                    $('.directory[data-path="'+path+'"]').addClass('hasRepo');
                    codiad.filemanager.rescan(path);
                }
            });
        },
        
        /**
         * Clone repo or show dialog to clone repo
         * 
         * @param {string} path
         * @param {string} repo
         * @param {boolean} init_submodules
         */
        clone: function(path, repo, init_submodules) {
            var _this = this;
            init_submodules = init_submodules || "false";
            if (typeof(repo) == 'undefined') {
                this.showDialog('clone', path);
            } else {
                codiad.modal.unload();
                $.getJSON(_this.path + 'controller.php?action=clone&path=' + path + '&repo=' + repo + '&init_submodules=' + init_submodules, function(result){
                    if (result.status == 'login_required') {
                        codiad.message.error(result.message);
                        _this.showDialog('login', _this.location);
                        _this.login = function(){
                            var username = $('.git_login_area #username').val();
                            var password = $('.git_login_area #password').val();
                            codiad.modal.unload();
                            $.post(_this.path + 'controller.php?action=clone&path='+path+'&repo=' + repo + '&init_submodules=' + init_submodules, {username: username, password: password},
                                function(result){
                                    result = JSON.parse(result);
                                    codiad.message[result.status](result.message);
                                    if (result.status == 'success') {
                                        codiad.filemanager.rescan(path);
                                    }
                                });
                        };
                    } else {
                        codiad.message[result.status](result.message);
                    }
                    if (result.status == 'success') {
                        codiad.filemanager.rescan(path);
                    }
                });
            }
        },
        
        diff: function(path, repo) {
            var _this   = this;
            repo        = this.getPath(repo);
            $.getJSON(this.path + 'controller.php?action=diff&repo=' + repo + '&path=' + path, function(result){
                if (result.status != 'success') {
                    codiad.message[result.status](result.message);
                    _this.showDialog('overview', repo);
                    return;
                }
                result.data = _this.renderDiff(result.data);
                $('.git_diff').append(result.data.join(""));
            });
        },
        
        contextMenuDiff: function(path, repo) {
            this.location   = repo;
            path            = path.replace(repo + "/", "");
            this.files      = [];
            this.files.push(path);
            this.showDialog('diff', repo);
        },
        
        commit: function(path, msg) {
            var _this = this;
            path = this.getPath(path);
            var message = $('.git_commit_area #commit_msg').val();
            this.showDialog('overview', this.location);
            $.post(this.path + 'controller.php?action=add&path=' + path, {files : JSON.stringify(_this.files)}, function(result){
                result = JSON.parse(result);
                if (result.status == 'error') {
                    codiad.message.error(result.message);
                    return;
                }
                $.post(_this.path + 'controller.php?action=commit&path=' + path, {message: message}, function(result){
                    result = JSON.parse(result);
                    codiad.message[result.status](result.message);
                    _this.status(path);
                });
            });
        },
        
        filesDiff: function() {
            var _this = this;
            $.each(this.files, function(i, item){
                _this.diff(item, _this.location);
            });
        },
        
        push: function() {
            var _this   = this;
            var remote  = $('.git_push_area #git_remotes').val();
            var branch  = $('.git_push_area #git_branches').val();
            this.showDialog('overview', this.location);
            $.getJSON(this.path + 'controller.php?action=push&path=' + this.location + '&remote=' + remote + '&branch=' + branch, function(result){
                if (result.status == 'login_required') {
                    codiad.message.error(result.message);
                    _this.showDialog('login', _this.location);
                    _this.login = function(){
                        var username = $('.git_login_area #username').val();
                        var password = $('.git_login_area #password').val();
                        _this.showDialog('overview', _this.location);
                        $.post(_this.path + 'controller.php?action=push&path=' + _this.location + '&remote=' + remote + '&branch=' + branch,
                            {username: username, password: password}, function(result){
                                result = JSON.parse(result);
                                codiad.message[result.status](result.message);
                            });
                    };
                } else if (result.status == 'passphrase_required') {
                    codiad.message.error(result.message);
                    _this.showDialog('passphrase', _this.location);
                    _this.login = function() {
                        var passphrase = $('.git_login_area #passphrase').val();
                        _this.showDialog('overview', _this.location);
                        $.post(_this.path + 'controller.php?action=push&path=' + _this.location + '&remote=' + remote + '&branch=' + branch,
                            {passphrase: passphrase}, function(result){
                                result = JSON.parse(result);
                                codiad.message[result.status](result.message);
                            });
                    };
                } else {
                    codiad.message[result.status](result.message);
                }
            });
        },
        
        pull: function() {
            var _this = this;
            var remote  = $('.git_push_area #git_remotes').val();
            var branch  = $('.git_push_area #git_branches').val();
            this.showDialog('overview', this.location);
            $.getJSON(this.path + 'controller.php?action=pull&path=' + this.location + '&remote=' + remote + '&branch=' + branch, function(result){
                if (result.status == 'login_required') {
                    codiad.message.error(result.message);
                    _this.showDialog('login', _this.location);
                    _this.login = function(){
                        var username = $('.git_login_area #username').val();
                        var password = $('.git_login_area #password').val();
                        _this.showDialog('overview', _this.location);
                        $.post(_this.path + 'controller.php?action=pull&path=' + _this.location + '&remote=' + remote + '&branch=' + branch,
                            {username: username, password: password}, function(result){
                                result = JSON.parse(result);
                                codiad.message[result.status](result.message);
                            });
                    };
                } else if (result.status == 'passphrase_required') {
                    codiad.message.error(result.message);
                    _this.showDialog('passphrase', _this.location);
                    _this.login = function() {
                        var passphrase = $('.git_login_area #passphrase').val();
                        _this.showDialog('overview', _this.location);
                        $.post(_this.path + 'controller.php?action=pull&path=' + _this.location + '&remote=' + remote + '&branch=' + branch,
                            {passphrase: passphrase}, function(result){
                                result = JSON.parse(result);
                                codiad.message[result.status](result.message);
                            });
                    };
                } else {
                    codiad.message[result.status](result.message);
                }
            });
        },
        
        fetch: function() {
            var _this = this;
            var remote = $('.git_remote_area #git_remotes').val();
            this.showDialog('overview', this.location);
            $.getJSON(this.path + 'controller.php?action=fetch&path=' + this.location + '&remote=' + remote, function(result){
                if (result.status == 'login_required') {
                    codiad.message.error(result.message);
                    _this.showDialog('login', _this.location);
                    _this.login = function(){
                        var username = $('.git_login_area #username').val();
                        var password = $('.git_login_area #password').val();
                        _this.showDialog('overview', _this.location);
                        $.post(_this.path + 'controller.php?action=fetch&path=' + _this.location + '&remote=' + remote,
                            {username: username, password: password}, function(result){
                                result = JSON.parse(result);
                                codiad.message[result.status](result.message);
                            });
                    };
                } else if (result.status == 'passphrase_required') {
                    codiad.message.error(result.message);
                    _this.showDialog('passphrase', _this.location);
                    _this.login = function() {
                        var passphrase = $('.git_login_area #passphrase').val();
                        _this.showDialog('overview', _this.location);
                        $.post(_this.path + 'controller.php?action=fetch&path=' + _this.location + '&remote=' + remote,
                            {passphrase: passphrase}, function(result){
                                result = JSON.parse(result);
                                codiad.message[result.status](result.message);
                            });
                    };
                } else {
                    codiad.message[result.status](result.message);
                }
            });
        },
        
        checkout: function(path, repo) {
            var result = confirm("Are you sure to undo the changes on: " + path);
            if (result) {
                $.getJSON(this.path + 'controller.php?action=checkout&repo=' + repo + '&path=' + path, function(result){
                    codiad.message[result.status](result.message);
                    if (codiad.active.isOpen(repo + "/" + path)) {
                        codiad.message.notice("Reloading file after undoing changes");
                        codiad.active.close(repo + "/" + path);
                        codiad.filemanager.openFile(repo + "/" + path);
                    }
                });
            }
        },
        
        status: function(path) {
            path = this.getPath(path);
            this.files = [];
            var _this = this;
            $.getJSON(this.path + 'controller.php?action=status&path=' + path, function(result){
                if (result.status == 'error') {
                    codiad.message.error(result.message);
                    return;
                }
                //Reset list
                $('.git_list tbody').html('');
                var added, deleted, modified, renamed, untracked;
                added = result.data.added;
                deleted = result.data.deleted;
                modified = result.data.modified;
                renamed = result.data.renamed;
                untracked = result.data.untracked;
                //Add entries
                $.each(added, function(i, item){
                    _this.addLine("Added", item);
                });
                $.each(deleted, function(i, item){
                    _this.addLine("Deleted", item);
                });
                $.each(modified, function(i, item){
                    _this.addLine("Modified", item);
                });
                $.each(renamed, function(i, item) {
                    _this.addLine("Renamed", item);
                });
                $.each(untracked, function(i, item) {
                    _this.addLine("Untracked", item);
                });
                _this.setBranch(result.data.branch);
            });
        },
        
        log: function(repo, path) {
            repo = this.getPath(repo);
            var component = "";
            if (typeof(path) !== 'undefined') {
                component = '&path=' + this.encode(path);
                $('.git_log_area .path').text(path);
            } else if (this.files.length !== 0) {
                component = '&path=' + this.encode(this.files[0]);
                $('.git_log_area .path').text(this.files[0]);
            }
            $.getJSON(this.path + 'controller.php?action=log&repo=' + this.encode(repo) + component, function(result){
                if (result.status == 'error') {
                    codiad.message.error(result.message);
                    return;
                }
                $.each(result.data, function(i, item){
                    item = item.replace(new RegExp(" ", "g"), "&nbsp;");
                    if (item.indexOf("commit") === 0) {
                        $('.git_log_area .git_log').append('<li class="commit_hash">' + item + '</li>');
                    } else {
                        $('.git_log_area .git_log').append('<li>' + item + '</li>');
                    }
                });
            });
        },
        
        getRemotes: function(path) {
            path = this.getPath(path);
            $.getJSON(this.path + 'controller.php?action=getRemotes&path=' + path, function(result){
                if (result.status == 'error') {
                    codiad.message.error(result.message);
                    return;
                }
                $.each(result.data, function(i, item){
                    $('#git_remotes').append('<option value="'+i+'">'+i+'</option>');
                });
                $.each(result.data, function(i, item){
                    $('.git_remote_info').html(item);
                    return false;
                });
                $('#git_remotes').live('change', function(){
                    var value = $('#git_remotes').val();
                    $('.git_remote_info').html(result.data[value]);
                });
            });
        },
        
        newRemote: function(path) {
            var _this   = this;
            path        = this.getPath(path);
            var name    = $('.git_new_remote_area #remote_name').val();
            var url     = $('.git_new_remote_area #remote_url').val();
            $.getJSON(this.path + 'controller.php?action=newRemote&path=' + path + '&name=' + name + '&url=' + url, function(result){
                _this.showDialog('overview', _this.location);
                codiad.message[result.status](result.message);
            });
        },
        
        removeRemote: function(path) {
            var _this   = this;
            path        = this.getPath(path);
            var name    = $('#git_remotes').val();
            var result  = confirm("Are you sure to remove the remote: " + name);
            if (result) {
                $.getJSON(this.path + 'controller.php?action=removeRemote&path=' + path + '&name=' + name, function(result){
                    codiad.message[result.status](result.message);
                });
            }
            this.showDialog('overview', this.location);
        },
        
        renameRemote: function(path) {
            path        = this.getPath(path);
            var name    = $('#git_remote').text();
            var newName = $('#git_new_name').val();
            $.getJSON(this.path + 'controller.php?action=renameRemote&path=' + path + '&name=' + name + '&newName=' + newName, function(result){
                codiad.message[result.status](result.message);
            });
            this.showDialog('overview', this.location);
        },
        
        getRemoteBranches: function(path){
            path = this.getPath(path); 
            $.getJSON(this.path + 'controller.php?action=getRemoteBranches&path=' + path, function(result){
                if (result.status == 'error') {
                    codiad.message.error(result.message);
                    return;
                }
                $.each(result.data.branches, function(i, item){
                    $('#git_remote_branches').append('<option value="'+item+'">'+item+'</option>');
                });
                $('#git_new_branch').val(result.data.current.substr(result.data.current.search('/') + 1));
                $('#git_remote_branches').val(result.data.current);
            });
        },
        
        checkoutRemote: function(path){
        	path           = this.getPath(path);
        	var remoteName = $('#git_remote_branches').val();
        	var name       = $('#git_new_branch').val();
    	    $.getJSON(this.path + 'controller.php?action=checkoutRemote&path=' + path + '&name=' + name + '&remoteName=' + remoteName, function(result){
    		    codiad.message[result.status](result.message);
    	    });
    	    this.showDialog('remote', this.location);
        },
        
        getBranches: function(path) {
            path = this.getPath(path);
            $.getJSON(this.path + 'controller.php?action=getBranches&path=' + path, function(result){
                if (result.status == 'error') {
                    codiad.message.error(result.message);
                    return;
                }
                $.each(result.data.branches, function(i, item){
                    $('#git_branches').append('<option value="'+item+'">'+item+'</option>');
                });
                $('#git_branches').val(result.data.current);
            });
        },
        
        newBranch: function(path) {
            var _this   = this;
            path        = this.getPath(path);
            var name    = $('.git_new_branch_area #branch_name').val();
            $.getJSON(this.path + 'controller.php?action=newBranch&path=' + path + '&name=' + name, function(result){
                _this.showDialog('branches', _this.location);
                codiad.message[result.status](result.message);
            });
        },
        
        deleteBranch: function(path) {
            path = this.getPath(path);
            var name = $('#git_branches').val();
            var result = confirm("Are you sure to remove the branch: " + name);
            if (result) {
                $.getJSON(this.path + 'controller.php?action=deleteBranch&path=' + path + '&name=' + name, function(result){
                    codiad.message[result.status](result.message);
                });
            }
            this.showDialog('branches', this.location);
        },
        
        checkoutBranch: function(path) {
            path = this.getPath(path);
            var name = $('#git_branches').val();
            $.getJSON(this.path + 'controller.php?action=checkoutBranch&path=' + path + '&name=' + name, function(result){
                codiad.message[result.status](result.message);
            });
            this.showDialog('overview', this.location);
        },
        
        renameBranch: function(path) {
            path        = this.getPath(path);
            var name    = $('#git_branch').text();
            var newName = $('#git_new_name').val();
            $.getJSON(this.path + 'controller.php?action=renameBranch&path=' + path + '&name=' + name + '&newName=' + newName, function(result){
                codiad.message[result.status](result.message);
            });
            this.showDialog('overview', this.location);
        },
        
        merge: function(path) {
            var _this = this;
            path = this.getPath(path);
            var name = $('#git_branches').val();
            var result = confirm("Are you sure to merge " + name + " into the current branch?");
            if (result) {
                $.getJSON(this.path + 'controller.php?action=merge&path=' + path + '&name=' + name, function(result){
                    codiad.message[result.status](result.message);
                    _this.status(_this.location);
                });
            }
            this.showDialog('overview', this.location);
        },
        
        rename: function(fPath) {
            var _this       = this;
            var path        = _this.dirname(fPath);
            var old_name    = fPath.replace(path, "").substr(1);
            if (old_name.length === 0 || old_name === fPath) {
                //Codiad renaming
                codiad.filemanager.renameNode(fPath);
                return;
            }
            var shortName   = codiad.filemanager.getShortName(fPath);
            var type        = codiad.filemanager.getType(fPath);
            codiad.modal.load(250, codiad.filemanager.dialog, { action: 'rename', path: fPath, short_name: shortName, type: type});
            $('#modal-content form')
                .live('submit', function(e) {
                    e.preventDefault();
                    var newName = $('#modal-content form input[name="object_name"]')
                        .val();
                    // Build new path
                    var arr = fPath.split('/');
                    var temp = [];
                    for (i = 0; i < arr.length - 1; i++) {
                        temp.push(arr[i]);
                    }
                    var newPath = temp.join('/') + '/' + newName;
                    codiad.modal.unload();
                    $.getJSON(_this.path + "controller.php?action=rename&path="+path+"&old_name="+old_name+"&new_name="+newName, function(data) {
                        if (data.status != 'error') {
                            codiad.message.success(type.charAt(0)
                                .toUpperCase() + type.slice(1) + ' Renamed');
                            var node = $('#file-manager a[data-path="' + fPath + '"]');
                            // Change pathing and name for node
                            node.attr('data-path', newPath)
                                .html(newName);
                            if (type == 'file') { // Change icons for file
                                curExtClass = 'ext-' + codiad.filemanager.getExtension(fPath);
                                newExtClass = 'ext-' + codiad.filemanager.getExtension(newPath);
                                $('#file-manager a[data-path="' + newPath + '"]')
                                    .removeClass(curExtClass)
                                    .addClass(newExtClass);
                            } else { // Change pathing on any sub-files/directories
                                codiad.filemanager.repathSubs(path, newPath);
                            }
                            // Change any active files
                            codiad.active.rename(fPath, newPath);
                        } else {
                            codiad.message.error(data.message);
                            codiad.filemanager.renameNode(fPath);
                        }
                    });
                });
        },
        
        submoduleDialog: function(repo, path) {
            this.location = repo;
            if (repo === path) {
                path = "";
            } else {
                path = path.replace(repo + "/", "");
            }
            this.files      = [];
            this.files.push(path);
            this.showDialog('submodule');
        },
        
        submodule: function(repo, dir, submodule) {
            var _this = this;
            repo = repo || this.location;
            path = dir;
            if (this.files[0] != "") {
            	path = this.files[0] + "/" + dir;
            }
            _this.showDialog('overview', repo);
            $.getJSON(this.path + 'controller.php?action=submodule&repo='+repo+'&path='+path+'&submodule='+submodule, function(result){
                if (result.status == 'login_required') {
                    codiad.message.error(result.message);
                    _this.showDialog('login', _this.location);
                    _this.login = function(){
                        var username = $('.git_login_area #username').val();
                        var password = $('.git_login_area #password').val();
                        _this.showDialog('overview', _this.location);
                        $.post(_this.path + 'controller.php?action=submodule&repo='+repo+'&path='+path+'&submodule='+submodule,
                            {username: username, password: password}, function(result){
                                result = JSON.parse(result);
                                codiad.message[result.status](result.message);
                                if (result.status == 'success') {
                                    codiad.filemanager.rescan(repo);
                                }
                            });
                    };
                } else if (result.status == 'passphrase_required') {
                    codiad.message.error(result.message);
                    _this.showDialog('passphrase', _this.location);
                    _this.login = function() {
                        var passphrase = $('.git_login_area #passphrase').val();
                        _this.showDialog('overview', _this.location);
                        $.post(_this.path + 'controller.php?action=submodule&repo='+repo+'&path='+path+'&submodule='+submodule,
                            {passphrase: passphrase}, function(result){
                                result = JSON.parse(result);
                                codiad.message[result.status](result.message);
                                if (result.status == 'success') {
                                    codiad.filemanager.rescan(repo);
                                }
                            });
                    };
                } else {
                    codiad.message[result.status](result.message);
                    if (result.status == 'success') {
                        codiad.filemanager.rescan(repo);
                    }
                }
            });
        },
        
        initSubmodule: function(path) {
            var _this = this;
            path = path || this.location;
            $.getJSON(this.path + 'controller.php?action=initSubmodule&path='+path, function(result){
                if (result.status == 'login_required') {
                    codiad.message.error(result.message);
                    _this.showDialog('login', _this.location);
                    _this.login = function(){
                        var username = $('.git_login_area #username').val();
                        var password = $('.git_login_area #password').val();
                        _this.showDialog('overview', _this.location);
                        $.post(_this.path + 'controller.php?action=initSubmodule&path='+path,
                            {username: username, password: password}, function(result){
                                result = JSON.parse(result);
                                codiad.message[result.status](result.message);
                                if (result.status == 'success') {
                                    codiad.filemanager.rescan(path);
                                }
                            });
                    };
                } else if (result.status == 'passphrase_required') {
                    codiad.message.error(result.message);
                    _this.showDialog('passphrase', _this.location);
                    _this.login = function() {
                        var passphrase = $('.git_login_area #passphrase').val();
                        _this.showDialog('overview', _this.location);
                        $.post(_this.path + 'controller.php?action=initSubmodule&path='+path,
                            {passphrase: passphrase}, function(result){
                                result = JSON.parse(result);
                                codiad.message[result.status](result.message);
                                if (result.status == 'success') {
                                    codiad.filemanager.rescan(path);
                                }
                            });
                    };
                } else {
                    codiad.message[result.status](result.message);
                    if (result.status == 'success') {
                        codiad.filemanager.rescan(path);
                    }
                }
            })
        },
        
        numstat: function(path) {
            if (typeof(path) == 'undefined') {
                path = codiad.active.getPath();
            }
            $.getJSON(this.path + 'controller.php?action=numstat&path='+path, function(json){
                var insert = "";
                if (json.status != "error") {
                    var data    = json.data;
                    insert      = '<span class="icon-flow-branch"></span>'+ data.branch + ' +' + data.insertions + ',-' + data.deletions;
                }
                $('#git-stat').html(insert);
            });
        },

        repostat: function() {
            path = codiad.project.getCurrent();
            $.getJSON(this.path + 'controller.php?action=status&path='+path, function(json){
                var insert = "Unknown", cls = "";
                if (json.status != "error") {
                  var data    = json.data;
                    if (data.added.length !== 0 ||
                      data.deleted.length !== 0 ||
                      data.modified.length !== 0 ||
                      data.renamed.length !== 0) {
                      insert = "Uncommitted";
                      cls = "invalid";
                    } else if (data.untracked.length !== 0) {
                      insert = "Untracked";
                      cls = "untracked";
                    } else {
                      insert = "Committed";
                      cls = "valid";
                    }
                }
                $('#git-repo-stat').html(insert);
                $('#git-repo-stat-wrapper').removeClass("git-repo-stat-valid git-repo-stat-invalid git-repo-stat-untracked")
                   .addClass("git-repo-stat-"+cls);
                // show the icon
                $("#git-repo-status-icon").removeClass("git-repo-icon-valid git-repo-icon-invalid git-repo-icon-untracked")
                   .addClass("git-repo-icon-"+cls);
            });
        },
        
        showCommit: function(path, commit) {
            var _this = this;
            path = this.getPath(path);
            this.showDialog('showCommit', path);
            $.getJSON(this.path + 'controller.php?action=showCommit&path=' + this.encode(path) + '&commit=' + commit, function(result){
                $('.git_show_commit_area .hash').text(commit);
                if (result.status != "success") {
                    codiad.message.error(result.message);
                    _this.showDialog('overview', path);
                }
                result.data = _this.renderDiff(result.data);
                $('.git_show_commit_area .content ul').append(result.data.join(""));
            });
        },
        
        blame: function(path, repo) {
            var _this = this;
            this.location   = repo;
            path            = path.replace(repo + "/", "");
            this.showDialog('blame', repo);
            $.getJSON(this.path + 'controller.php?action=blame&repo=' + this.encode(repo) + '&path=' + this.encode(path), function(result){
                if (result.status != "success") {
                    codiad.message.error(result.message);
                    _this.showDialog('overview', repo);
                }
                $('.git_blame_area table thead th').text(path);
                //Split blame output per file line
                var hashRegExp = /^[a-z0-9]{40}/;
                var data = result.data, starts, startIndexes = [], segments = [], s, e, i;
                starts = data.filter(function(line){
                    return hashRegExp.test(line);
                });
                for (i = 0; i < starts.length; i++) {
                    startIndexes.push(data.indexOf(starts[i]));
                }
                for (i = 0; i < starts.length; i++) {
                    s = startIndexes[i];
                    e = (i < (starts.length - 1)) ? (startIndexes[i + 1]) : (data.length);
                    segments.push(data.slice(s, e));
                }
                //Combine lines with the same commit
                var hash = segments[0][0].match(hashRegExp)[0];
                var unique = [{segment: segments[0], hash: hash, lines: [segments[0][12]]}];
                for (i = 1; i < segments.length; i++) {
                    if (hash === segments[i][0].match(hashRegExp)[0]) {
                        //Same
                        unique[unique.length - 1].lines.push(segments[i][12]);
                    } else {
                        hash = segments[i][0].match(hashRegExp)[0];
                        //Next
                        unique.push({segment: segments[i], hash: hash, lines: [segments[i][12]]});
                    }
                }
                //Format output
                var output = "", msg, date, name, line;
                for (i = 0; i < unique.length; i++) {
                    msg = unique[i].segment[9].replace("summary ", "");
                    date = unique[i].segment[7].replace("committer-time ", "");
                    date = new Date(date * 1000);
                    date = (date.getMonth() + 1) + "/" + date.getDate() + "/" + date.getFullYear();
                    name = unique[i].segment[5].replace("committer ", "");
                    hash = unique[i].hash;
                    output += '<tr><td>' + msg + '<br>' + name + ': ' + date + '</td>';
                    output += '<td class="commit_hash" data-hash="' + hash + '">' + hash.substr(0, 8) + '</td><td><ol>';  
                    for (var j = 0; j < unique[i].lines.length; j++) {
                        line = unique[i].lines[j].replace(new RegExp('\t', 'g'), ' ')
                                                .replace(new RegExp(' ', 'g'), "&nbsp;")
                                                .replace(new RegExp('\n', 'g'), "<br>");
                        output += '<li>' + line + '</li>';
                    }
                    output += '</ol></td></tr>';
                }
                $('.git_blame_area table tbody').html(output);
            });
        },
        
        history: function(path, repo) {
            this.location   = repo;
            path            = path.replace(repo + "/", "");
            this.files      = [];
            this.files.push(path);
            this.showDialog('log', repo);
        },
        
        network: function(path) {
            var _this = this;
            path = this.getPath(path);
            this.showDialog('network', path);
            $.getJSON(this.path + 'controller.php?action=network&path=' + this.encode(path), function(result){
                _this.network_graph.setData(result.data);
                _this.network_graph.generate();
            });
        },
        
        login: function(){},
        
        setSettings: function(path) {
            var _this    = this;
            var settings = {};
            path = this.getPath(path);
            $('.git_settings_area input:not(.no_setting)').each(function(i, el){
                settings[$(el).attr("id")] = $(el).val();
            });
            
            $.post(this.path + 'controller.php?action=setSettings&path='+path, {settings: JSON.stringify(settings)}, function(result){
                result = JSON.parse(result);
                codiad.message[result.status](result.message);
                _this.showDialog('overview', _this.location);
            });
        },
        
        getSettings: function(path) {
            path = this.getPath(path);
            $.getJSON(this.path + 'controller.php?action=getSettings&path=' + path, function(result){
                if (result.status == 'error') {
                    codiad.message.error(result.message);
                    return;
                }
                var local = false;
                $.each(result.data, function(i, item){
                    if (/\//.test(i)) {
                        return;
                    }
                    $('.git_settings_area #' + i).val(item);
                    if (/^local_/.test(i)) {
                        local = true;
                    }
                });
                if (!local) {
                    $('#box_local').click();
                }
            });
        },
        
        /**
         * Get path
         * 
         * @param {string} [path]
         * @result {string} path
         */
        getPath: function(path) {
            if (typeof(path) == 'undefined') {
                return this.location;
            } else {
                return path;
            }
        },
        
        /**
         * Get basename
         * 
         * @param {string} [path]
         * @result {string} basename
         */
        basename: function(path) {
            return path.replace(/\\/g,'/').replace( /.*\//, '' );
        },
        
        /**
         * Get dirname
         * 
         * @param {string} [path]
         * @result {string} dirname
         */
        dirname: function(path) {
            return path.replace(/\\/g,'/').replace(/\/[^\/]*$/, '');
        },
        
        /**
         * Encode Uri component
         * 
         * @param {string} [string]
         * @result {string} encoded string
         */
        encode: function(string) {
            return encodeURIComponent(string);
        },
        
        addLine: function(status, name) {
            var line = this.line;
            var element = '<tr><td><input type="checkbox" data-line="'+line+'"></td><td class="'+status.toLowerCase()+'">'+status+'</td><td data-line="'+line+'" class="file">'+name+'</td><td><button class="git_button git_diff" data-line="'+line+'">Diff</button><button class="git_button git_undo" data-line="'+line+'">Undo changes</button></td></tr>';
            $('.git_list tbody').append(element);
            this.line++;
        },
        
        /**
         * Render git diff output
         * 
         * @param {Array} [array]
         * @result {Array} Renderd output
         */
        renderDiff: function(array) {
            var output = [], element, item;
            for (var i = 0; i < array.length; i++) {
                item = array[i];
                element = item.replace(new RegExp('\t', 'g'), ' ')
                            .replace(new RegExp(' ', 'g'), "&nbsp;")
                            .replace(new RegExp('\n', 'g'), "<br>");
                if (item.indexOf('+++') === 0 || item.indexOf('---') === 0 || /^index [0-9a-z]{7}..[0-9a-z]{7}/.test(item) || /^new file mode [0-9]{6}/.test(item)) {
                    continue;
                } else if (/^diff --git a\/.+ b\/.+/.test(item)) {
                    element = item.match(/^diff --git a\/.+ b\/(.+)/);
                    element = '<li class="file-info">' + element[1] + '</li>';
                } else if (/^@@ -[0-9,]+ \+[0-9,]+ @@/.test(item)) {
                    element = '<li class="wrapper">' + element + '</li>';
                } else if (item.indexOf('+') === 0 && item.indexOf('+++') !== 0) {
                    element = '<li class="plus">' + element + '</li>';
                } else if (item.indexOf('-') === 0 && item.indexOf('---') !== 0) {
                    element = '<li class="minus">' + element + '</li>';
                } else {
                    element = '<li>' + element + '</li>';
                }
                output.push(element);
            }
            return output;
        },
        
        setBranch: function(branch) {
            $('.git_area .branch').text(branch);
        },

        addStatusIcon: function () {
            if ($("span#git-repo-status-icon").length < 1) {
                $('#file-manager #project-root').before('<span id="git-repo-status-icon" class="hidden uncommit"></span>');
            }
        },

        isEnabledRepoStatus: function () {
            var setting = localStorage.getItem('codiad.plugin.codegit.disableRepoStatus'), ret = true;
            if (setting === "true") {
                ret = false;
            }
            return(ret);
        },

        isEnabledWrapper: function () {
            var setting = localStorage.getItem('codiad.plugin.codegit.disableHeader'), ret = true;
            if (setting === "true") {
                ret = false;
            }
            return(ret);
        },

        suppressCommitDiff: function() {
            return false || localStorage.getItem('codiad.plugin.codegit.suppressCommitDiff') == "true";
        }
    };
})(this, jQuery);