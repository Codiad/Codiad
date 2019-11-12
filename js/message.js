(function(global) {

	var codiad = global.codiad;

	//////////////////////////////////////////////////////////////////////
	// User Alerts / Messages
	//////////////////////////////////////////////////////////////////////

	codiad.message = {

		icons: {
			'error': 'exclamation-circle',
			'notice': 'info-circle',
			'success': 'check-circle',
			'warning': 'exclamation-triangle'
		},
		settings: {

			stayTime: 3000, // time in miliseconds before the item has to disappear
			text: '', // content of the item. Might be a string or a jQuery object. Be aware that any jQuery object which is acting as a message will be deleted when the toast is fading away.
			sticky: false, // should the toast item sticky or not?
			type: 'info-circle', // notice, warning, error, success
			position: 'bottom-right', // top-left, top-center, top-right, middle-left, middle-center, middle-right ... Position of the toast container holding different toast. Position can be set only once at the very first call, changing the position after the first call does nothing
			close: null // callback function when the toastmessage is closed
		},

		init: function(options) {
			if (options) {
				this.setings = this.extend(this.settings, options);
			}
		},

		extend: function(obj, src) {
			for (var key in src) {
				if (src.hasOwnProperty(key)) obj[key] = src[key];
			}
			return obj;
		},

		createContainer: function() {
			var container = document.createElement('div');
			container.id = 'toast-container';
			container.classList.add('toast-position-' + this.settings.position);
			document.body.appendChild(container);
			return container;
		},

		createToast: function(text, type) {
			var wrapper = document.createElement('div'),
				message = document.createElement('p'),
				icon = document.createElement('i'),
				close = document.createElement('i');

			wrapper.classList.add('toast-wrapper');
			message.classList.add('toast-message');
			message.innerText = text || 'Default Text';

			icon.classList.add('fas', 'fa-' + type, 'toast-icon');
			close.classList.add('far', 'fa-times-circle', 'toast-close');

			wrapper.appendChild(icon);
			wrapper.appendChild(message);
			wrapper.appendChild(close);

			close.addEventListener('click', function() {
				codiad.message.hide(wrapper);
			});

			return wrapper;
		},

		showToast: function(options) {
			options = this.extend(this.settings, options);

			// declare variables
			var container = document.querySelector('#toast-container') || this.createContainer(),
				wrapper = this.createToast(options.text, options.type);

			container.appendChild(wrapper);

			setTimeout(function() {
				wrapper.classList.add('toast-active');

				if (!options.sticky) {
					setTimeout(function() {
						codiad.message.hide(wrapper);
					}, options.stayTime);
				}
			}, 10);


			return wrapper;
		},

		success: function(message, options) {
			options = (options && typeof options === 'object') ? options : {};
			options.text = message || 'Message undefined';
			options.type = this.icons.success;
			this.showToast(options);
		},
		error: function(message, options) {
			options = (options && typeof options === 'object') ? options : {};
			options.text = message || 'Message undefined';
			options.type = this.icons.error;
			options.stayTime = 10000;
			this.showToast(options);
		},
		warning: function(message, options) {
			options = (options && typeof options === 'object') ? options : {};
			options.text = message || 'Message undefined';
			options.type = this.icons.warning;
			options.stayTime = 5000;
			this.showToast(options);
		},
		notice: function(message, options) {
			options = (options && typeof options === 'object') ? options : {};
			options.text = message || 'Message undefined';
			options.type = this.icons.notice;
			this.showToast(options);
		},
		hide: function(wrapper) {
			wrapper.classList.remove('toast-active');
			wrapper.addEventListener("animationend", function() {
				wrapper.remove();
			});
		}
	};

})(this);