(function(global, $){

    var codiad = global.codiad;

    $(window).load(function() {
        codiad.finder.init();
    });

    codiad.finder = {
        _makeDomNode: function(name, obj){
            var str, path, ext, chStr;
            str = "<li><a";

            if (obj.type === 'directory'){
                str += " class='directory open'";
            } else {
                path = obj.path;
                s = name.split('.');
                str += " class='file";
                if (s.length > 0)
                    str += " ext-"+s[s.length -1];
                str += "' data-path='"+obj.path+"' data-type='"+
                    obj.type +"'";
            }
            str += ">" + name + "</a>";
            chStr = "";
            for (key in obj.children){
                chStr += this._makeDomNode(key, obj.children[key]);
            }
            if (chStr.length > 0){
                str += "<ul>"+chStr +"</ul>";
            }

            str += "</li>";
            return str;
        },
        _makeDomTree: function(tree){
            var str = "<ul>";
            for (key in tree){
                str += this._makeDomNode(key, tree[key]);
            }
            str += "</ul>";
            console.debug("DOM tree :", str);
            return str;
        },
        _makeHierarchy: function(data){
            data = data.index;
            console.log('data : ', data);
            var tree = {}, fpathArr, i, j, fragment, curLevel;
            for (i = 0; i < data.length; i++){
                curLevel = tree;
                fpathArr = data[i].path.split('/');
                for (j = 0; j < fpathArr.length; j++){
                    fragment = fpathArr[j];
                    if (fragment === "") continue;
                    if (! curLevel[fragment]){
                        curLevel[fragment] = {
                            type: j < fpathArr.length -1 ? 'directory' : data[i].type,
                            children: {}
                        }
                        if (data[i].type == 'file'){
                            curLevel[fragment].path = data[i].path;
                        }
                    }
                    curLevel = curLevel[fragment].children;
                }
            }
            console.log('tree : ', tree, JSON.stringify(tree));
            return tree;
        },
        _filterTree: function(data){
            var tree = this._makeHierarchy(data);
            var domTree = this._makeDomTree(tree);
            $('#file-manager').html(domTree);
            $('#file-manager>ul>li:first-child>a').attr({
                id: 'project-root',
                'data-path': this._rootPath
            });
        },
        _clearFilters: function(){
            console.info("Reloading initial tree state ");
            if (this._htmlStash)
                $('#file-manager').html(this._htmlStash);
            this._htmlStash = null;
            $('#finder').attr('value', '');
        },
        _emptyTree: function(){
            $('#file-manager').html("No files found .");
        },
        _checkFinder: function(){
            var fentry = $('#finder').attr('value');
            var _this = this;
            fentry = fentry.replace(/^\s+|\s+$/g, '');
            if (fentry && fentry != this._finderLastEntry){
                console.log("Finder query changed");
                this._finderLastEntry = fentry;
                $.ajax({
                    url: 'components/filemanager/controller.php',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        query: fentry,
                        action: 'find',
                        path: this._rootPath,
                        options: this._finderOptions
                    },
                    success: function(data){
                        if (data.status == 'success'){
                            _this._filterTree(data.data);
                        } else {
                            _this._emptyTree();
                        }
                    }
                });
            }
        },
        _expandFinder: function(){
            this._isFinderExpanded = true;
            console.info("Saving tree state : ");
            this._htmlStash = $('#file-manager').html();
            this._rootPath = $('#project-root').attr('data-path');
            $("#finder-wrapper").show('slow');
            $("#sb-left-title h2").hide('slow');
            var _this = this;
            this._finderLastEntry = null;
            this._finderPoller = setInterval(function(){
                _this._checkFinder();
            }, 500);
            $("#finder").focus();
        },
        _contractFinder: function(){
            this._isFinderExpanded = false;
            $("#finder-wrapper").hide('fast');
            $("#sb-left-title h2").show('fast');
            clearInterval(this._finderPoller);
            this.finderMenu.hide();
            this._clearFilters();
        },
        init: function(){
            var _this = this;
            var isExpanded = false;
            this._finderOptions = {};
            $('#tree-search').click(function(){
                $(this).toggleClass('active');
                if (! _this._isFinderExpanded) {
                    _this._expandFinder();
                } else {
                    _this._contractFinder();
                }
            });

            this.finderMenu = finderMenu = $('#finder-options-menu')
                .appendTo($('#sb-left'))
                .hide();

            $finderOptionsMenu = $('#finder-options-menu');

            $('#finder-options').click(function(){
                finderMenu.toggle();
            });
            $finderOptionsMenu.bind('click', 'a', function(e){
                $target = $(e.target);
                _this._finderOptions.strategy = $target.attr('data-option');
                $finderOptionsMenu
                    .find('li.chosen')
                    .removeClass('chosen');
                $target.parent('li').addClass('chosen');
                $finderOptionsMenu.hide();
            });
            /*

              TODO: provide configuration option
              to automatically collapse finder
              --
              The code below does exactly that
              --

            $('#sb-left').mouseleave(function(){
                _this.finderSustainFocus = false;
                if (! $('#finder').is(':focus')){
                    _this._contractFinder();
                }
            }).mouseenter(function(){
                _this.finderSustainFocus = true;
            });
            $('#finder').blur(function(){
                if (! _this.finderSustainFocus)
                    _this._contractFinder();
            });

            */
        }
    }
})(this, jQuery);
