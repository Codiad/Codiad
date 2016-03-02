/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function(global, $){

    var codiad = global.codiad;

    $(function() {
        codiad.poller.init();
    });

    codiad.poller = {

        interval: 10000,

        init: function() {
            var _this = this;
            setInterval(function() {

                _this.checkAuth();
                _this.saveDrafts();

            }, _this.interval);

        },

        //////////////////////////////////////////////////////////////////
        // Poll authentication
        //////////////////////////////////////////////////////////////////

        checkAuth: function() {

            // Run controller to check session (also acts as keep-alive) & Check user
            $.get(codiad.user.controller + '?action=verify', function(data) {
                if (data == 'false') {
                    codiad.user.logout();
                }
            });

        },

        //////////////////////////////////////////////////////////////////
        // Poll For Auto-Save of drafts (persist)
        //////////////////////////////////////////////////////////////////

        saveDrafts: function() {
            $('#active-files a.changed')
                .each(function() {

                // Get changed content and path
                var path = $(this)
                    .attr('data-path');
                var content = codiad.active.sessions[path].getValue();

                // TODO: Add some visual indication about draft getting saved.

                // Set localstorage
                localStorage.setItem(path, content);

            });
        }

    };

})(this, jQuery);