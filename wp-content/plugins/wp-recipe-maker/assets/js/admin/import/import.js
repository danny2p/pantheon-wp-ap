let importing_recipes = [];
let importing_recipes_total = 0;

function import_recipes() {
	var data = {
		action: 'wprm_import_recipes',
		security: wprm_admin.nonce,
		importer_uid: wprm_import.importer_uid,
		post_data: wprm_import.post_data,
		recipes: importing_recipes
	};

	jQuery.post(wprm_admin.ajax_url, data, function(out) {
		if (out.success) {
			importing_recipes = out.data.recipes_left;
			update_progress_bar();

			if(importing_recipes.length > 0) {
				import_recipes();
			} else {
				jQuery('#wprm-import-finished').show();
			}
		} else {
			window.location = out.data.redirect;
		}
	}, 'json');
}

function getContrastClass(color) {
	var ctx = document.createElement('canvas').getContext('2d');
	ctx.fillStyle = color;
	var hex = ctx.fillStyle;
	var r = parseInt(hex.slice(1, 3), 16);
	var g = parseInt(hex.slice(3, 5), 16);
	var b = parseInt(hex.slice(5, 7), 16);
	var luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
	return luminance > 0.5 ? 'wprm-progress-percentage-dark' : 'wprm-progress-percentage-light';
}

function update_progress_bar() {
	var percentage = ( 1.0 - ( importing_recipes.length / importing_recipes_total ) ) * 100;
	var isComplete = percentage >= 100;
	jQuery('#wprm-import-progress-bar').css('width', percentage + '%').toggleClass('wprm-progress-complete', isComplete);

	var label = jQuery('#wprm-import-progress-container .wprm-progress-percentage');
	label.text(isComplete ? '100%' : percentage.toFixed(1) + '%');
	label.removeClass('wprm-progress-percentage-light wprm-progress-percentage-dark wprm-progress-percentage-complete');

	if (isComplete) {
		label.addClass('wprm-progress-percentage-complete');
	} else if (percentage >= 50) {
		var color = getComputedStyle(document.documentElement).getPropertyValue('--wp-admin-theme-color').trim() || '#3858e9';
		label.addClass(getContrastClass(color));
	}
};

jQuery(document).ready(function($) {
	// Import Process
	if(window.wprm_import !== undefined) {
		importing_recipes = wprm_import.recipes;
		importing_recipes_total = wprm_import.recipes.length;
		import_recipes();
	}
});
