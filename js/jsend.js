(function(global, $){

    var codiad = global.codiad;

    //////////////////////////////////////////////////////////////////////
    // Parse JSEND Formatted Returns
    //////////////////////////////////////////////////////////////////////

    codiad.jsend = {

        parse: function(d) { // (Data)
            var obj = $.parseJSON(d);
            if (obj.status == 'error') {
                codiad.message.error(obj.message);
                var errMsg = 'error';
                errMsg.message = obj.message;
                return errMsg;
            } else {
                return obj.data;
            }
        }

    };

})(this, jQuery);