/*
 * Copyright (c) Codiad & Andr3as, distributed
 * as-is and without warranty under the MIT License.
 * See http://opensource.org/licenses/MIT for more information. 
 * This information must remain intact.
 */

codiad.PreviewHelper = {
    
    version: "1.0.0",
    
    init: function() {
        var _this = this;
        amplify.subscribe("active.onOpen", function(path){
            if (codiad.editor.getActive() !== null) {
                var manager = codiad.editor.getActive().commands;
                manager.addCommand({
                    name: 'OpenPreview',
                    bindKey: "Ctrl-O",
                    exec: function () {
                        _this.preview();
                    }
                });
            }
        });
        $('#context-menu a[onclick="codiad.filemanager.openInBrowser($(\'#context-menu\').attr(\'data-path\'));"]')
            .attr("onclick", "codiad.PreviewHelper.preview($(\'#context-menu\').attr(\'data-path\'))");
    },
    
    preview: function(path) {
        if (typeof(path) == 'undefined') {
            path = codiad.active.getPath();
        }
        var result = amplify.publish("helper.onPreview", path);
        if (result) {
            codiad.filemanager.openInBrowser(path);
        }
    }
};

//Running init
codiad.PreviewHelper.init();