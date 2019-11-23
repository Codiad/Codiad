(function(global, $) {

	var codiad = global.codiad,
		amplify = global.amplify,
		bioflux = global.bioflux,
		events = global.events;

	//////////////////////////////////////////////////////////////////////
	// Modal
	//////////////////////////////////////////////////////////////////////
	// Notes: 
	// In an effort of removing jquery and saving myself time, I removed
	// persistant modal functions, modal will always load center screen.
	//
	// I will re-add them later when need them
	// Event propagation from the overlay and the interactions within
	// the modal seem to be a bit twisted up. Once I sort out all the
	// plugins & components into a cohesive system, I'll need to clean
	// this mess up.
	//
	// It looks like the modal loader handles event management & even
	// loading the content for each modal, while I know a lot of the 
	// plugins seem to as well, this will have to be optimized.
	//
	// *Sigh* The jquery HTML function for loading the content also
	// executes the script tags contained within, which is a nightmare in
	// my opinion. I think the idea was to containerize each component &
	// only call it when it's loaded. The two options I can think of are
	// create my own version of that function, or load all javascript from
	// the start.
	//												- Liam Siira
	//////////////////////////////////////////////////////////////////////


	codiad.modal = {

		settings: {
			overlay_id: 'modal_overlay',
			wrapper_id: 'modal_wrapper',
			content_id: 'modal_content'
		},

		init: function() {
			console.log('Modal Initialized');
		},

		createModal: function() {
			var overlay = document.createElement('div'),
				modal = document.createElement('div'),
				content = document.createElement('div'),
				drag = document.createElement('i'),
				close = document.createElement('i');

			overlay.id = "modal_overlay";
			overlay.addEventListener('click', function(event) {
				if (event.target.id !== 'modal_overlay') return;
				codiad.modal.unload();
			}, false);

			modal.id = 'modal_wrapper';
			content.id = 'modal_content';

			close.classList.add('icon-cancel');
			close.addEventListener('click', codiad.modal.unload, false);

			drag.classList.add('icon-arrows');
			drag.addEventListener('mousedown', function() {
				codiad.modal.drag(modal);
			}, false);

			modal.appendChild(close);
			modal.appendChild(drag);
			modal.appendChild(content);

			// overlay.appendChild(modal);
			document.body.appendChild(modal);

			document.body.appendChild(overlay);
			return modal;
		},

		load: function(width, url, data) {
			if (data) {
				console.log(data);
			}
			data = data || {};

			var modal = bioflux.queryO('#modal_wrapper') || this.createModal(),
				content = modal.querySelector('#modal_content');
			modal.style.top = '15%';
			modal.style.left = 'calc(50% - ' + (width / 2) + 'px)';
			modal.style.minWidth = width ? width + 'px' : '400px';

			content.innerHTML = '<div id="modal_loading"></div>';

			this.load_process = $.get(url, data, function(data) {
				$(content).html(data);
				// content.innerHTML = data;
				// var script = content.getElementsByTagName('script');
				// if (script) {
				// 	console.log(script);
				// 	console.log('Script Evaled');
				// 	eval(script.innerText);
				// }
				// Fix for Firefox autofocus goofiness
				var input = modal.querySelector('input[autofocus="autofocus"]');
				if (input) input.focus();
			});

			amplify.publish('modal.onLoad', {
				animationPerformed: false
			});

			modal.style.display = 'block';
			bioflux.queryO('#modal_overlay').style.display = 'block';

			// setTimeout(function() {
			// 	modal.classList.add('modal-active');
			// 	document.querySelector("#modal_overlay").classList.add('modal-active');
			// }, 10);

			codiad.sidebars.settings.modalLock = true;
		},

		hideOverlay: function() {
			bioflux.queryO("#modal_overlay").style.display = 'none';
		},
		hide: function() {
			var modal = bioflux.queryO('#modal_wrapper'),
				overlay = bioflux.queryO('#modal_overlay');

			modal.classList.remove('modal-active');
			overlay.classList.remove('modal-active');

			modal.addEventListener("transitionend", function() {
				modal.remove();
				overlay.remove();
			});


			codiad.editor.focus();
			codiad.sidebars.settings.modalLock = false;
		},
		unload: function() {

			$('#modal_content form').die('submit'); // Prevent form bubbling

			amplify.publish('modal.onUnload', {
				animationPerformed: false
			});

			bioflux.queryO('#modal_overlay').style.display = '';
			bioflux.queryO('#modal_wrapper').style.display = '';
			bioflux.queryO('#modal_content').innerHtml = '';

			codiad.editor.focus();
			codiad.sidebars.settings.modalLock = false;

		},
		drag: function(modal) {
			//References: http://jsfiddle.net/8wtq17L8/ & https://jsfiddle.net/tovic/Xcb8d/

			var rect = modal.getBoundingClientRect(),
				mouse_x = window.event.clientX,
				mouse_y = window.event.clientY, // Stores x & y coordinates of the mouse pointer
				modal_x = rect.left,
				modal_y = rect.top; // Stores top, left values (edge) of the element

			function move_element(event) {
				if (modal !== null) {
					modal.style.left = modal_x + event.clientX - mouse_x + 'px';
					modal.style.top = modal_y + event.clientY - mouse_y + 'px';
				}
			}

			// Destroy the object when we are done
			function remove_listeners() {
				document.removeEventListener('mousemove', move_element, false);
				document.removeEventListener('mouseup', remove_listeners, false);
			}

			// document.onmousemove = _move_elem;
			document.addEventListener('mousemove', move_element, false);
			document.addEventListener('mouseup', remove_listeners, false);
		}
	};

})(this, jQuery);