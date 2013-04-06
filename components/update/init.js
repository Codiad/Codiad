/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */ 
 
 (function (global, $) {

    var codiad = global.codiad;

    $(window)
        .load(function() {
            codiad.update.init();
        });

    codiad.update = {

        controller: 'components/update/controller.php',
        dialog: 'components/update/dialog.php',

        //////////////////////////////////////////////////////////////////
        // Initilization
        //////////////////////////////////////////////////////////////////

        init: function () {
            var _this = this;
            $.get(_this.controller + '?action=init');
        },
        
        //////////////////////////////////////////////////////////////////
        // Test Permission
        //////////////////////////////////////////////////////////////////

        test: function () {
            var _this = this;
            $.get(_this.controller + '?action=test', function(data) {
                var response = codiad.jsend.parse(data);
                if (response != 'error') {
                    codiad.update.update();
                } else {
                    codiad.message.error('No Write Permission');
                }
            });
        },

        //////////////////////////////////////////////////////////////////
        // Update Check
        //////////////////////////////////////////////////////////////////

        check: function () {
            var _this = this;
            $('#modal-content form')
                .die('submit'); // Prevent form bubbling
                codiad.modal.load(500, this.dialog + '?action=check');
                $('#modal-content').html('<div id="modal-loading"></div><div align="center">Contacting GitHub...</div><br>');
        }, 
        
        //////////////////////////////////////////////////////////////////
        // Update System
        //////////////////////////////////////////////////////////////////

        update: function () {
            var _this = this;
            var remoteversion = $('#modal-content form input[name="remoteversion"]')
                        .val();
            codiad.modal.load(350, this.dialog + '?action=update&remoteversion=' + remoteversion);            
            $('#modal-content form')
                    .live('submit', function (e) {
                    e.preventDefault();
                    var remoteversion = $('#modal-content form input[name="remoteversion"]')
                        .val();
                        $('#modal-content').html('<div id="modal-loading"></div><br>Downloading ' + remoteversion + '...<br><br>');
                        $.get(_this.controller + '?action=update&remoteversion=' + remoteversion, function(data) {
                            var response = codiad.jsend.parse(data);
                            codiad.modal.unload();
                            if (response != 'error') {
                                window.open('./' + remoteversion + '.php','_self');
                            } else {
                                codiad.message.error('Update failed');
                            }
                        });
                });
        },
        
        //////////////////////////////////////////////////////////////////
        // Download Archive
        //////////////////////////////////////////////////////////////////

        download: function () {
            var _this = this;
            var archive = $('#modal-content form input[name="archive"]')
                        .val();
            $('#download')
                .attr('src', archive);            
            $.get(_this.controller + '?action=clear');             
            codiad.modal.unload();    
        }

    };

})(this, jQuery);