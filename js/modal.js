(function(global, $) {

	var codiad = global.codiad;

	//////////////////////////////////////////////////////////////////////
	// Modal
	//////////////////////////////////////////////////////////////////////
	// Notes: 
	// In an effort of removing jquery and saving myself time, I removed
	// persistant modal functions, modal will always load center screen.
	// I will re-add them later when need them
	//												- Liam Siira
	//////////////////////////////////////////////////////////////////////


	codiad.modal = {

		init: function() {
			console.log('Modal Initialized');
		},

		load: function(width, url, data) {
			if(data) {console.log(data);}
			data = data || {};

			var modal = document.querySelector('#modal');
			var content = document.querySelector('#modal-content');
			modal.style.top = '15%';
			modal.style.left = 'calc(50% - ' + (width / 2) + 'px)';
			modal.style.minWidth = width ? width + 'px' : '400px';

			modal.querySelector('#drag-handle').addEventListener('mousedown', function() {
				codiad.modal.drag(modal);
			});
			
			content.innerHTML = '<div id="modal-loading"></div>';
			
			this.load_process = $.get(url, data, function(data) {
				content.innerHTML = data;
				// Fix for Firefox autofocus goofiness
				modal.querySelector('input[autofocus="autofocus"]').focus();
			});
			amplify.publish('modal.onLoad', event);
			// If no plugin has provided a custom load animation
			modal.style.display = 'block';
			document.querySelector("#modal-overlay").style.display = 'block';

			codiad.sidebars.modalLock = true;
		},

		hideOverlay: function() {
			document.querySelector("#modal-overlay").style.display = 'none';
		},

		unload: function() {
			$('#modal-content form')
				.die('submit'); // Prevent form bubbling
			var event = {
				animationPerformed: false
			};
			amplify.publish('modal.onUnload', event);
			// If no plugin has provided a custom unload animation
			if (!event.animationPerformed) {
				$('#modal, #modal-overlay')
					.fadeOut(200);
				$('#modal_content')
					.html('');
			}
			codiad.sidebars.modalLock = false;
			if (!codiad.sidebars.leftLock) { // Slide sidebar back
				$('#sb-left')
					.animate({
						'left': '-290px'
					}, 300, 'easeOutQuart');
				$('#editor-region')
					.animate({
						'margin-left': '10px'
					}, 300, 'easeOutQuart');
			}
			codiad.editor.focus();
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