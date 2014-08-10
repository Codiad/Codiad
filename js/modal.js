(function(global, $){

    var codiad = global.codiad;

    //////////////////////////////////////////////////////////////////////
    // Modal
    //////////////////////////////////////////////////////////////////////

    codiad.modal = {

        load: function(width, url, data) {
            data = data || {};
            var bounds = this._getBounds(width);
            $('#modal')
                .css({
                    'top': bounds.top,
                    'left': bounds.left,
                    'min-width': width + 'px',
                    'margin-left': '-' + Math.ceil(width / 2) + 'px'
                })
                .draggable({
                    handle: '#drag-handle'
                });
            $('#modal-content')
                .html('<div id="modal-loading"></div>');
            this.load_process = $.get(url, data, function(data) {
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
            this._setBounds();
            $('#modal-content form')
                .die('submit'); // Prevent form bubbling
            $('#modal, #modal-overlay')
                .fadeOut(200);
            $('#modal-content')
                .html('');
            codiad.sidebars.modalLock = false;
            if (!codiad.sidebars.leftLock) { // Slide sidebar back
                $('#sb-left')
                    .animate({
                        'left': '-290px'
                    }, 300, 'easeOutQuart');
                $('#editor-region')
                    .animate({
                        'margin-left': '10px'
                }, 300, 'easeOutQuart');
            }
        },

        _setBounds: function(bounds) {
            if (typeof(bounds) == 'undefined') {
                if ($('#modal').is(':visible')) {
                    bounds      = {};
                    bounds.top  = Math.floor($('#modal').offset().top);
                    bounds.left = Math.floor($('#modal').offset().left);
                } else {
                    return false;
                }
            }
            //Save bounds
            localStorage.setItem("codiad.modal.top", bounds.top);
            localStorage.setItem("codiad.modal.left", bounds.left);
        },

        _getBounds: function(width) {
            if (localStorage.getItem("codiad.modal.top") !== null && localStorage.getItem("codiad.modal.left") !== null) {
                var top     = localStorage.getItem('codiad.modal.top'),
                    left    = localStorage.getItem('codiad.modal.left');
                //Check if modal is out of window
                if ((parseInt(top, 10) + 40) > $(window).height()) {
                    top = "15%";
                }
                if ((parseInt(left, 10) + parseInt(width, 10) + 40) > $(window).width()) {
                    left = "50%";
                }
                return {
                    top: top,
                    left: left
                };
            } else {
                return {
                    top: "15%",
                    left: "50%"
                };
            }
        }

    };

})(this, jQuery);

