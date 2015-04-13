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

            amplify.subscribe('settings.loaded', function(settings){
                var sbWidth = localStorage.getItem('codiad.sidebars.sb-left-width');

                if (sbWidth !== null) {
                    $('#sb-left').width(sbWidth);
                    $(window).resize();
                    $('#editor-region').trigger('h-resize-init');
                }

                if (localStorage.getItem('codiad.sidebars.lock-left-sidebar') === "false") {
                    $('#lock-left-sidebar').trigger('click');
                    _this.closeLeftSidebar();
                }

                if (localStorage.getItem('codiad.sidebars.lock-right-sidebar') === "true") {
                    $('#lock-right-sidebar').trigger('click');
                    _this.openRightSidebar();
                }
            });

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

                localStorage.setItem('codiad.sidebars.lock-left-sidebar', _this.leftLock);
            });
            
            $('#lock-right-sidebar')
                .on('click', function() {
                if (_this.rightLock) {

                    _this.rightLock = false;
                    $('#lock-right-sidebar')
                        .removeClass('icon-lock')
                        .addClass('icon-switch');
                    //Push cursor position rightwards
                    $('#cursor-position').css("right", "30px");

                } else {

                    _this.rightLock = true;
                    $('#lock-right-sidebar')
                        .removeClass('icon-switch')
                        .addClass('icon-lock');
                    //Push cursor position leftwards
                    $('#cursor-position').css("right", "220px");

                }

                localStorage.setItem('codiad.sidebars.lock-right-sidebar', _this.rightLock);
            });

            // Left Column Slider
            $("#sb-left").hoverIntent(_this.openLeftSidebar, _this.closeLeftSidebar);

            // Right Column Slider
            $("#sb-right")
                .click(function() {
                    if (codiad.editor.settings.rightSidebarTrigger) { // if trigger set to Click
                        _this.openRightSidebar();
                    }
                })
                .hoverIntent(function() {
                    if (!codiad.editor.settings.rightSidebarTrigger) { // if trigger set to Hover
                        _this.openRightSidebar();
                    }
                }, function() {
                    $(this)
                        .data("timeout_r", setTimeout($.proxy(function() {
                        if (!codiad.sidebars.rightLock) {
                            _this.closeRightSidebar();
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
                        localStorage.setItem('codiad.sidebars.sb-left-width', $('#sb-left').width());
                    }
                });
        },

        closeLeftSidebar: function() {
            var _this = this;
            var sbarWidthL = $("#sb-left")
                .width(),
                sbarWidthR = $("#sb-right")
                .width();
                if (!codiad.sidebars.rightLock) {
                    sbarWidthR = 10;
                }
            $('#sb-left')
                .data("timeout_r", setTimeout($.proxy(function() {
                if (!codiad.sidebars.leftLock && !codiad.sidebars.modalLock) { // Check locks
                    $('#sb-left')
                        .animate({
                        'left': (-sbarWidthL + 10) + "px"
                    }, 300, 'easeOutQuart');
                    $('#editor-region')
                        .animate({
                            'margin-left': '10px'
                        }, 300, 'easeOutQuart', function(){
                            _this.isLeftSidebarOpen = false;
                            $('#sb-left').trigger('h-resize-init');
                            codiad.active.updateTabDropdownVisibility();
                        });
                } else {
                    if($("#sb-left .sidebar-handle").position().left <= 0) {
                        $("#sb-left").width(10);
                        $("#sb-left")
                            .animate({
                            'left': "0px"
                            }, 300, 'easeOutQuart');
                        $("#sb-left .sidebar-handle").css("left",0);
                        $('#editor-region')
                            .animate({
                                'margin-left': '10px'
                            }, 300, 'easeOutQuart', function(){
                                _this.isLeftSidebarOpen = false;
                                $(this).trigger('h-resize-init');
                                codiad.active.updateTabDropdownVisibility();
                            });
                    }
                }
            }, this), 500));
        },

        openLeftSidebar: function() {
            var _this = this;
            var timeout_r = $('#sb-left')
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
                    $('#sb-left').trigger('h-resize-init');
                    codiad.active.updateTabDropdownVisibility();
                });
            $('#sb-left')
                .animate({
                'left': '0px'
            }, 300, 'easeOutQuart');
        },

        closeRightSidebar: function() {
			var _this = this;
            var sbarWidthR = $("#sb-right").width();
			$('#sb-right')
                .animate({
                    'right': '-'+(sbarWidthR-10)+'px'
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
                    'margin-right': '0px'
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
        },

        openRightSidebar: function() {
			var _this = this;
			var timeout_r = $('#sb-right')
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
            $('#editor-region').css('margin-right','0px');
            $('#editor-region')
                .animate({
                    'margin-right': sbarWidthR-10+'px'
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
            $('#sb-right')
                .animate({
                    'right': '0px'
                }, 300, 'easeOutQuart');
        }

    };

})(this, jQuery);
