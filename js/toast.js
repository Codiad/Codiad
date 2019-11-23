'use strict';

(function(global) {

	var codiad = global.codiad,
	amplify = global.amplify,
	bioflux = global.bioflux,
	events = global.events;

	//////////////////////////////////////////////////////////////////////
	// User Alerts / Messages
	//////////////////////////////////////////////////////////////////////
	// Notes: 
	// Currently the icons are hard coded: Close/Types. They'll need to be
	// migrated to css classes modifiable by the themes for consistancy.
	//
	// codiad.message needs to be changed to codiad.toast in the future
	//												- Liam Siira
	//////////////////////////////////////////////////////////////////////

	codiad.message = {

		icons: {
			'error': 'exclamation-circle',
			'notice': 'info-circle',
			'success': 'check-circle',
			'warning': 'exclamation-triangle'
		},
		settings: {

			stayTime: 3000,
			text: '',
			sticky: false,
			type: 'info-circle',
			position: 'bottom-right', // top-left, top-center, top-right, middle-left, middle-center, middle-right
			close: null
		},

		init: function(options) {
			if (options) {
				this.settings = codiad.helpers.extend(this.settings, options);
			}
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
			close.classList.add('fas', 'fa-times-circle', 'toast-close');

			wrapper.appendChild(icon);
			wrapper.appendChild(message);
			wrapper.appendChild(close);
			
			close.addEventListener('click', function() {
				codiad.message.hide(wrapper);
			});

			return wrapper;
		},

		showToast: function(options) {
			options = codiad.helpers.extend(this.settings, options);

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
			wrapper.addEventListener("transitionend", function() {
				wrapper.remove();
			});
		}
	};

})(this);