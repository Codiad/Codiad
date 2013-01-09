(function(global, $){

    var codiad = global.codiad;

    //////////////////////////////////////////////////////////////////////
    // User Alerts / Messages
    //////////////////////////////////////////////////////////////////////

    codiad.message = {

        init: function() {},

        _showMessage: function(toastType, message, options){
            options = options || {};
            options.text = message;
            options.type = toastType
            $().toastmessage('showToast', options);
        },
        success: function(m, options) {
            this._showMessage('success', m, options);
        },
        error: function(m, options) {
            this._showMessage('error', m, options);
        },
        warning: function(m, options) {
            this._showMessage('warning', m, options);
        },
        notice: function(m, options){
            this._showMessage('notice', m, options);
        },
        hide: function() {
            $(".toast-item-wrapper").remove();
        }
    };

})(this, jQuery);
