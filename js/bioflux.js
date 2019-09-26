(function(root, factory) {
	if (typeof define === 'function' && define.amd) {
		define([], function() {
			return factory(root);
		});
	} else if (typeof exports === 'object') {
		module.exports = factory(root);
	} else {
		root.bioflux = factory(root);
	}
})(typeof global !== 'undefined' ? global : typeof window !== 'undefined' ? window : this, function(window) {

	'use strict';

	var publicAPIs = {};

	publicAPIs.forEach = function(collection, callback, scope) {
		if (Object.prototype.toString.call(collection) === '[object Object]') {
			for (var prop in collection) {
				if (Object.prototype.hasOwnProperty.call(collection, prop)) {
					callback.call(scope, collection[prop], prop, collection);
				}
			}
		} else {
			for (var i = 0, len = collection.length; i < len; i++) {
				callback.call(scope, collection[i], i, collection);
			}
		}
	};

	publicAPIs.rand = function(m) {
		return m ? ~~(Math.random() * m) : false;
	};

	publicAPIs.getRandItem = function(a) {
		return (Array.isArray(a)) ? a[~~(Math.random() * a.length)] : false;
	};

	publicAPIs.queryO = function(s) {
		return document.querySelector(s);
	};
	publicAPIs.queryA = function(s) {
		return document.querySelectorAll(s);
	};

	publicAPIs.addClass = function(s, c) {
		if (!s) {
			return;
		}
		s = Array.isArray(s) ? s : [s];
		for (let i = 0; i < s.length; i++) {
			s[i].classList.add(cls);
		}
	};
	publicAPIs.removeClass = function(s, c) {
		if (!s) {
			return;
		}
		s = Array.isArray(s) ? s : [s];
		for (let i = 0; i < s.length; i++) {
			s[i].classList.remove(cls);
		}
	};


	publicAPIs.grapple = function(url, options) {
		if (options && !options.method) {
			options = {
				method: 'post',
				body: options
			};
		}
		return fetch(url, options)
			.then(publicAPIs.handleResponse, publicAPIs.handleNetworkError);
	};

	publicAPIs.handleResponse = function(response) {
		if (response.ok) {
			return response.json().then(function(data) {
				return {
					code: response.status,
					json: data
				};
			});
		} else {
			return response.json().then(function(data) {
				return {
					code: response.status,
					json: data
				};
			}).then(function(error) {
				throw error;
			});
		}
	};


	publicAPIs.handleNetworkError = function(error) {
		console.log(error);
		throw {
			msg: error.message
		};
	};


	publicAPIs.serializeObject = function(obj) {

		var o = {};
		var a = this.serializeArray();
		$.each(a, function() {
			if (o[this.name] !== undefined) {
				if (!o[this.name].push) {
					o[this.name] = [o[this.name]];
				}
				o[this.name].push(this.value || '');
			} else {
				o[this.name] = this.value || '';
			}
		});
		return o;
	};

	// Return public APIs
	return publicAPIs;

});