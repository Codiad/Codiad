(function(root, factory) {
	if (typeof define === 'function' && define.amd) {
		define([], function() {
			return factory(root);
		});
	} else if (typeof exports === 'object') {
		module.exports = factory(root);
	} else {
		root.types = factory(root);
	}
})(typeof global !== 'undefined' ? global : typeof window !== 'undefined' ? window : this, function(window) {

	'use strict';

	var publicAPIs = {};

	publicAPIs.isString = function(value) {
		return typeof value === 'string' || value instanceof String;
	};

	publicAPIs.isNumber = function(value) {
		return typeof value === 'number' && isFinite(value);
	};
	publicAPIs.isArray = function(value) {
		return value && typeof value === 'object' && value.constructor === Array;
	};
	publicAPIs.isFunction = function(value) {
		return typeof value === 'function';
	};
	publicAPIs.isObject = function(value) {
		return value && typeof value === 'object' && value.constructor === Object;
	};
	publicAPIs.isNull = function(value) {
		return value === null;
	};
	publicAPIs.isBoolean = function(value) {
		return typeof value === 'boolean';
	};
	publicAPIs.isRegExp = function(value) {
		return value && typeof value === 'object' && value.constructor === RegExp;
	};
	publicAPIs.isError = function(value) {
		return value instanceof Error && typeof value.message !== 'undefined';
	};

	publicAPIs.isDate = function(value) {
		return value instanceof Date;
	};
	publicAPIs.isSymbol = function(value) {
		return typeof value === 'symbol';
	};
	// Return public APIs
	return publicAPIs;

});