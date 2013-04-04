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
        // Update Check
        //////////////////////////////////////////////////////////////////

        check: function () {
            var _this = this;
            var currentResponse = null;
            $.ajax({
                url: _this.controller + '?action=check',
                async: false,
                success: function (data) {
                    currentResponse = codiad.jsend.parse(data);
                }
            });
            if (currentResponse != 'error') {
                codiad.modal.load(350, this.dialog, {
                    action: 'check',
                    current: currentResponse.currentversion,
                    remote: currentResponse.remoteversion,
                    message: currentResponse.message,
                    archive: currentResponse.archive
                });
                $('#modal-content form')
                    .live('submit', function (e) {
                    e.preventDefault();
                    var archive = $('#modal-content form input[name="archive"]')
                        .val();
                        $('#modal-content').html('<div id="modal-loading"></div><br>Downloading ' + archive + '...<br><br>');
                        $.get(_this.controller + '?action=download&sha=' + archive, function(data) {
                            var response = codiad.jsend.parse(data);
                            codiad.modal.unload();
                            if (response != 'error') {
                                window.open('./' + archive + '.php','_self');
                            } else {
                                codiad.message.error(i18n('Updating failed for ') + archive);
                            }
                        });
                });
            }
        }

    };

})(this, jQuery);