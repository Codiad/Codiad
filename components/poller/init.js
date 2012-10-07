/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

$(function() {
    poller.init();
});

var poller = {

    controller: 'components/poller/controller.php',
    interval: 10000,

    init: function() {

        setInterval(function() {

            poller.check_auth();
            poller.save_drafts();

        }, poller.interval);

    },

    //////////////////////////////////////////////////////////////////
    // Poll authentication
    //////////////////////////////////////////////////////////////////

    check_auth: function() {

        // Run controller to check session (also acts as keep-alive)
        $.get(poller.controller + '?action=check_auth', function(data) {

            if (data) {
                parsed = jsend.parse(data);
                if (parsed == 'error') {
                    // Session not set, reload
                    user.logout();
                }
            }

        });

        // Check user
        $.get(user.controller + '?action=verify', function(data) {
            if (data == 'false') {
                user.logout();
            }
        });

    },

    //////////////////////////////////////////////////////////////////
    // Poll For Auto-Save of drafts (persist)
    //////////////////////////////////////////////////////////////////

    save_drafts: function() {
        $('#active-files a.changed')
            .each(function() {

            // Get changed content and path
            var path = $(this)
                .attr('data-path');
            var content = active.sessions[path].getValue();

            // TODO: Add some visual indication about draft getting saved.

            // Set localstorage
            localStorage.setItem(path, content);

        });
    }

};
