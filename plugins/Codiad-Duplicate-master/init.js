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
        codiad.Duplicate.init();
    });

    codiad.Duplicate = {
        
        path: curpath,
        file: "",
        
        init: function() {
            
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Show dialog to enter new name
        //
        //  Parameter
        //
        //  path - {String} - File path
        //
        //////////////////////////////////////////////////////////
        showDialog: function(path) {
            this.file = path;
            var name = this.getName(path);
            codiad.modal.load(400, this.path+"dialog.php?name="+name);
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Duplicate file
        //
        //  Parameter
        //
        //  path - {String} - File path
        //  name - {String} - Name of the duplicate
        //
        //////////////////////////////////////////////////////////
        duplicate: function(path, name) {
            var _this = this;
            if (typeof(path) == 'undefined') {
                path = this.file;
            }
            if (typeof(name) == 'undefined') {
                name = $('#duplicate_name').val();
                codiad.modal.unload();
            }
            $.getJSON(_this.path+"controller.php?action=duplicate&path="+path+"&name="+name, function(json){
                codiad.message[json.status](json.message);
                codiad.filemanager.rescan(codiad.project.getCurrent());
            });
        },
        
        //////////////////////////////////////////////////////////
        //
        //  Get basename of file
        //
        //  Parameter
        //
        //  path - {String} - File path
        //
        //////////////////////////////////////////////////////////
        getName: function(path) {
            return path.substring(path.lastIndexOf("/")+1);
        }
    };
})(this, jQuery);