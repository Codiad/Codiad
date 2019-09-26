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
        codiad.Ignore.init();
    });

    codiad.Ignore = {
        
        path: curpath,
        ignoreData: [],
        ignorePath: "",
        entries: 0,
        line: "",
        
        init: function() {
            var _this = this;
            //Load data
            this.load();
            $.get(this.path+"template.html", function(html){
                _this.line = html;
            });
            //Subscribe
            amplify.subscribe("filemanager.onIndex", function(path){
                var buf = [];
                var fPath, result, ext, name;
                $.each(codiad.filemanager.indexFiles, function(i, item){
                    fPath   = item.name;
                    name    = _this.getName(fPath);
                    ext     = _this.getExtension(fPath);
                    result  = true;
                    $.each(_this.ignoreData, function(index, rule){
                        if (rule.range == "file") {
                            if (fPath == rule.name) {
                                result = false;
                            }
                        } else if (rule.range == "name") {
                            if (name == rule.name) {
                                result = false;
                            }
                        } else if (rule.range == "type") {
                            var tExt = new RegExp(rule.name.replace("*", "").replace(".", "\\.") + "$");
                            if (tExt.test(fPath)) {
                                result = false;
                            }
                        }
                        if (!result) {
                            return false;
                        }
                    });
                    if (result) {
                        buf.push(item);
                    }
                });
                codiad.filemanager.indexFiles = buf;
            });
            amplify.subscribe('settings.dialog.save', function(){
                if ($('.ignore_display').length > 0) {
                    codiad.Ignore.saveDialog();
                }
            });
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Show dialog with a log of every rule
        //
        //////////////////////////////////////////////////////////
        showDialog: function() {
            var path = this.path.substring(this.path.indexOf(window.location.pathname)) + "dialog.php?action=log";
            codiad.settings.show(path);
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Test if ignorePath is a directory
        //
        //////////////////////////////////////////////////////////
        isDir: function() {
            $.getJSON(this.path+"controller.php?action=isDir&path="+this.ignorePath, function(json){
                var type = "";
                if (json.result) {
                    $('#fileType').remove();
                    type = "directory";
                } else {
                    type = "file";
                }
                $('#filePath').text("Ignore just this "+type);
                $('#fileGeneral').text("Ignore this "+type+" in every project");
            });
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Show dialog to choose ignore range
        //
        //  Parameter
        //
        //  path - {String} - File path
        //
        //////////////////////////////////////////////////////////
        ignore: function(path) {
            codiad.modal.load(300, this.path+"dialog.php?action=ignore");
            this.ignorePath = path;
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Add new ignore rule of ignore dialog
        //
        //////////////////////////////////////////////////////////
        setIgnore: function() {
            var obj     = {};
            obj.range   = $('.ignore_range').val();
            if (obj.range == "file") {
                obj.name    = this.ignorePath;
            } else if (obj.range == "name") {
                obj.name    = this.getName(this.ignorePath);
            } else {
                obj.name    = "*."+this.getExtension(this.ignorePath);
            }
            this.ignoreData.push(obj);
            this.save();
            codiad.filemanager.rescan(this.getDir(this.ignorePath));
            codiad.modal.unload();
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Load current rules
        //
        //////////////////////////////////////////////////////////
        load: function() {
            var _this = this;
            $.getJSON(this.path+"controller.php?action=load", function(json){
                _this.ignoreData = json;
            });
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Save current rules
        //
        //////////////////////////////////////////////////////////
        save: function() {
            var _this = this;
            $.post(this.path+"controller.php?action=save", {data: JSON.stringify(_this.ignoreData)}, function(data){
                var json = JSON.parse(data);
                if (json.status == "error") {
                    codiad.message.error(json.message);
                }
            });
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Add current rules to log dialog
        //
        //////////////////////////////////////////////////////////
        loadDialog: function() {
            var _this = this;
            var line;
            $.each(this.ignoreData, function(i, item) {
                line = _this.addRule();
                $('.ignore_rule_name[data-line="'+line+'"]').val(item.name);
                $('.ignore_range[data-line="'+line+'"]').val(item.range);
            });
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Add new rule to log dialog
        //
        //////////////////////////////////////////////////////////
        addRule: function() {
            var number  = this.entries++;
            var line    = this.line.replace(new RegExp("__line__", "g"), number);
            $('#ignore_list').append(line);
            this.setDelete();
            return number;
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Save rule of log dialog
        //
        //////////////////////////////////////////////////////////
        saveDialog: function() {
            var _this   = this;
            var buf     = [];
            var line;
            $('.ignore_line').each(function(i, item){
                var obj = {"name": "", "range": ""};
                line = $(item).attr("data-line");
                obj.name = $('.ignore_rule_name[data-line="'+line+'"]').val();
                obj.range = $('.ignore_range[data-line="'+line+'"]').val();
                buf.push(obj);
            });
            this.ignoreData = buf;
            this.save();
            codiad.filemanager.rescan(codiad.project.getCurrent());
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Activate delete button of log dialog
        //
        //////////////////////////////////////////////////////////
        setDelete: function(){
            $('.ignore_remove').click(function(){
                var line = $(this).attr("data-line");
                $('.ignore_line[data-line="'+line+'"]').remove();
                return false;
            });
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Display help dialog
        //
        //////////////////////////////////////////////////////////
        help: function() {
            codiad.modal.load(400, this.path+"dialog.php?action=help");
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Get directory of file
        //
        //  Parameter
        //
        //  path - {String} - File path
        //
        //////////////////////////////////////////////////////////
        getDir: function(path) {
            return path.substring(0, path.lastIndexOf("/"));
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Get file name
        //
        //  Parameter
        //
        //  path - {String} - File path
        //
        //////////////////////////////////////////////////////////
        getName: function(path) {
            return path.substring(path.lastIndexOf("/")+1);
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Get extension of file
        //
        //  Parameter
        //
        //  path - {String} - File path
        //
        //////////////////////////////////////////////////////////
        getExtension: function(path) {
            return path.substring(path.lastIndexOf(".")+1);
        }
    };
})(this, jQuery);