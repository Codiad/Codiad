(function(global, $){

    var codiad = global.codiad;

    //////////////////////////////////////////////////////////////////////
    // Mobile
    //////////////////////////////////////////////////////////////////////

    codiad.mobile = {

        isTouchDevice: false,

        init: function() {
            var _this = this;
            _this.isTouchDevice = ('ontouchstart' in window || 'onmsgesturechange' in window);
        }
        
    };

})(this, jQuery);

