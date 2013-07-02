/*
 *  Copyright (c) Codiad & daeks (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function(global, $){

    var codiad = global.codiad;

    $(function() {
        codiad.theme_manager.init();
    });

    codiad.theme_manager = {

        controller: 'components/theme_manager/controller.php',
        dialog: 'components/theme_manager/dialog.php',

        init: function() {
           
        },
        
        //////////////////////////////////////////////////////////////////
        // Open the theme manager market
        //////////////////////////////////////////////////////////////////
        
        market: function() {
            $('#modal-content form')
                .die('submit'); // Prevent form bubbling
            codiad.modal.load(500, this.dialog + '?action=market');
        },

        //////////////////////////////////////////////////////////////////
        // Open the theme manager dialog
        //////////////////////////////////////////////////////////////////

        list: function() {
            $('#modal-content form')
                .die('submit'); // Prevent form bubbling
            codiad.modal.load(500, this.dialog + '?action=list');
        },
        
        //////////////////////////////////////////////////////////////////
        // Checks for theme updates
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
        // Install theme
        //////////////////////////////////////////////////////////////////

        install: function(name, repo) {
            var _this = this;
            $('#modal-content').html('<div id="modal-loading"></div><div align="center">Installing ' + name + '...</div><br>');
            $.get(_this.controller + '?action=install&name=' + name + '&repo=' + repo, function(data) {
                var response = codiad.jsend.parse(data);
                if (response == 'error') {
                    codiad.message.error(response.message);
                }
                _this.list();
            });
        },
        
        //////////////////////////////////////////////////////////////////
        // Remove theme
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
        // Update theme
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
        // Activate theme
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
        // Deactivate theme
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
