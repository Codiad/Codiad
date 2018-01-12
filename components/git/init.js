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

        submit: function() {

            var _this = this;

            $.get(codiad.git.controller + '?action=submit', function(data) {
                var obj = JSON.parse(data);
                console.log(obj.data.submit);
                codiad.git.stash();
            });

        },

        diff: function() {

            var _this = this;

            $('#diff-window').text("");
            $.get(codiad.git.controller + '?action=diff', function(data) {
                var obj = JSON.parse(data);
                if (obj.data.diff != null) {
                    $('#diff-window').text(obj.data.diff);
                } else {
                    $('#diff-window').text("No Changes Saved");
                }
            });

        },

        stash: function() {

            var _this = this;

            $.get(codiad.git.controller + '?action=stash', function(data) {
                var obj = JSON.parse(data);
                console.log(obj.data.stash);
                codiad.git.diff();

                //codiad.active.sessions['WebClub2018/index.html'].getDocument().setValue('asdfas\nsdfsa');
            });
        },

        init: function() {


        },

    };

})(this, jQuery);
