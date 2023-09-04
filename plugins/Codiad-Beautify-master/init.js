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
        codiad.Beautify.init();
    });

    codiad.Beautify = {
        
        path: curpath,
        beautifyPhp: null,
        lines: 0,
        row: 0,
        settings: {
            js: false, json: false, html: false, css: false, auto: false
        },
        files: ["html", "htm", "js", "json", "css", "php"],
        
        init: function() {
            var _this = this;
            //Load libs
            $.getScript(this.path+"libs/beautify-css.js");
            $.getScript(this.path+"libs/beautify-html.js");
            $.getScript(this.path+"libs/beautify.js");
            $.getScript(this.path+"libs/ext-beautify.js", function() {
				_this.beautifyPhp = ace.require("ace/ext/beautify");
			});
            //Load settings
            this.load();
            //Set subscriptions
            amplify.subscribe('active.onOpen', function(path){
	    	if(codiad.editor.getActive() === null)
			return;
                var manager = codiad.editor.getActive().commands;
                manager.addCommand({
                    name: "Beautify",
                    bindKey: {win: "Ctrl-Alt-B", mac: "Command-Alt-B"},
                    exec: function(){
                        _this.beautify();
                    }
                });
            });
            amplify.subscribe('active.onSave', function(path){
                path = path || codiad.active.getPath();
                var ext = _this.getExtension(path);
                if (_this.files.indexOf(ext) != -1) {
                    if (_this.check(path)) {
                        var content = codiad.editor.getContent();
                        _this.lines = _this.getLines();
                        _this.row   = codiad.editor.getActive().getCursorPosition().row;
                        content = _this.beautifyContent(path, content);
                        if (typeof(content) !== 'string') {
                            return true;
                        }
                        codiad.editor.setContent(content);
                        _this.guessCursorPosition();
                    }
                }
            });
            amplify.subscribe('context-menu.onShow', function(obj){
                var ext = _this.getExtension(obj.path);
                if (_this.files.indexOf(ext) != -1 && ext !== "php") {
                    $('#context-menu').append('<hr class="file-only beautify">');
                    $('#context-menu').append('<a class="file-only beautify" onclick="codiad.Beautify.contextMenu($(\'#context-menu\').attr(\'data-path\'));"><span class="icon-brush"></span>Beautify</a>');
                }
            });
            amplify.subscribe('context-menu.onHide', function(){
                $('.beautify').remove();
            });
            amplify.subscribe('settings.dialog.save', function(){
                if ($('#beautify_form').length > 0) {
                    codiad.Beautify.save();
                }
            });
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Show settings dialog
        //
        //////////////////////////////////////////////////////////
        showDialog: function() {
            codiad.modal.load(200, this.path+"dialog.php");
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Save new settings
        //
        //////////////////////////////////////////////////////////
        save: function() {
            var _this = this;
            this.checkSettings("js");
            this.checkSettings("json");
            this.checkSettings("html");
            this.checkSettings("css");
            this.checkSettings("php");
            $.post(this.path+"controller.php?action=save", {settings: JSON.stringify(this.settings)}, function(data){
                var json = JSON.parse(data);
                if (json.status == "error") {
                    codiad.message.error(json.message);
                } else {
                    codiad.message.success(json.message);
                }
                _this.load();
            });
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Load existing settings
        //
        //////////////////////////////////////////////////////////
        load: function() {
            var _this = this;
            $.getJSON(this.path+"controller.php?action=load", function(json){
                _this.settings = json;
            });
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Display settings in the dialog
        //
        //////////////////////////////////////////////////////////
        get: function() {
            this.setSettings("js");
            this.setSettings("json");
            this.setSettings("html");
            this.setSettings("css");
            this.setSettings("php");
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Get number of lines of current document
        //
        //  Parameters
        //
        //  content - {string} - (optional) Content of current document
        //
        //////////////////////////////////////////////////////////
        getLines: function(content) {
            content = content || codiad.editor.getContent();
            return (content.match(/\n/g) || []).length + 1;
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Guess the cursor position after beautifying content
        //
        //////////////////////////////////////////////////////////
        guessCursorPosition: function() {
            if (localStorage.getItem("codiad.plugin.beautify.guessCursorPosition") == "true") {
                var newLines= this.getLines();
                var factor  = newLines / this.lines;
                var newRow  = Math.floor(factor * this.row);
                codiad.editor.getActive().clearSelection();
                codiad.editor.getActive().moveCursorToPosition({"row":newRow, "column":0});
            }
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Set checkbox of dialog given by extension
        //
        //  Parameters
        //
        //  ext - {string} - Extension of id of checkbox
        //
        //////////////////////////////////////////////////////////
        setSettings: function(ext) {
            if (this.settings.auto[ext] === true) {
                $('#beautify_'+ext).attr("checked", "checked");
            }
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Check checkboxes and store status
        //
        //  Parameters
        //
        //  ext - {string} - Extension of id of checkbox
        //
        //////////////////////////////////////////////////////////
        checkSettings: function(ext) {
            if ($('#beautify_'+ext).attr("checked") == "checked") {
                this.settings.auto[ext] = true;
            } else {
                this.settings.auto[ext] = false;
            }
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Function to handle context menu click
        //
        //  Parameters
        //
        //  path - {string} - File path of the clicked file
        //
        //////////////////////////////////////////////////////////
        contextMenu: function(path) {
            var _this = this;
            $.get(this.path+"controller.php?action=getContent&path="+path, function(data){
                var content = _this.beautifyContent(path, data);
                $.post(_this.path+"controller.php?action=saveContent&path="+path, {"content": content}, function(result){
                    var json = JSON.parse(result);
                    if (json.status == "error") {
                        codiad.message.error(json.message);
                    } else {
                        codiad.message.success(json.message);
                    }
                });
            });
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Beautify content
        //
        //  Parameters
        //
        //  path - {string} - File path
        //  content - {string} - Content to beautify
        //  settings - {object} - Settings for beautify
        //
        //////////////////////////////////////////////////////////
        beautifyContent: function(path, content, settings) {
            this.checkBeautifySettings();
            if (typeof(settings) == 'undefined') {
                settings = this.settings.beautify;
            }
            var ext  = this.getExtension(path);
            if (ext == "html" || ext == "htm") {
                return html_beautify(content, settings);
            } else if (ext == "css") {
                return css_beautify(content, settings);
            } else if (ext == "js" || ext == "json") {
                return js_beautify(content, settings);
            } else if (ext == "php") {
				this.beautifyPhp.beautify(codiad.editor.getActive().getSession());
				return true;
            } else {
                return false;
            }
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Beautify command to handle hotkey
        //
        //////////////////////////////////////////////////////////
        beautify: function() {
            var _this   = this;
            var settings = this.settings.beautify;
            var path    = codiad.active.getPath();
            var editor  = codiad.editor.getActive();
            var session = editor.getSession();
            var selText = codiad.editor.getSelectedText();
            var range   = editor.selection.getRange();
            var fn      = function(range, text) {
                if (typeof(text) == 'undefined') {
                    settings.indent_level   = "keep";
                    range.start.column      = 0;
                    text = session.getTextRange(range);
                }
                text = _this.beautifyContent(path, text, settings);
                if (typeof(text) == 'string') {
					session.replace(range, text);
                }
            };
            if (selText !== "") {
                if (editor.selection.inMultiSelectMode) {
                    var multiRanges = editor.selection.getAllRanges();
                    for (var i = 0; i < multiRanges.length; i++) {
                        fn(multiRanges[i]);
                    }
                } else {
                    //Single selection
                    fn(range);
                }
            } else {
                this.row    = codiad.editor.getActive().getCursorPosition().row;
                this.lines  = this.getLines();
                var content = codiad.editor.getContent();
                range       = editor.selectAll() || editor.selection.getRange();
                fn(range, content);
                
                this.guessCursorPosition();
            }
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Get settings for given file path
        //
        //  Parameters
        //
        //  path - {string} - File path
        //
        //////////////////////////////////////////////////////////
        check: function(path) {
            var ext = this.getExtension(path);
            if (ext == "htm") {
                ext = "html";
            }
            return this.settings.auto[ext];
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Check settings for beautify
        //
        //////////////////////////////////////////////////////////
        checkBeautifySettings: function() {
            var char    = "";
            var tab     = 1;
            if (codiad.editor.settings.softTabs) {
                char    = " ";
                tab     = 4;
            } else {
                char    = "\t";
                tab     = 1;
            }
            this.settings.beautify.indent_char = char;
            this.settings.beautify.indent_size = tab;
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Get extension of file
        //
        //  Parameters
        //
        //  path - {string} - File path
        //
        //////////////////////////////////////////////////////////
        getExtension: function(path) {
            return path.substring(path.lastIndexOf(".")+1);
        }
    };
})(this, jQuery);
