/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function(global, $) {

    var codiad = global.codiad;

    $(function() {
        codiad.git.init();
    });

    //////////////////////////////////////////////////////////////////
    //
    // Git Component for Codiad
    // ---------------------------------
    // Track and manage project's git status
    //
    //////////////////////////////////////////////////////////////////

    codiad.git = {

        controller: 'components/git/controller.php',

        diff: function() {

            var _this = this;

            $('#diff-window').text("");
            $.get(codiad.git.controller + '?action=diff', function(data) {
                var obj = JSON.parse(data);
                $('#diff-window').text(obj.data.diff);
            });

        },

        stash: function() {

            var _this = this;

            $.get(codiad.git.controller + '?action=stash', function(data) {
                codiad.git.diff();
            });

        },

        init: function() {

            var _this = this;

            // Focus from list.
            $('#list-active-files a')
                .live('click', function(e) {
                    e.stopPropagation();
                    _this.focus($(this).parent('li').attr('data-path'));
            });

        },

    };

})(this, jQuery);
