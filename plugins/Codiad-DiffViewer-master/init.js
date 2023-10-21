/*
 *  Copyright (c) Codiad & daeks, distributed
 *  as-is and without warranty under the MIT License. See
 *  [root]/license.txt for more. This information must remain intact.
 */

(function(global, $){

    var codiad = global.codiad,
        scripts = document.getElementsByTagName('script'),
        path = scripts[scripts.length-1].src.split('?')[0],
        curpath = path.split('/').slice(0, -1).join('/')+'/';

    var first;

    $(function() {
        codiad.diff.init();
    });

    codiad.diff = {
    
        dialog: curpath + 'dialog.php',

        init: function() {
        },
        
        select: function(path) {
            if(typeof(this.first) !== 'undefined') {
                this.compare(this.first,path);
                this.first = undefined;
            } else {
                this.first = path;
                codiad.message.success('First File selected');
            }
        },
                
        compare: function(first,second) {
            codiad.modal.load(850, this.dialog + '?action=compare&first='+first+'&second='+second);
        }
        
    };

})(this, jQuery);
