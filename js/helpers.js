'use strict';

(function(global) {

	var codiad = global.codiad;

	//////////////////////////////////////////////////////////////////////
	// Codiad Specific Helper functions
	//////////////////////////////////////////////////////////////////////
	// Notes: 
	// This helper module is potentially temporary, but will be used to
	// help identify and reduce code repition across the application.
	// Think of it like a temporary garbage dump of all functions that
	// don't fit within the actual module I found them in.
	//
	// If any of these become long term solutions, more research will need
	// to take place on each function to ensure it does what it says. Most
	// of these were just pulled from a google search and kept if they 
	// seemed to work.
	//												- Liam Siira
	//////////////////////////////////////////////////////////////////////

	codiad.helpers = {

		icons: {},
		settings: {},

		init: function(options) {
			if (options) {
				this.setings = this.extend(this.settings, options);
			}
		},

		//////////////////////////////////////////////////////////////////////
		// Extend used in:
		//   Toast.js
		//////////////////////////////////////////////////////////////////////

		extend: function(obj, src) {
			for (var key in src) {
				if (src.hasOwnProperty(key)) obj[key] = src[key];
			}
			return obj;
		},

		//////////////////////////////////////////////////////////////////////
		// Trigger used in:
		//   Sidebars.js
		//////////////////////////////////////////////////////////////////////
		trigger: function(selector, event) {
			if (!event || !selector) return;
			var element;
			if (selector.self == window) {
				element = selector;
			} else {
				element = selector.nodeType === Node.ELEMENT_NODE ? selector : document.querySelector(selector);
			}
			if (element) {
				if ('createEvent' in document) {
					// modern browsers, IE9+
					var e = document.createEvent('HTMLEvents');
					e.initEvent(event, false, true);
					element.dispatchEvent(e);
				} else {
					// IE 8
					var e = document.createEventObject();
					e.eventType = event;
					el.fireEvent('on' + e.eventType, e);
				}
			}

			// if (selector.nodeType === Node.ELEMENT_NODE) {
			// 	selector.dispatchEvent(new Event(event));
			// } else if (typeof s === 'string') {
			// 	var element = document.querySelector(selector);
			// 	if (element) {
			// 		element.dispatchEvent(new Event(event));
			// 	}
			// }
		}
	};

})(this);