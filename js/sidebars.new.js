'use strict';

(function(global, $) {

	var core = global.codiad,
		amplify = global.amplify,
		bioflux = global.bioflux,
		events = global.events,
		hoverintent = global.hoverintent;

	//////////////////////////////////////////////////////////////////////
	// Sidebar
	//////////////////////////////////////////////////////////////////////
	// Notes: 
	//
	// Sidebar module currently called from:
	//	Modal.js
	//												- Liam Siira
	//////////////////////////////////////////////////////////////////////		

	core.sidebars = {
		settings: {
			leftLock: true,
			rightLock: false,
			modalLock: false,
			isLeftSidebarOpen: true,
			isRightSidebarOpen: false
		},
		IDs: {
			sbarLeft: '#sb-left',
			sbarRight: '#sb-right',
			sbarLockLeft: '#lock-left-sidebar',
			sbarLockRight: '#lock-right-sidebar',
		},
		//////////////////////////////////////////////////////////////////////
		// Hold-Over functions: 
		// Originally, all settigns were a part of the main object, I moved
		// them into a settings obj, and the 5 following functions need to be
		// tracked down on their callers and edited.
		// 
		// Sidebar module currently called from:
		//	Modal.js
		//												- Liam Siira
		//////////////////////////////////////////////////////////////////////	
		leftLock: function() {
			console.trace('LeftLock');
			return this.settings.leftLock;
		},
		rightLock: function() {
			console.trace('RightLock');
			return this.settings.rightLock;
		},
		modalLock: function() {
			console.trace('ModalLock');
			return this.settings.modalLock;
		},
		isLeftSidebarOpen: function() {
			console.trace('LeftSidebarOpen');
			return this.settings.isLeftSidebarOpen;
		},
		isRightSidebarOpen: function() {
			console.trace('RightSidebarOpen');
			return this.settings.isRightSidebarOpen;
		},
		//////////////////////////////////////////////////////////////////////	

		init: function() {
			amplify.subscribe('settings.loaded', function(settings) {
				var sbWidth = localStorage.getItem('codiad.sidebars.sb-left-width');

				if (sbWidth !== null) {
					bioflux.queryO(this.IDs.sbarLeft).style.width = sbWidth;
					core.helpers.trigger(window, 'resize');
					core.helpers.trigger('#editor-region', 'h-resize-init');
				}

				if (localStorage.getItem('codiad.sidebars.lock-left-sidebar') === "false") {
					core.helpers.trigger(this.IDs.sbarLockLeft, 'click');
					this.closeLeftSidebar();
				}

				if (localStorage.getItem('codiad.sidebars.lock-right-sidebar') === "true") {
					core.helpers.trigger(this.IDs.sbarLockRight, 'click');
					this.openRightSidebar();
				}
			});

			//////////////////////////////////////////////////////////////////////	
			// Left Sidebar Initialization
			//////////////////////////////////////////////////////////////////////	

			events.on('click', this.IDs.sbarLockLeft, function(e) {
				var icon = e.target || e.srcElement;
				if (core.sidebars.settings.leftLock) {
					bioflux.replaceClass(icon, 'icon-lock', 'icon-lock-open');
				} else {
					bioflux.replaceClass(icon, 'icon-lock-open', 'icon-lock');
				}
				core.sidebars.settings.leftLock = !(core.sidebars.settings.leftLock);
				localStorage.setItem('codiad.sidebars.lock-left-sidebar', core.sidebars.settings.leftLock);
			});

			events.on('mousedown', this.IDs.sbarLeft + ' .sidebar-handle', function(e) {
				core.sidebars.drag(bioflux.queryO(core.sidebars.IDs.sbarLeft));
			});

			hoverintent(bioflux.queryO(this.IDs.sbarLeft), this.openLeftSidebar, this.closeLeftSidebar);

			//////////////////////////////////////////////////////////////////////	
			// Right Sidebar Initialization
			//////////////////////////////////////////////////////////////////////	

			events.on('click', this.IDs.sbarLockRight, function(e) {
				var icon = e.target || e.srcElement;
				if (core.sidebars.settings.rightLock) {
					bioflux.replaceClass(icon, 'icon-lock', 'icon-lock-open');
				} else {
					bioflux.replaceClass(icon, 'icon-lock-open', 'icon-lock');
				}
				core.sidebars.settings.rightLock = !(core.sidebars.settings.rightLock);
				localStorage.setItem('codiad.sidebars.lock-right-sidebar', core.sidebars.settings.rightLock);
			});

			events.on('click', this.IDs.sbarRight + ' .sidebar-handle', function() {
				if (core.editor.settings.rightSidebarTrigger) { // if trigger set to Click
					core.sidebars.openRightSidebar();
				}
			});

			hoverintent(bioflux.queryO(this.IDs.sbarRight), function() {
				if (!core.editor.settings.rightSidebarTrigger) { // if trigger set to Hover
					core.sidebars.openRightSidebar();
				}
			}, function() {
				setTimeout(function() {
					if (!core.sidebars.settings.rightLock) {
						core.sidebars.closeRightSidebar();
					}
				}, 500);
			});
		},

		closeLeftSidebar: function() {
			var sidebars = core.sidebars;

			var sbarLeft = bioflux.queryO('#sb-left'),
				sbarRight = bioflux.queryO('#sb-left'),
				sbarWidthL = sbarLeft.clientWidth,
				sbarWidthR = sbarRight.clientWidth;

			if (!sidebars.settings.rightLock) {
				sbarWidthR = 10;
			}
			$('#sb-left')
				.data("timeout_r", setTimeout($.proxy(function() {
					if (!sidebars.settings.leftLock && !sidebars.settings.modalLock) { // Check locks
						$('#sb-left')
							.animate({
								'left': (-sbarWidthL + 10) + "px"
							}, 300, 'easeOutQuart');
						$('#editor-region')
							.animate({
								'margin-left': '10px'
							}, 300, 'easeOutQuart', function() {
								sidebars.settings.isLeftSidebarOpen = false;
								$('#sb-left').trigger('h-resize-init');
								core.active.updateTabDropdownVisibility();
							});
					} else {
						if ($("#sb-left .sidebar-handle").position().left <= 0) {
							$("#sb-left").width(10);
							$("#sb-left")
								.animate({
									'left': "0px"
								}, 300, 'easeOutQuart');
							$("#sb-left .sidebar-handle").css("left", 0);
							$('#editor-region')
								.animate({
									'margin-left': '10px'
								}, 300, 'easeOutQuart', function() {
									sidebars.settings.isLeftSidebarOpen = false;
									$(this).trigger('h-resize-init');
									core.active.updateTabDropdownVisibility();
								});
						}
					}
				}, this), 500));
		},
		openLeftSidebar: function() {
			var _this = core.sidebars;
			var timeout_r = $('#sb-left')
				.data("timeout_r");
			if (timeout_r) {
				clearTimeout(timeout_r);
			}
			var sbarWidthL = $("#sb-left")
				.width(),
				sbarWidthR = $("#sb-right")
				.width();
			if (!core.sidebars.settings.rightLock) {
				sbarWidthR = 10;
			}
			$('#editor-region')
				.animate({
					'margin-left': sbarWidthL + 'px'
				}, 300, 'easeOutQuart', function() {
					_this.settings.isLeftSidebarOpen = true;
					$('#sb-left').trigger('h-resize-init');
					core.active.updateTabDropdownVisibility();
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
					'right': '-' + (sbarWidthR - 10) + 'px'
				}, 300, 'easeOutQuart');
			var sbarWidthL = $("#sb-left")
				.width(),
				sbarWidthR = $("#sb-right")
				.width();
			if (!core.sidebars.settings.leftLock) {
				sbarWidthL = 10;
			}
			$('#editor-region')
				.animate({
					'margin-right': '0px'
				}, 300, 'easeOutQuart', function() {
					_this.settings.isRightSidebarOpen = false;
					core.active.updateTabDropdownVisibility();
				});
			$('#tab-close')
				.animate({
					'margin-right': 0 + 'px'
				}, 300, 'easeOutQuart');
			$('#tab-dropdown')
				.animate({
					'margin-right': 0 + 'px'
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
			if (!core.sidebars.settings.leftLock) {
				sbarWidthL = 10;
			}
			$('#editor-region').css('margin-right', '0px');
			$('#editor-region')
				.animate({
					'margin-right': sbarWidthR - 10 + 'px'
				}, 300, 'easeOutQuart', function() {
					_this.settings.isRightSidebarOpen = true;
					core.active.updateTabDropdownVisibility();
				});
			$('#tab-close')
				.animate({
					'margin-right': (sbarWidthR - 10) + 'px'
				}, 300, 'easeOutQuart');
			$('#tab-dropdown')
				.animate({
					'margin-right': (sbarWidthR - 10) + 'px'
				}, 300, 'easeOutQuart');
			$('#sb-right')
				.animate({
					'right': '0px'
				}, 300, 'easeOutQuart');
		},
		drag: function(sidebar) {
			//References: http://jsfiddle.net/8wtq17L8/ & https://jsfiddle.net/tovic/Xcb8d/

			var rect = sidebar.getBoundingClientRect(),
				mouse_x = window.event.clientX,
				modal_x = rect.left;

			function move_element(event) {
				if (sidebar !== null) {
					sidebar.style.width = (modal_x + event.clientX + 10) + 'px';
				}
			}

			// Destroy the object when we are done
			function remove_listeners() {
				core.helpers.trigger(window, 'resize');
				core.helpers.trigger('#editor-region', 'h-resize-init');
				// $(window).resize();
				// $('editor-region').trigger('h-resize-init');

				localStorage.setItem('codiad.sidebars.sb-left-width', bioflux.queryO('#sb-left').style.width);

				document.removeEventListener('mousemove', move_element, false);
				document.removeEventListener('mouseup', remove_listeners, false);
			}

			// document.onmousemove = _move_elem;
			document.addEventListener('mousemove', move_element, false);
			document.addEventListener('mouseup', remove_listeners, false);
		}
	};

})(this, jQuery);