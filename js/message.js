(function(global, $){

    var codiad = global.codiad;

    //////////////////////////////////////////////////////////////////////
    // User Alerts / Messages
    //////////////////////////////////////////////////////////////////////

    autoclose = null;

    codiad.message = {

        init: function() {
            // Hide message on click.
            $('#message').click(function(){
                codiad.message.hide();
            });
        },

        success: function(m) { // (Message)
            $('#message')
                .removeClass('error')
                .addClass('success')
                .html(m);
            this.show();
        },

        error: function(m) { // (Message)
            $('#message')
                .removeClass('success')
                .addClass('error')
                .html(m);
            this.show();
        },

        show: function() {
            clearTimeout(autoclose);
            $('#message')
                .fadeIn(300);
            autoclose = setTimeout(function() {
                codiad.message.hide();
            }, 2000);
        },

        hide: function() {
            $('#message')
                .fadeOut(300);
        }
    };

})(this, jQuery);
