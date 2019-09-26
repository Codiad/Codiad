(function (root, factory) {
	if ( typeof define === 'function' && define.amd ) {
		define([], function () {
			return factory(root);
		});
	} else if ( typeof exports === 'object' ) {
		module.exports = factory(root);
	} else {
		root.events = factory(root);
	}
})(typeof global !== 'undefined' ? global : typeof window !== 'undefined' ? window : this, function (window) {

	'use strict';

	//
	// Variables
	//

	var publicAPIs = {};
	var activeEvents = {};


	//
	// Methods
	//

	/**
	 * Get the index for the listener
	 * @param  {Array}   arr      The listeners for an event
	 * @param  {Array}   listener The listener details
	 * @return {Integer}          The index of the listener
	 */
	var getIndex = function (arr, selector, callback) {
		for (var i = 0; i < arr.length; i++) {
			if (
				arr[i].selector === selector &&
				arr[i].callback.toString() === callback.toString()
			) return i;
		}
		return -1;
	};

	/**
	 * Check if the listener callback should run or not
	 * @param  {Node}         target   The event.target
	 * @param  {String|Node}  selector The selector to check the target against
	 * @return {Boolean}               If true, run listener
	 */
	var doRun = function (target, selector) {
		if ([
			'*',
			'window',
			'document',
			'document.documentElement',
			window,
			document,
			document.documentElement
		].indexOf(selector) > -1) return true;
		if (typeof selector !== 'string' && selector.contains) {
			return selector === target || selector.contains(target);
		}
		return target.closest(selector);
	};

	/**
	 * Handle listeners after event fires
	 * @param {Event} event The event
	 */
	var eventHandler = function (event) {
		if (!activeEvents[event.type]) return;
		activeEvents[event.type].forEach(function (listener) {
			if (!doRun(event.target, listener.selector)) return;
			listener.callback(event);
		});
	};

	/**
	 * Add an event
	 * @param  {String}   types    The event type or types (comma separated)
	 * @param  {String}   selector The selector to run the event on
	 * @param  {Function} callback The function to run when the event fires
	 */
	publicAPIs.on = function (types, selector, callback) {

		// Make sure there's a selector and callback
		if (!selector || !callback) return;

		// Loop through each event type
		types.split(',').forEach(function (type) {

			// Remove whitespace
			type = type.trim();

			// If no event of this type yet, setup
			if (!activeEvents[type]) {
				activeEvents[type] = [];
				window.addEventListener(type, eventHandler, true);
			}

			// Push to active events
			activeEvents[type].push({
				selector: selector,
				callback: callback
			});

		});

	};

	/**
	 * Remove an event
	 * @param  {String}   types    The event type or types (comma separated)
	 * @param  {String}   selector The selector to remove the event from
	 * @param  {Function} callback The function to remove
	 */
	publicAPIs.off = function (types, selector, callback) {

		// Loop through each event type
		types.split(',').forEach(function (type) {

			// Remove whitespace
			type = type.trim();

			// if event type doesn't exist, bail
			if (!activeEvents[type]) return;

			// If it's the last event of it's type, remove entirely
			if (activeEvents[type].length < 2 || !selector) {
				delete activeEvents[type];
				window.removeEventListener(type, eventHandler, true);
				return;
			}

			// Otherwise, remove event
			var index = getIndex(activeEvents[type], selector, callback);
			if (index < 0) return;
			activeEvents[type].splice(index, 1);

		});

	};

	/**
	 * Add an event, and automatically remove it after it's first run
	 * @param  {String}   types    The event type or types (comma separated)
	 * @param  {String}   selector The selector to run the event on
	 * @param  {Function} callback The function to run when the event fires
	 */
	publicAPIs.once = function (types, selector, callback) {
		publicAPIs.on(types, selector, function temp (event) {
			callback(event);
			publicAPIs.off(types, selector, temp);
		});
	};

	/**
	 * Get an immutable copy of all active event listeners
	 * @return {Object} Active event listeners
	 */
	publicAPIs.get = function () {
		var obj = {};
		for (var type in activeEvents) {
			if (activeEvents.hasOwnProperty(type)) {
				obj[type] = activeEvents[type];
			}
		}
		return obj;
	};


	//
	// Return public APIs
	//

	return publicAPIs;

});