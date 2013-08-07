/*
 *  Copyright (c) Codiad & daeks (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function(global, $){

    var codiad = global.codiad;

    $(function() {
        codiad.market.init();
    });

    codiad.market = {

        controller: 'components/market/controller.php',
        dialog: 'components/market/dialog.php',

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
        // Open marketplace
        //////////////////////////////////////////////////////////////////

        list: function(type) {
            $('#modal-content form')
                .die('submit'); // Prevent form bubbling
            codiad.modal.load(800, this.dialog + '?action=list&type='+type);
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
        // Install
        //////////////////////////////////////////////////////////////////

        install: function(type, name, repo) {
            var _this = this;
            if(repo != '') {
              $('#modal-content').html('<div id="modal-loading"></div><div align="center">Installing ' + name + '...</div><br>');
              $.get(_this.controller + '?action=install&type=' + type + '&name=' + name + '&repo=' + repo, function(data) {
                  var response = codiad.jsend.parse(data);
                  if (response == 'error') {
                      codiad.message.error(response.message);
                  } else {
                      _this.list();
                  }
              });
            } else {
               codiad.message.error('No Repository URL');
            }
        },
        
        //////////////////////////////////////////////////////////////////
        // Remove
        //////////////////////////////////////////////////////////////////

        remove: function(type, name) {
            var _this = this;
            $('#modal-content').html('<div id="modal-loading"></div><div align="center">Deleting ' + name + '...</div><br>');
            $.get(_this.controller + '?action=remove&type=' + type + '&name=' + name, function(data) {
                var response = codiad.jsend.parse(data);
                if (response == 'error') {
                    codiad.message.error(response.message);
                }
                _this.list();
            });
        },
        
        //////////////////////////////////////////////////////////////////
        // Update
        //////////////////////////////////////////////////////////////////

        update: function(type, name) {
            var _this = this;
            $('#modal-content').html('<div id="modal-loading"></div><div align="center">Updating ' + name + '...</div><br>');
            $.get(_this.controller + '?action=update&type=' + type + '&name=' + name, function(data) {
                var response = codiad.jsend.parse(data);
                if (response == 'error') {
                    codiad.message.error(response.message);
                }
                _this.check();
            });
        },

        //////////////////////////////////////////////////////////////////
        // Activate
        //////////////////////////////////////////////////////////////////

        activate: function(type, name) {
            var _this = this;
            $.get(this.controller + '?action=activate&type=' + type + '&name=' + name, function(data) {
                var response = codiad.jsend.parse(data);
                if (response != 'error') {
                    _this.list();
                }
            });
        },

        //////////////////////////////////////////////////////////////////
        // Deactivate
        //////////////////////////////////////////////////////////////////

        deactivate: function(type, name) {
            var _this = this;
            $.get(this.controller + '?action=deactivate&type=' + type + '&name=' + name, function(data) {
                var response = codiad.jsend.parse(data);
                if (response != 'error') {
                    _this.list();
                }
            });
        }
    };
})(this, jQuery);
