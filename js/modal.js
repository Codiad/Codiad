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
            var event = {animationPerformed: false};
            amplify.publish('modal.onLoad', event);            
            // If no plugin has provided a custom load animation
            if(!event.animationPerformed) {
                $('#modal, #modal-overlay')
                    .fadeIn(200);
            }
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
            var event = { animationPerformed : false };
            amplify.publish('modal.onUnload', event);
            // If no plugin has provided a custom unload animation
            if(!event.animationPerformed) {
                $('#modal, #modal-overlay')
                    .fadeOut(200);
                $('#modal-content')
                    .html('');
            }
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
            codiad.editor.focus();
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
            if (localStorage.getItem("codiad.modal.top") !== null && localStorage.getItem("codiad.modal.left") !== null && codiad.editor.settings.persistentModal) {
                var top     = parseInt(localStorage.getItem('codiad.modal.top'), 10),
                    left    = parseInt(localStorage.getItem('codiad.modal.left'), 10);
                //Check if modal is out of window
                if ((top + 40) > $(window).height()) {
                    top = "15%";
                } else {
                    top += "px";
                }
                if ((left + width + 40) > $(window).width()) {
                    left = "50%";
                } else {
                    left += Math.ceil(width / 2);
                    left += "px";
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

