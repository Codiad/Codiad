(function(global, $){

    var codiad = global.codiad;

    //////////////////////////////////////////////////////////////////////
    // Parse JSEND Formatted Returns
    //////////////////////////////////////////////////////////////////////

    codiad.jsend = {

        parse: function(d) { // (Data)
            var obj = $.parseJSON(d);
            if (obj.debug !== undefined && Array.isArray(obj.debug)) {
                var debug = obj.debug.join('\nDEBUG: ');
                if(debug !== '') {
                    debug = 'DEBUG: ' + debug;
                }
                console.log(debug);
            }
            if (obj.status == 'error') {
                codiad.message.error(obj.message);
                return 'error';
            } else {
                return obj.data;
            }
        }

    };

})(this, jQuery);
