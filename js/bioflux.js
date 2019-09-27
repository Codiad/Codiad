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

	publicAPIs.addClass = function(selector, cls) {
		try {
			if (!selector || !cls) {
				throw "Required parameter missing";
			}
			selector = Array.isArray(selector) ? selector : [selector];
			for (let i = 0; i < selector.length; i++) {
				if (typeof selector === 'string') {
					publicAPIs.queryO(selector[i]).classList.add(cls);
				} else {
					selector[i].classList.add(cls);
				}
			}
		} catch (err) {
			publicAPIs.log(err, 'error');
		}
	};
	
	publicAPIs.removeClass = function(selector, cls) {
		try {
			if (!selector || !cls) {
				throw "Required parameter missing";
			}
			selector = Array.isArray(selector) ? selector : [selector];
			for (let i = 0; i < selector.length; i++) {
				if (typeof selector === 'string') {
					publicAPIs.queryO(selector[i]).classList.remove(cls);
				} else {
					selector[i].classList.remove(cls);
				}
			}
		} catch (err) {
			publicAPIs.log(err, 'error');
		}
	};

	publicAPIs.replaceClass = function(selector, cls, newcls) {
		try {
			if (!selector || !cls || !newcls) {
				throw "Required parameter missing";
			}
			selector = Array.isArray(selector) ? selector : [selector];
			for (let i = 0; i < selector.length; i++) {
				if (typeof selector === 'string') {
					publicAPIs.queryO(selector[i]).classList.remove(cls);
					publicAPIs.queryO(selector[i]).classList.add(newcls);
				} else {
					selector[i].classList.remove(cls);
					selector[i].classList.add(newcls);
				}
			}
		} catch (err) {
			publicAPIs.log(err, 'error');
		}
	};

	publicAPIs.log = function(msg, type) {
		if (type === 'error') {
			console.error(msg);
		} else {
			console.log(msg);
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

	publicAPIs.trigger = function(s, e) {
		if (!e || !s) return;
		if (s.nodeType === Node.ELEMENT_NODE) {
			s.dispatchEvent(new Event(e));
		} else if (typeof s === 'string') {
			publicAPIs.queryO(s).dispatchEvent(new Event(e));
		}
	};

	// Return public APIs
	return publicAPIs;

});