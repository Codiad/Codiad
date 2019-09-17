/*
 *  Copyright (c) Codiad & daeks (codiad.com), distributed
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
            $('#modal-content form')
                .die('submit'); // Prevent form bubbling
                codiad.modal.load(500, this.dialog + '?action=check');
                $('#modal-content').html('<div id="modal-loading"></div><div align="center">' + i18n("Contacting GitHub...") + '</div><br>');
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