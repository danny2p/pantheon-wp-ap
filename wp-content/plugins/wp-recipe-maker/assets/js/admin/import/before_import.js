let import_last_checked = false;

jQuery(document).ready(function($) {
	// Search recipes functionality.
	jQuery('#wprm-import-recipes-search').on('click', function(e) {
		e.preventDefault();
		const search = prompt( 'What do you want to search for?' );

		if ( search ) {
			const url = e.target.href;
			window.location = url + '&s=' + encodeURIComponent( search );
		}
	});

	// Quick select functionality.
	jQuery('.wprm-import-recipes-select-all').on('click', function(e) {
		e.preventDefault();
		jQuery('.wprm-import-recipes').find(':checkbox').each(function() {
			jQuery(this).prop('checked', true);
		});
	});
	jQuery('.wprm-import-recipes-select-none').on('click', function(e) {
		e.preventDefault();
		jQuery('.wprm-import-recipes').find(':checkbox').each(function() {
			jQuery(this).prop('checked', false);
		});
	});

	// Select multiple using SHIFT
	jQuery('.wprm-import-recipes').on('click', ':checkbox', function(e) {
		if(import_last_checked && e.shiftKey) {
			var checkboxes = jQuery('.wprm-import-recipes').find(':checkbox'),
				start = checkboxes.index(this),  
				end = checkboxes.index(import_last_checked);

			checkboxes.slice(Math.min(start,end), Math.max(start,end)+ 1).prop('checked', import_last_checked.checked);
		}

		import_last_checked = this;
	});

	// Go to next import page
	jQuery('.wprm-import-next-page').on('click', function() {
		var url = window.location.href,
			regex = /(&|\?)p=(\d+)/,
			match = regex.exec(url);

		if(match) {
			var page = parseInt(match[2]),
				search = 'p=' + page,
				replace = 'p=' + (page+1);
							
			url = url.replace('?' + search, '?' + replace);
			url = url.replace('&' + search, '&' + replace);
		}

		window.location = url;
	});

	// Go back to the first import page
	jQuery('.wprm-import-reset-page').on('click', function() {
		var url = window.location.href,
			regex = /(&|\?)p=(\d+)/,
			match = regex.exec(url);

		if(match) {
			var page = parseInt(match[2]),
				search = 'p=' + page,
				replace = 'p=0';
							
			url = url.replace('?' + search, '?' + replace);
			url = url.replace('&' + search, '&' + replace);
		}

		window.location = url;
	});
});
