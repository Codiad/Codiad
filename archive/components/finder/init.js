(function(global, $){

    var codiad = global.codiad;

    $(window).load(function() {
        codiad.finder.init();
    });

    //////////////////////////////////////////////////////////////////
    //
    //  Search utility to quickly filter the directory tree
    //  according to multiple matching strategies
    //
    //////////////////////////////////////////////////////////////////

    codiad.finder = {

        // Create DOM node for a particular tree element
        _makeDomNode: function(name, obj){
            var str, path, ext, chStr;
            str = "<li><span class=\"none\"></span><a";

            if (obj.type === 'directory'){
                str += " class='directory open'";
            } else {
                s = name.split('.');
                str += " class='file";
                if (s.length > 0)
                    str += " ext-"+s[s.length -1];
                str += "'";
            }
            str += " data-path='"+obj.path+"' data-type='"+
                obj.type +"' >" + name + "</a>";
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

        // Construct DOM tree from internal data-structure representing
        // the filtered directory tree
        _makeDomTree: function(tree){
            var str = "<ul>";
            for (key in tree){
                str += this._makeDomNode(key, tree[key]);
            }
            str += "</ul>";
            //console.debug("DOM tree :", str);
            return str;
        },

        // Construct internal representation for filtered directory tree
        // from array returned by server
        _makeHierarchy: function(data){
            data = data.index;
            //console.log('data : ', data);
            var tree = {}, fpathArr, i, j, fragment, curLevel, type;
            for (i = 0; i < data.length; i++){
                curLevel = tree;
                if(codiad.project.isAbsPath(data[i].path)) {
                    fpathArr = data[i].path.replace(this._rootPath, this._rootName).split('/');
                } else {
                    fpathArr = data[i].path.split('/');
                }
                for (j = 0; j < fpathArr.length; j++){
                    fragment = fpathArr[j];
                    if (fragment === "") continue;
                    if (! curLevel[fragment]){
                        type = j < fpathArr.length -1 ? 'directory' : data[i].type;
                        curLevel[fragment] = {
                            type: type,
                            children: {}
                        }
                        if (type === 'file'){
                            curLevel[fragment].path = data[i].path;
                        } else {
                            if(codiad.project.isAbsPath(data[i].path)) {
                                curLevel[fragment].path = fpathArr.slice(0, j+1).join('/').replace(this._rootName, this._rootPath);
                            } else {
                                curLevel[fragment].path = fpathArr.slice(0, j+1).join('/');
                            }
                        }
                    }
                    curLevel = curLevel[fragment].children;
                }
            }
            //console.log('tree : ', tree, JSON.stringify(tree));
            return tree;
        },

        // Use query response returned by server to filter the directory tree
        _filterTree: function(data){
            var tree = this._makeHierarchy(data);
            var domTree = this._makeDomTree(tree);
            $('#file-manager').html(domTree);
            $('#file-manager>ul>li:first-child>span').remove();
            $('#file-manager>ul>li:first-child>a').attr({
                id: 'project-root',
                'data-type': 'root',
                'data-path': this._rootPath
            });
        },

        // Clear all filters applied and restore the tree to its original state
        _clearFilters: function(){
            //console.info("Reloading initial tree state ");
            if (this._htmlStash)
                $('#file-manager').html(this._htmlStash);
            this._htmlStash = null;
            $('#finder').attr('value', '');
        },

        // Empty the tree and notify that no files were found
        _emptyTree: function(){
            $('#file-manager').html("No files found .");
        },

        // Check finder for changes in the user entered query
        _checkFinder: function(){
            var fentry = $('#finder').attr('value');
            var _this = this;
            fentry = fentry.replace(/^\s+|\s+$/g, '');
            if (! fentry || fentry == this._lastEntry) return;
            /*else if (fentry.substring(0, this._lastEntry.length) ===
                     this._lastEntry) {

                // TODO : Scope for optimization
                //
                // User has added characters to query - so unless the
                // query is a regexp - the filtered results can be
                // deduced locally if last ajax request had completed.

                // Not implementing this currently because this is
                // not very beneficial practically for decent
                // typing speed.

            }*/ else{
                // Stop currently ongoing request
                if (this._xhr) this._xhr.abort();

                // Query the server for results
                //console.log("Finder query changed");
                this._lastEntry = fentry;
                this._xhr = $.ajax({
                    url: 'components/filemanager/controller.php',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        query: fentry,
                        action: 'find',
                        path: this._rootPath,
                        options: this._options
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

        //////////////////////////////////////////////////////////////////
        //
        // Expand the finder box (Textbox through which the directory tree
        // can be searched for matching files) and focus on it.
        //
        //////////////////////////////////////////////////////////////////

        expandFinder: function(){
            this._isFinderExpanded = true;
            //console.info("Saving tree state : ");
            this._htmlStash = $('#file-manager').html();
            this._rootPath = $('#project-root').attr('data-path');
            this._rootName = $('#project-root').html();
            $("#finder-wrapper").show();
            $("#sb-left-title h2").hide();
            var _this = this;
            this._lastEntry = null;
            this._poller = setInterval(function(){
                _this._checkFinder();
            }, 500);
            $("#finder").focus();
            $("#finder-quick").hide();
            $("#sb-left-title").addClass('active');
            $("#tree-search")
                .removeClass('icon-search')
                .addClass('icon-cancel-squared active');
        },

        // Contract the finder box
        contractFinder: function(){
            this._isFinderExpanded = false;
            $("#finder-wrapper").hide('fast');
            $("#sb-left-title h2").show('fast');
            clearInterval(this._poller);
            this.finderMenu.hide();
            this._clearFilters();
            $("#finder-quick").show();
            $("#sb-left-title").removeClass('active');
            $("#tree-search")
                .removeClass('icon-cancel-squared active')
                .addClass('icon-search');
        },

        // Setup finder
        init: function(){
            var _this = this;
            var isExpanded = false;
            this._options = {};
            $('#tree-search').click(function(){
                if (! _this._isFinderExpanded) {
                    _this.expandFinder();
                } else {
                    _this.contractFinder();
                }
            });

            $('#finder-label').click(function(){
                _this.expandFinder();
            });

            this.finderMenu = finderMenu = $('#finder-options-menu')
                .appendTo($('#sb-left'))
                .hide();

            $finderOptionsMenu = $('#finder-options-menu');

            $('#finder-options').click(function(){
                finderMenu.toggle();
            });

            // Setup the menu for selection of finding strategy
            $finderOptionsMenu.bind('click', 'a', function(e){
                $target = $(e.target);
                var strategy = $target.attr('data-option');
                var action = $target.attr('data-action');
                if (strategy){
                    _this._options.strategy = strategy;
                    $finderOptionsMenu
                        .find('li.chosen')
                        .removeClass('chosen');
                    $target.parent('li').addClass('chosen');
                } else if (action){
                    codiad.filemanager[action](codiad.project.getCurrent());
                    _this.contractFinder();
                }
                $finderOptionsMenu.hide();
            });
            
            // Setup the menu for selection of finding strategy
            $('#finder-quick').click(function(e){
                codiad.filemanager['search'](codiad.project.getCurrent());
                _this.contractFinder();
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
                    _this.contractFinder();
                }
            }).mouseenter(function(){
                _this.finderSustainFocus = true;
            });
            $('#finder').blur(function(){
                if (! _this.finderSustainFocus)
                    _this.contractFinder();
            });

            */
        }
    }
})(this, jQuery);
