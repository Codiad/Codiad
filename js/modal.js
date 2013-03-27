(function(global, $){

    var codiad = global.codiad;

    //////////////////////////////////////////////////////////////////////
    // Modal
    //////////////////////////////////////////////////////////////////////

    codiad.modal = {

        load: function(width, url, data) {
            data = data || {};
            $('#modal')
                .css({
                    'top': '15%',
                    'left': '50%',
                    'min-width': width + 'px',
                    'margin-left': '-' + Math.ceil(width / 2) + 'px'
                })
                .draggable({
                    handle: '#drag-handle'
                });
            $('#modal-content')
                .html('<div id="modal-loading"></div>');
            $.get(url, data, function(data) {
                $('#modal-content').html(data);
                // Fix for Firefox autofocus goofiness
                $('input[autofocus="autofocus"]')
                    .focus();
            });
            $('#modal, #modal-overlay')
                .fadeIn(200);
            codiad.sidebars.modalLock = true;
        },

        hideOverlay: function() {
            $('#modal-overlay')
                .hide();
        },

        unload: function() {
            $('#modal-content form')
                .die('submit'); // Prevent form bubbling
            $('#modal, #modal-overlay')
                .fadeOut(200);
            $('#modal-content')
                .html('');
            codiad.sidebars.modalLock = false;
            if (!codiad.sidebars.userLock) { // Slide sidebar back
                $('#sb-left')
                    .animate({
                        'left': '-290px'
                    }, 300, 'easeOutQuart');
                $('#editor-region')
                    .animate({
                        'margin-left': '10px',
                        'width': ($('body')
                            .outerWidth() - 20) + 'px'
                }, 300, 'easeOutQuart');
            }
        }

    };

})(this, jQuery);

