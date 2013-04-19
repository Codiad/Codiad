/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function(global, $){

    var codiad = global.codiad;

    $(function() {
        codiad.plugin_manager.init();
    });

    codiad.plugin_manager = {

        controller: 'components/plugin_manager/controller.php',
        dialog: 'components/plugin_manager/dialog.php',

        init: function() {
           
        },

        //////////////////////////////////////////////////////////////////
        // Open the plugin manager dialog
        //////////////////////////////////////////////////////////////////

        list: function() {
            $('#modal-content form')
                .die('submit'); // Prevent form bubbling
            codiad.modal.load(500, this.dialog + '?action=list');
        },

        //////////////////////////////////////////////////////////////////
        // Activate Plugin
        //////////////////////////////////////////////////////////////////

        activate: function(name) {
            var _this = this;
            $.get(this.controller + '?action=activate&name=' + name, function(data) {
                var projectInfo = codiad.jsend.parse(data);
                if (projectInfo != 'error') {
                    codiad.modal.unload();
                    if(confirm('Plugin activated. Do you want to reload?')) {
                        window.location.reload();
                    }
                }
            });
        },

        //////////////////////////////////////////////////////////////////
        // Deactivate Plugin
        //////////////////////////////////////////////////////////////////

        deactivate: function(name) {
            var _this = this;
            $.get(this.controller + '?action=deactivate&name=' + name, function(data) {
                var projectInfo = codiad.jsend.parse(data);
                if (projectInfo != 'error') {
                    codiad.modal.unload();
                    if(confirm('Plugin deactivated. Do you want to reload?')) {
                        window.location.reload();
                    }
                }
            });
        }
    };
})(this, jQuery);
