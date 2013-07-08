/*
 *  Copyright (c) Codiad & daeks (codiad.com), distributed
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
        // Open the plugin manager market
        //////////////////////////////////////////////////////////////////
        
        market: function() {
            $('#modal-content form')
                .die('submit'); // Prevent form bubbling
            codiad.modal.load(500, this.dialog + '?action=market');
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
        // Checks for plugin updates
        //////////////////////////////////////////////////////////////////

        check: function() {
            $('#modal-content form')
                .die('submit'); // Prevent form bubbling
            codiad.modal.load(500, this.dialog + '?action=check');
        },
        
        openInBrowser: function(path) {
            window.open(path, '_newtab');
        },
        
        //////////////////////////////////////////////////////////////////
        // Install Plugin
        //////////////////////////////////////////////////////////////////

        install: function(name, repo) {
            var _this = this;
            $('#modal-content').html('<div id="modal-loading"></div><div align="center">Installing ' + name + '...</div><br>');
            $.get(_this.controller + '?action=install&name=' + name + '&repo=' + repo, function(data) {
                var response = codiad.jsend.parse(data);
                if (response == 'error') {
                    codiad.message.error(response.message);
                } else {
                    _this.list();
                }
            });
        },
        
        //////////////////////////////////////////////////////////////////
        // Remove Plugin
        //////////////////////////////////////////////////////////////////

        remove: function(name) {
            var _this = this;
            $.get(_this.controller + '?action=remove&name=' + name, function(data) {
                var response = codiad.jsend.parse(data);
                if (response == 'error') {
                    codiad.message.error(response.message);
                }
                _this.list();
            });
        },
        
        //////////////////////////////////////////////////////////////////
        // Update Plugin
        //////////////////////////////////////////////////////////////////

        update: function(name) {
            var _this = this;
            $('#modal-content').html('<div id="modal-loading"></div><div align="center">Updating ' + name + '...</div><br>');
            $.get(_this.controller + '?action=update&name=' + name, function(data) {
                var response = codiad.jsend.parse(data);
                if (response == 'error') {
                    codiad.message.error(response.message);
                }
                _this.check();
            });
        },

        //////////////////////////////////////////////////////////////////
        // Activate Plugin
        //////////////////////////////////////////////////////////////////

        activate: function(name) {
            var _this = this;
            $.get(this.controller + '?action=activate&name=' + name, function(data) {
                var response = codiad.jsend.parse(data);
                if (response != 'error') {
                    _this.list();
                }
            });
        },

        //////////////////////////////////////////////////////////////////
        // Deactivate Plugin
        //////////////////////////////////////////////////////////////////

        deactivate: function(name) {
            var _this = this;
            $.get(this.controller + '?action=deactivate&name=' + name, function(data) {
                var response = codiad.jsend.parse(data);
                if (response != 'error') {
                    _this.list();
                }
            });
        }
    };
})(this, jQuery);
