
 (function (global, $) {

    var codiad = global.codiad;

    $(window)
        .load(function() {
            codiad.zencoding.init();
        });

    codiad.zencoding = {

        controller: 'components/ZenPHP/controller.php',

        //////////////////////////////////////////////////////////////////
        // Initilization
        //////////////////////////////////////////////////////////////////

        init: function () {
            var _this = this;
            $.get(_this.controller);
        },

        //////////////////////////////////////////////////////////////////
        // Convert Text to HTML/CSS Block
        //////////////////////////////////////////////////////////////////

        convert: function () {
			alert("Clicked");
            var _this = this;
            
        }
    };

})(this, jQuery);
