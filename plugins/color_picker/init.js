/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function(global, $){

    var codiad = global.codiad;

    $(function() {
        codiad.colorPicker.init();
    });

    codiad.colorPicker = {

        init: function() {

            $.loadScript("plugins/color_picker/color_parser.js");
            $.loadScript("plugins/color_picker/jquery.colorpicker.js");

        },

        open: function() {

            codiad.modal.load(400, 'plugins/color_picker/dialog.php');

        },

        insert: function(type) {
            var color = '';
            if (type == 'rgb') {
                color = $('.colorpicker_rgb_r input')
                    .val() + ',' + $('.colorpicker_rgb_g input')
                    .val() + ',' + $('.colorpicker_rgb_b input')
                    .val();
                if (returnRGBWrapper === false) {
                    insert = (color);
                } else {
                    insert = ('rgb(' + color + ')');
                }
            } else {
                color = $('.colorpicker_hex input')
                    .val();
                if (sellength == 3 || sellength == 6) {
                    if (seltest) {
                        insert = color;
                    } else {
                        insert = '#' + color;
                    }
                } else {
                    insert = '#' + color;
                }
            }

            codiad.active.insertText(insert);
            codiad.modal.unload();

        }

    };

})(this, jQuery);
