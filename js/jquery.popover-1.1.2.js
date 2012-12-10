/**
 * jQuery.popover plugin v1.1.2
 * By Davey IJzermans
 * See http://wp.me/p12l3P-gT for details
 * http://daveyyzermans.nl/
 * 
 * Released under MIT License.
 */

;(function($) {
	//define some default plugin options
	var defaults = {
		verticalOffset: 10, //offset the popover by y px vertically (movement depends on position of popover. If position == 'bottom', positive numbers are down)
		horizontalOffset: 10, //offset the popover by x px horizontally (movement depends on position of popover. If position == 'right', positive numbers are right)
		title: false, //heading, false for none
		content: false, //content of the popover
		url: false, //set to an url to load content via ajax
		classes: '', //classes to give the popover, i.e. normal, wider or large
		position: 'auto', //where should the popover be placed? Auto, top, right, bottom, left or absolute (i.e. { top: 4 }, { left: 4 })
		fadeSpeed: 160, //how fast to fade out popovers when destroying or hiding
		trigger: 'click', //how to trigger the popover: click, hover or manual
		preventDefault: true, //preventDefault actions on the element on which the popover is called
		stopChildrenPropagation: true, //prevent propagation on popover children
		hideOnHTMLClick: true, //hides the popover when clicked outside of it
		animateChange: true, //animate a popover reposition
		autoReposition: true, //automatically reposition popover on popover change and window resize
		anchor: false //anchor the popover to a different element
	}
	var popovers = [];
	var _ = {
		calc_position: function(popover, position) {
			var data = popover.popover("getData");
			var options = data.options;
			var $anchor = options.anchor ? $(options.anchor) : popover;
			var el = data.popover;
			
			var coordinates = $anchor.offset();
			var y1, x1;
			
			if (position == 'top') {
				y1 = coordinates.top - el.outerHeight();
				x1 = coordinates.left - el.outerWidth() / 2 + $anchor.outerWidth() / 2;
			} else if (position == 'right') {
				y1 = coordinates.top + $anchor.outerHeight() / 2 - el.outerHeight() / 2;
				x1 = coordinates.left	+ $anchor.outerWidth();
			} else if (position == 'left') {
				y1 = coordinates.top + $anchor.outerHeight() / 2 - el.outerHeight() / 2;
				x1 = coordinates.left	- el.outerWidth();
			} else {
				//bottom
				y1 = coordinates.top + $anchor.outerHeight();
				x1 = coordinates.left - el.outerWidth() / 2 + $anchor.outerWidth() / 2;
			}
			
			x2 = x1 + el.outerWidth();
			y2 = y1 + el.outerHeight();
			ret = {
				x1: x1,
				x2: x2,
				y1: y1,
				y2: y2
			};
			
			return ret;
		},
		pop_position_class: function(popover, position) {
			var remove = "popover-top popover-right popover-left";
			var arrow = "top-arrow"
			var arrow_remove = "right-arrow bottom-arrow left-arrow";
			
			if (position == 'top') {
				remove = "popover-right popover-bottom popover-left";
				arrow = 'bottom-arrow';
				arrow_remove = "top-arrow right-arrow left-arrow";
			} else if (position == 'right') {
				remove = "popover-yop popover-bottom popover-left";
				arrow = 'left-arrow';
				arrow_remove = "top-arrow right-arrow bottom-arrow";
			} else if (position == 'left') {
				remove = "popover-top popover-right popover-bottom";
				arrow = 'right-arrow';
				arrow_remove = "top-arrow bottom-arrow left-arrow";
			}
			
			popover
				.removeClass(remove)
				.addClass('popover-' + position)
				.find('.arrow')
					.removeClass(arrow_remove)
					.addClass(arrow);
		}
	};
	var methods = {
		/**
		 * Initialization method
		 * Merges parameters with defaults, makes the popover and saves data
		 * 
		 * @param object
		 * @return jQuery
		 */
		init : function(params) {
			return this.each(function() {
				var options = $.extend({}, defaults, params);
				
				var $this = $(this);
				var data = $this.popover('getData');
				
				if ( ! data) {
					var popover = $('<div class="popover" />')
						.addClass(options.classes)
						.append('<div class="arrow" />')
						.append('<div class="wrap"></div>')
						.appendTo('body')
						.hide();
					
					if (options.stopChildrenPropagation) {
						popover.children().bind('click.popover', function(event) {
							event.stopPropagation();
						});
					}
					
					if (options.anchor) {
						if ( ! options.anchor instanceof jQuery) {
							options.anchor = $(options.anchor);
						}
					}
					
					var data = {
						target: $this,
						popover: popover,
						options: options
					};
					
					if (options.title) {
						$('<div class="title" />')
							.html(options.title instanceof jQuery ? options.title.html() : options.title)
							.appendTo(popover.find('.wrap'));
					}
					if (options.content) {
						$('<div class="content" />')
							.html(options.content instanceof jQuery ? options.content.html() : options.content)
							.appendTo(popover.find('.wrap'));
					}

					$this.data('popover', data);
					popovers.push($this);
					
					if (options.url) {
						$this.popover('ajax', options.url);
					}
					
					$this.popover('reposition');
					$this.popover('setTrigger', options.trigger);
					
					if (options.hideOnHTMLClick) {
						var hideEvent = "click.popover";
						if ("ontouchstart" in document.documentElement)
							hideEvent = 'touchstart.popover';
						$('html').unbind(hideEvent).bind(hideEvent, function(event) {
							$('html').popover('fadeOutAll');
						});
					}
					
					if (options.autoReposition) {
						var repos_function = function(event) {
							$this.popover('reposition');
						};
						$(window)
							.unbind('resize.popover').bind('resize.popover', repos_function)
							.unbind('scroll.popover').bind('scroll.popover', repos_function);
					}
				}
			});
		},
		/**
		 * Reposition the popover
		 * 
		 * @return jQuery
		 */
		reposition: function() {
			return this.each(function() {
				var $this = $(this);
				var data = $this.popover('getData');
				
				if (data) {
					var popover = data.popover;
					var options = data.options;
					var $anchor = options.anchor ? $(options.anchor) : $this;
					var coordinates = $anchor.offset();
					
					var position = options.position;
					if ( ! (position == 'top' || position == 'right' || position == 'left' || position == 'auto')) {
						position = 'bottom';
					}
					var calc;
					
					if (position == 'auto') {
						var positions = ["bottom", "left", "top", "right"];
						var scrollTop = $(window).scrollTop();
						var scrollLeft = $(window).scrollLeft();
						var windowHeight = $(window).outerHeight();
						var windowWidth = $(window).outerWidth();
						
						$.each (positions, function(i, pos) {
							calc = _.calc_position($this, pos);
							
							var x1 = calc.x1 - scrollLeft;
							var x2 = calc.x2 - scrollLeft + options.horizontalOffset;
							var y1 = calc.y1 - scrollTop;
							var y2 = calc.y2 - scrollTop + options.verticalOffset;
							
							if (x1 < 0 || x2 < 0 || y1 < 0 || y2 < 0)
								//popover is left off of the screen or above it
								return true; //continue
							
							if (y2 > windowHeight)
								//popover is under the window viewport
								return true; //continue
							
							if (x2 > windowWidth)
								//popover is right off of the screen
								return true; //continue
							
							position = pos;
							return false;
						});
						
						if (position == 'auto') {
							//position is still auto
							return;
						}
					}
					
					calc = _.calc_position($this, position);
					var top = calc.top;
					var left = calc.left;
					_.pop_position_class(popover, position);
					
					var marginTop = 0;
					var marginLeft = 0;
					if (position == 'bottom') {
						marginTop = options.verticalOffset;
					}
					if (position == 'top') {
						marginTop = -options.verticalOffset;
					}
					if (position == 'right') {
						marginLeft = options.horizontalOffset;
					}
					if (position == 'left') {
						marginLeft = -options.horizontalOffset;
					}
					
					var css = {
						left: calc.x1,
						top: calc.y1,
						marginTop: marginTop,
						marginLeft: marginLeft
					};
					
					if (data.initd && options.animateChange) {
						popover.css(css);
					} else {
						data.initd = true;
						popover.css(css);
					}
					$this.data('popover', data);
				}
			});
		},
		/**
		 * Remove a popover from the DOM and clean up data associated with it.
		 * 
		 * @return jQuery
		 */
		destroy: function() {
			return this.each(function() {
				var $this = $(this);
				var data = $this.popover('getData');
				
				$this.unbind('.popover');
				$(window).unbind('.popover');
				data.popover.remove();
				$this.removeData('popover');
			});
		},
		/**
		 * Show the popover
		 * 
		 * @return jQuery
		 */
		show: function() {
			return this.each(function() {
				var $this = $(this);
				var data = $this.popover('getData');
				
				if (data) {
					var popover = data.popover;
					$this.popover('reposition');
					popover.clearQueue().css({ zIndex: 950 }).show();
				}
			});
		},
		/**
		 * Hide the popover
		 * 
		 * @return jQuery
		 */
		hide: function() {
			return this.each(function() {
				var $this = $(this);
				var data = $this.popover('getData');
				
				if (data) {
					data.popover.hide().css({ zIndex: 949 });
				}
			});
		},
		/**
		 * Fade out the popover
		 * 
		 * @return jQuery
		 */
		fadeOut: function(ms) {
			return this.each(function() {
				var $this = $(this);
				var data = $this.popover('getData');
				
				if (data) {
					var popover = data.popover;
					var options = data.options;
					popover.delay(100).css({ zIndex: 949 }).fadeOut(ms ? ms : options.fadeSpeed);
				}
			});
		},
		/**
		 * Hide all popovers
		 * 
		 * @return jQuery
		 */
		hideAll: function() {
			return $.each (popovers, function(i, pop) {
				var $this = $(this);
				var data = $this.popover('getData');
				
				if (data) {
					var popover = data.popover;
					popover.hide();
				}
			});
		},
		/**
		 * Fade out all popovers
		 * 
		 * @param int
		 * @return jQuery
		 */
		fadeOutAll: function(ms) {
			return $.each (popovers, function(i, pop) {
				var $this = $(this);
				var data = $this.popover('getData');
				
				if (data) {
					var popover = data.popover;
					var options = data.options;
					popover.css({ zIndex: 949 }).fadeOut(ms ? ms : options.fadeSpeed);
				}
			});
		},
		/**
		 * Set the event trigger for the popover. Also cleans the previous binding. 
		 * 
		 * @param string
		 * @return jQuery
		 */
		setTrigger: function(trigger) {
			return this.each(function() {
				var $this = $(this);
				var data = $this.popover('getData');
				
				if (data) {
					var popover = data.popover;
					var options = data.options;
					var $anchor = options.anchor ? $(options.anchor) : $this;
					
					if (trigger === 'click') {
						$anchor.unbind('click.popover').bind('click.popover', function(event) {
							if (options.preventDefault) {
								event.preventDefault();
							}
							event.stopPropagation();
							$this.popover('show');
						});
						popover.unbind('click.popover').bind('click.popover', function(event) {
							event.stopPropagation();
						});
					} else {
						$anchor.unbind('click.popover');
						popover.unbind('click.popover')
					}
					
					if (trigger === 'hover') {
						$anchor.add(popover).bind('mousemove.popover', function(event) {
							$this.popover('show');
						});
						$anchor.add(popover).bind('mouseleave.popover', function(event) {
							$this.popover('fadeOut');
						});
					} else {
						$anchor.add(popover).unbind('mousemove.popover').unbind('mouseleave.popover');
					}
					
					if (trigger === 'focus') {
						$anchor.add(popover).bind('focus.popover', function(event) {
							$this.popover('show');
						});
						$anchor.add(popover).bind('blur.popover', function(event) {
							$this.popover('fadeOut');
						});
						$anchor.bind('click.popover', function(event) {
							event.stopPropagation();
						});
					} else {
						$anchor.add(popover).unbind('focus.popover').unbind('blur.popover').unbind('click.popover');
					}
				}
			});
		},
		/**
		 * Rename the popover's title
		 * 
		 * @param string
		 * @return jQuery
		 */
		title: function(text) {
			return this.each(function() {
				var $this = $(this);
				var data = $this.popover('getData');
				
				if (data) {
					var title = data.popover.find('.title');
					var wrap = data.popover.find('.wrap');
					if (title.length === 0) {
						title = $('<div class="title" />').appendTo(wrap);
					}
					title.html(text);
				}
			});
		},
		/**
		 * Set the popover's content
		 * 
		 * @param html
		 * @return jQuery
		 */
		content: function(html) {
			return this.each(function() {
				var $this = $(this);
				var data = $this.popover('getData');
				
				if (data) {
					var content = data.popover.find('.content');
					var wrap = data.popover.find('.wrap');
					if (content.length === 0) {
						content = $('<div class="content" />').appendTo(wrap);
					}
					content.html(html);
				}
			});
		},
		/**
		 * Read content with AJAX and set popover's content.
		 * 
		 * @param string
		 * @param object
		 * @return jQuery
		 */
		ajax: function(url, ajax_params) {
			return this.each(function() {
				var $this = $(this);
				var data = $this.popover('getData');
				
				if (data) {
					var ajax_defaults = {
						url: url,
						success: function(ajax_data) {
							var content = data.popover.find('.content');
							var wrap = data.popover.find('.wrap');
							if (content.length === 0) {
								content = $('<div class="content" />').appendTo(wrap);
							}
							content.html(ajax_data);
						}
					}
					var ajax_options = $.extend({}, ajax_defaults, ajax_params);
					$.ajax(ajax_options);
				}
			});
		},
		setOption: function(option, value) {
			return this.each(function() {
				var $this = $(this);
				var data = $this.popover('getData');
				
				if (data) {
					data.options[option] = value;
					$this.data('popover', data);
				}
			});
		},
		getData: function() {
			var ret = [];
			this.each(function() {
				var $this = $(this);
				var data = $this.data('popover');
				
				if (data) ret.push(data);
			});
			
			if (ret.length == 0) {
				return;
			}
			if (ret.length == 1) {
				ret = ret[0];
			}
			return ret;
		}
	};

	$.fn.popover = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if ( typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.popover');
		}
	}
})(jQuery);
