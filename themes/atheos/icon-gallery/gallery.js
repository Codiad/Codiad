document.addEventListener("DOMContentLoaded", function() {
	const $ = function(s) {
		return document.querySelector(s);
	};
	const $$ = function(s) {
		return document.querySelectorAll(s);
	};
	var initialText = $('#icon-details').textContent,
		counter = 0,
		iconLock = false;

	const search = function(e) {
		var query = this.value.trim().replace(/\-/gi, ' ').toLowerCase(),
			matches = [],
			result = initialText,
			qnt = 4;

		var icons = $$('.icon');

		if (query) {
			// Determine matches

			for (i = 0; i < icons.length; ++i) {
				let icon = icons[i];
				try {
					if (icon.dataset.match.match(query)) {
						icon.classList.remove("inactive");
						matches.push(icon.dataset.name);
					} else {
						icon.classList.add("inactive");
					}

				} catch (e) {
					// Suppress RegExp errors
				}
			}

			// Interpolate result message
			if (matches.length > qnt) {
				var rem = matches.length - qnt,
					plural = rem > 1;

				result = matches.slice(0, qnt).join(', ') + ' and ' +
					rem + ' other' + (plural ? 's' : '');
			} else if (matches.length > 0) {
				result = matches.join(', ');
			} else {
				result = 'No results found for "' + query + '". Try "media", "weather" or "arrow".';
			}
		} else {
			for (i = 0; i < icons.length; ++i) {
				let icon = icons[i];
				icon.classList.remove("inactive");
			}			
		}
		$('#icon-details').innerHTML = result;
	};

	$('#icon-search').addEventListener('keyup', search);
	$('#icon-search').addEventListener('focus', search);
	$('#icon-search').addEventListener('blur', search);

	$('#icon-gallery').addEventListener('mouseout', function(e) {
		// $('#icon-details').innerHTML = initialText;
	});
	
	const pushIcon = function() {
		let icon = this;
		if(!iconLock) {
			$('#icon-details').innerHTML = '<i class="typcn typcn-' + icon.dataset.name + '"></i>\n<span class="icon-name">' + icon.dataset.name + '</span>\n<small class="icon-code">' + icon.dataset.code + "</small>";
		}
	};
	
	const lockIcon = function() {
		let icon = this;
		if ($('.locked')) { $('.locked').classList.remove('locked');}
		$('#icon-details').innerHTML = '<i class="typcn typcn-' + icon.dataset.name + '"></i>\n<span class="icon-name">' + icon.dataset.name + '</span>\n<small class="icon-code">' + icon.dataset.code + "</small>";
		icon.classList.add('locked');
		iconLock = true;
	};	

	var icons = $$('.icon');
	for (i = 0; i < icons.length; ++i) {
		let icon = icons[i];
		icons[i].addEventListener('mouseover', pushIcon);
		icons[i].addEventListener('click', lockIcon);
	}


	// if (isTouch) {
	// 	$('#preview aside').html('Tap on an icon to enlarge');
	// }
});