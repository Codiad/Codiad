/*
* Copyright (c) Codiad & Andr3as, distributed
* as-is and without warranty under the MIT License.
* See [root]/license.md for more information. This information must remain intact.
*/

(function(global, $){
    
    var codiad = global.codiad,
        scripts = document.getElementsByTagName('script'),
        path = scripts[scripts.length-1].src.split('?')[0],
        curpath = path.split('/').slice(0, -1).join('/')+'/';

    $(function() {
        codiad.HTMLPreview.init();
    });

    codiad.HTMLPreview = {
        
        path: curpath,
        default: "",
        
        init: function() {
            var _this = this;
            amplify.subscribe("context-menu.onShow", function(obj){
                var ext = _this.getExtension(obj.path);
                var defaultExt = ["html", "htm", "php", "php4", "php5", "phtml"];
                if (defaultExt.indexOf(ext) !== -1) {
                    $('#context-menu').append('<hr class="file-only html-preview">');
                    $('#context-menu').append('<a class="file-only html-preview" onclick="codiad.HTMLPreview.setDefault($(\'#context-menu\').attr(\'data-path\'));"><span class="icon-check"></span>Set as preview</a>');
                }
            });
            amplify.subscribe("context-menu.onHide", function(){
                $('.html-preview').remove();
            });
            //Register preview callbacks
            amplify.subscribe("helper.onPreview", function(path){
                var ext = _this.getExtension(path);
                if (ext == "css") {
                    _this.showPreview(path);
                    return false;
                }
            });
            //Load helper
            if (typeof(codiad.PreviewHelper) == 'undefined') {
                $.getScript(this.path+"previewHelper.js");
            }
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Set a default html file
        //
        //  Parameter:
        //
        //  path - {String} - File path
        //
		//////////////////////////////////////////////////////////
        setDefault: function(path) {
            this.default = path;
            codiad.message.success("Default added");
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Show preview
        //
        //  Parameter:
        //
        //  path - {String} - File path
        //
		//////////////////////////////////////////////////////////
        showPreview: function(path) {
            if (typeof(path) == 'undefined') {
                path = codiad.active.getPath();
            }
            if (this.default === "") {
                codiad.filemanager.openInBrowser(path);
            } else {
                codiad.filemanager.openInBrowser(this.default);
            }
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Get file extension
        //
        //  Parameter:
        //
        //  path - {String} - File path
        //
		//////////////////////////////////////////////////////////
        getExtension: function(path) {
            return path.substring(path.lastIndexOf(".")+1);
        }
    };
})(this, jQuery);