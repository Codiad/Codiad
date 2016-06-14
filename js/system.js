/*
 *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function(global, $){

    var codiad = global.codiad = {};

    //////////////////////////////////////////////////////////////////////
    // loadScript instead of getScript (checks and balances and shit...)
    //////////////////////////////////////////////////////////////////////

    $.loadScript = function(url, arg1, arg2) {
        var cache = true,
            callback = null;
        //arg1 and arg2 can be interchangable
        if ($.isFunction(arg1)) {
            callback = arg1;
            cache = arg2 || cache;
        } else {
            cache = arg1 || cache;
            callback = arg2 || callback;
        }

        var load = true;
        //check all existing script tags in the page for the url
        jQuery('script[type="text/javascript"]')
            .each(function() {
            return load = (url != $(this)
                .attr('src'));
        });
        if (load) {
            //didn't find it in the page, so load it
            jQuery.ajax({
                type: 'GET',
                url: url,
                success: callback,
                dataType: 'script',
                cache: cache
            });
        } else {
            //already loaded so just call the callback
            if (jQuery.isFunction(callback)) {
                callback.call(this);
            };
        };
    };

    //////////////////////////////////////////////////////////////////////
    // Init
    //////////////////////////////////////////////////////////////////////

    $(function() {
        // Console fix for IE
        if (typeof(console) === 'undefined') {
            console = {}
            console.log = console.error = console.info = console.debug = console.warn = console.trace = console.dir = console.dirxml = console.group = console.groupEnd = console.time = console.timeEnd = console.assert = console.profile = function () {};
        }        
        
        // Sliding sidebars
        codiad.sidebars.init();
        var handleWidth = 10;
        
        // Messages
        codiad.message.init();

        $(window)
            .on('load resize', function() {

            var marginL, reduction;
            if ($("#sb-left")
                .css('left') !== 0 && !codiad.sidebars.leftLock) {
                marginL = handleWidth;
                reduction = 2 * handleWidth;
            } else {
                marginL = $("#sb-left")
                    .width();
                reduction = marginL + handleWidth;
            }
            $('#editor-region')
                .css({
                'margin-left': marginL + 'px',
                'height': ($('body')
                    .outerHeight()) + 'px'
            });
            $('#root-editor-wrapper')
                .css({
                'height': ($('body')
                    .outerHeight() - 60) + 'px' // TODO Adjust '75' in function of the final tabs height.
            });

            // Run resize command to fix render issues
            codiad.editor.resize();
            codiad.active.updateTabDropdownVisibility();
        });

        $('#settings').click(function(){
            codiad.settings.show();
        });
    });

})(this, jQuery);

