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
        isRightSidebarOpen: false,

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
                    var sbarWidthL = $("#sb-left")
                        .width(),
                        sbarWidthR = $("#sb-right")
                        .width();
                    if (!codiad.sidebars.rightLock) {
                        sbarWidthR = 10;
                    }
                    $('#editor-region')
                        .animate({
                        'margin-left': sbarWidthL + 'px'
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
                    var sbarWidthL = $("#sb-left")
                        .width(),
                        sbarWidthR = $("#sb-right")
                        .width();
                        if (!codiad.sidebars.rightLock) {
                            sbarWidthR = 10;
                        }
                    $(this)
                        .data("timeout_r", setTimeout($.proxy(function() {
                        if (!codiad.sidebars.leftLock && !codiad.sidebars.modalLock) { // Check locks
                            $(this)
                                .animate({
                                'left': (-sbarWidthL + 10) + "px"
                            }, 300, 'easeOutQuart');
                            $('#editor-region')
                                .animate({
                                    'margin-left': '10px'
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
                        var sbarWidthR = $("#sb-right")
                            .width(),
                            sbarWidthL = $("#sb-left")
                            .width();
                        $('#editor-region')
                            .animate({
                                'margin-right': sbarWidthR+'px'
                            }, 300, 'easeOutQuart', function(){
                                _this.isRightSidebarOpen = true;
                                codiad.active.updateTabDropdownVisibility();
                            });
                        $('#tab-close')
                            .animate({
                                'margin-right': (sbarWidthR-10)+'px'
                            }, 300, 'easeOutQuart');
                        $('#tab-dropdown')
                            .animate({
                                'margin-right': (sbarWidthR-10)+'px'
                            }, 300, 'easeOutQuart');
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
                        var sbarWidthR = $("#sb-right")
                            .width(),
                            sbarWidthL = $("#sb-left")
                            .width();
                        if (!codiad.sidebars.leftLock) {
                            sbarWidthL = 10;
                        }
                        $('#editor-region')
                            .animate({
                                'margin-right': sbarWidthR+'px'
                            }, 300, 'easeOutQuart', function(){
                                _this.isRightSidebarOpen = true;
                                codiad.active.updateTabDropdownVisibility();
                            });
                        $('#tab-close')
                            .animate({
                                'margin-right': (sbarWidthR-10)+'px'
                            }, 300, 'easeOutQuart');
                        $('#tab-dropdown')
                            .animate({
                                'margin-right': (sbarWidthR-10)+'px'
                            }, 300, 'easeOutQuart');
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
                            var sbarWidthL = $("#sb-left")
                                .width(),
                                sbarWidthR = $("#sb-right")
                                .width();
                            if (!codiad.sidebars.leftLock) {
                                sbarWidthL = 10;
                            }
                            $('#editor-region')
                                .animate({
                                    'margin-right': '10px'
                                }, 300, 'easeOutQuart', function(){
                                    _this.isRightSidebarOpen = false;
                                    codiad.active.updateTabDropdownVisibility();
                                });
                            $('#tab-close')
                                .animate({
                                    'margin-right': 0+'px'
                                }, 300, 'easeOutQuart');
							$('#tab-dropdown')
                                .animate({
                                    'margin-right': 0+'px'
                                }, 300, 'easeOutQuart');
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
