/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */ 
 
 (function (global, $) {

    var codiad = global.codiad;

    $(function () {
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
                        codiad.modal.unload();
                        window.open(archive);
                });
            }
        }

    };

})(this, jQuery);