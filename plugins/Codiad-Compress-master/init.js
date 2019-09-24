/*
* Copyright (c) Codiad & Andr3as, distributed
* as-is and without warranty under the MIT License.
* See [root]/license.md for more information. This information must remain intact.
*/

(function(global, $){

    var codiad  = global.codiad,
        scripts = document.getElementsByTagName('script'),
        path    = scripts[scripts.length-1].src.split('?')[0],
        curpath = path.split('/').slice(0, -1).join('/')+'/';

    $(function() {    
        codiad.Compress.init();
    });

    codiad.Compress = {
        
        path    : curpath,
        file    : "",
        settings: false,
        
        init: function() {
            var _this = this;
            $.getScript(this.path+"libs/uglifyjs.js");
            $.getScript(this.path+"libs/cssmin.js");
            amplify.subscribe("context-menu.onShow", function(obj){
                var ext = _this.getExtension(obj.path);
                if (ext == "css" || ext == "js") {
                    $('#context-menu').append('<hr class="file-only compress">');
                    $('#context-menu').append('<a class="file-only compress" onclick="codiad.Compress.compress($(\'#context-menu\').attr(\'data-path\'));"><span class="icon-feather"></span>Compress</a>');
                }
            });
            amplify.subscribe("context-menu.onHide", function(){
                $('.compress').remove();
            });
            amplify.subscribe('active.onOpen', function(path){
                var manager = codiad.editor.getActive().commands;
                manager.addCommand({
                    name: 'Compress',
                    bindKey: {win: 'Ctrl-Alt-C', mac: 'Command-Alt-C'},
                    exec: function(e){
                        _this.compress();
                    }
                });
            });
            amplify.subscribe('active.onSave', function(path){
                path = path || codiad.active.getPath();
                var ext = _this.getExtension(path);
                if (_this.compressOnSave() && _this.checkExtension(ext)) {
                    var session = codiad.active.sessions[path];
                    var content = session.getValue();
                    content = _this.compressCode(ext, content);
                    _this.saveCode(path, content);
                }
            });
        },

        //////////////////////////////////////////////////////////
        //
        //  Handle code compression (get code, compress and save)
        //
        //  Parameters:
        //
        //  path - {String} - File path
        //
        //////////////////////////////////////////////////////////
        compress: function(path) {
            if (typeof(path) == 'undefined') {
                path = codiad.active.getPath();
            }

            var _this = this;
            this.file = path;
            var ext = this.getExtension(path);
            if (!this.checkExtension(ext)) {
                return false;
            }
            $.get(this.path+"controller.php?action=getContent&path="+path, function(code){
                code = _this.compressCode(ext, code);
                _this.saveCode(path, code);
            });
        },

        //////////////////////////////////////////////////////////
        //
        //  Compress code
        //
        //  Parameters:
        //
        //  ext - {String} - File extension
        //
        //////////////////////////////////////////////////////////
        compressCode: function(ext, code) {
            //Minify code
            if (ext == "css") {
                return this.minify(code);
            } else if (ext == "js") {
                return this.uglify(code);
            }
        },

        //////////////////////////////////////////////////////////
        //
        //  Save the compressed code
        //
        //  Parameters:
        //
        //  path - {String} - File path
        //  code - {String} - Compressed code
        //
        //////////////////////////////////////////////////////////
        saveCode: function(path, code) {
            var ext = this.getExtension(path);
            $.post(this.path+"controller.php?action=compress"+ext.toUpperCase()+"&path="+path, {"code": code}, function(data){
                    data = JSON.parse(data);
                    if (data.status == "error") {
                        codiad.message.error(data.message);
                    } else {
                        codiad.message.success(data.message);
                        codiad.filemanager.rescan($('#project-root').attr('data-path'));
                    }
                });
        },

        //////////////////////////////////////////////////////////
        //
        //  Use CSSmin to compress the file
        //
        //////////////////////////////////////////////////////////
        minify: function(code) {
            return YAHOO.compressor.cssmin(code);
        },

        //////////////////////////////////////////////////////////
        //
        //  Use UglifyJS to compress file
        //
        //////////////////////////////////////////////////////////
        uglify: function(code) {
            return UglifyJS.minify(code);
        },

        //////////////////////////////////////////////////////////
        //
        //  Get extension of the given file
        //
        //  Parameters:
        //
        //  path - {String} - File path
        //
        //////////////////////////////////////////////////////////
        getExtension: function(path) {
            return path.substring(path.lastIndexOf(".")+1);
        },

        //////////////////////////////////////////////////////////
        //
        //  Check extension if minification works
        //
        //  Parameters:
        //
        //  ext - {String} - File extension
        //
        //////////////////////////////////////////////////////////
        checkExtension: function(ext) {
            return (ext == "css" || ext == "js");
        },

        //////////////////////////////////////////////////////////
        //
        //  Check wheater to compress file on save or not
        //
        //////////////////////////////////////////////////////////
        compressOnSave: function() {
            return false || localStorage.getItem('codiad.plugin.compress.compressOnSave') == "true";
        }
    };

})(this, jQuery);
