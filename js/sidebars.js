(function(global, $){

    var codiad = global.codiad;

    //////////////////////////////////////////////////////////////////////
    // Workspace Resize
    //////////////////////////////////////////////////////////////////////

    codiad.sidebars = {

        leftLock: true,
        rightLock: false,
        modalLock: false,

        isLeftSidebarOpen: true,
        isRigthSidebarOpen: false,

        init: function() {

            var _this = this;

            $('#lock-left-sidebar')
                .on('click', function() {
                if (_this.leftLock) {

                    _this.leftLock = false;
                    $('#lock-left-sidebar')
                        .removeClass('icon-lock')
                        .addClass('icon-switch');

                } else {

                    _this.leftLock = true;
                    $('#lock-left-sidebar')
                        .removeClass('icon-switch')
                        .addClass('icon-lock');

                }
            });
            
            $('#lock-right-sidebar')
                .on('click', function() {
                if (_this.rightLock) {

                    _this.rightLock = false;
                    $('#lock-right-sidebar')
                        .removeClass('icon-lock')
                        .addClass('icon-switch');

                } else {

                    _this.rightLock = true;
                    $('#lock-right-sidebar')
                        .removeClass('icon-switch')
                        .addClass('icon-lock');

                }
            });

            // Left Column Slider
            $("#sb-left")
                .hoverIntent(function() {
                    var timeout_r = $(this)
                        .data("timeout_r");
                    if (timeout_r) {
                        clearTimeout(timeout_r);
                    }
                    var sbarWidth = $("#sb-left")
                        .width();
                    $('#editor-region')
                        .animate({
                        'margin-left': sbarWidth + 'px',
                        'width': ($('body')
                            .outerWidth() - sbarWidth - 10) + 'px'
                        }, 300, 'easeOutQuart', function(){
                            _this.isLeftSidebarOpen = true;
                            $(this).trigger('h-resize-init');
                            codiad.active.updateTabDropdownVisibility();
                        });
                    $(this)
                        .animate({
                        'left': '0px'
                    }, 300, 'easeOutQuart');
                }, function() {
                    var sbarWidth = $("#sb-left")
                        .width();
                    $(this)
                        .data("timeout_r", setTimeout($.proxy(function() {
                        if (!codiad.sidebars.leftLock && !codiad.sidebars.modalLock) { // Check locks
                            $(this)
                                .animate({
                                'left': (-sbarWidth + 10) + "px"
                            }, 300, 'easeOutQuart');
                            $('#editor-region')
                                .animate({
                                    'margin-left': '10px',
                                    'width': ($('body')
                                        .outerWidth() - 20) + 'px'
                                }, 300, 'easeOutQuart', function(){
                                    _this.isLeftSidebarOpen = false;
                                    $(this).trigger('h-resize-init');
                                    codiad.active.updateTabDropdownVisibility();
                                });
                        }
                    }, this), 500));
                });

            // Right Column Slider
            $("#sb-right")
                .click(function() {
                    if (codiad.editor.settings.rightSidebarTrigger) { // if trigger set to Click
                        var timeout_r = $(this)
                            .data("timeout_r");
                        if (timeout_r) {
                            clearTimeout(timeout_r);
                        }
                        $('#editor-region')
                            .animate({
                                'margin-right': '200px'
                            }, 300, 'easeOutQuart', function(){
                                _this.isRigthSidebarOpen = true;
                            });
                        $(this)
                            .animate({
                                'right': '0px'
                            }, 300, 'easeOutQuart');
                    }
                })
                .hoverIntent(function() {
                    if (!codiad.editor.settings.rightSidebarTrigger) { // if trigger set to Hover
                        var timeout_r = $(this)
                            .data("timeout_r");
                        if (timeout_r) {
                            clearTimeout(timeout_r);
                        }
                        var sbarWidth = $("#sb-right")
                            .width();
                        $('#editor-region')
                            .animate({
                                'margin-right': sbarWidth+'px',
                                'width': ($('#editor-region')
                                            .outerWidth() - sbarWidth + 10) + 'px'
                            }, 300, 'easeOutQuart', function(){
                                _this.isRigthSidebarOpen = true;
                            });
                        $(this)
                            .animate({
                                'right': '0px'
                            }, 300, 'easeOutQuart');
                    }
                }, function() {
                    $(this)
                        .data("timeout_r", setTimeout($.proxy(function() {
                        if (!codiad.sidebars.rightLock) {
                            $(this)
                                .animate({
                                    'right': '-190px'
                                }, 300, 'easeOutQuart');
                            var sbarWidth = $("#sb-left")
                                .width();
                            $('#editor-region')
                                .animate({
                                    'margin-right': '10px',
                                    'width': ($('body')
                                            .outerWidth() - sbarWidth - 10) + 'px'
                                }, 300, 'easeOutQuart', function(){
                                    _this.isRigthSidebarOpen = false;
                                });
                        }
                    }, this), 500));
                });

            $("#sb-left .sidebar-handle")
                .draggable({
                    axis: 'x',
                    drag: function(event, ui) {
                        newWidth = ui.position.left;
                        $("#sb-left")
                            .width(newWidth + 10);
                    },
                    stop: function() {
                        $(window).resize();
                        $('#editor-region')
                            .trigger('h-resize-init');
                    }
                });
        }

    };

})(this, jQuery);
