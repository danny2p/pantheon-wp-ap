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

function update_progress_bar() {
	var percentage = ( 1.0 - ( importing_recipes.length / importing_recipes_total ) ) * 100;
	jQuery('#wprm-import-progress-bar').css('width', percentage + '%');
};

jQuery(document).ready(function($) {
	// Import Process
	if(window.wprm_import !== undefined) {
		importing_recipes = wprm_import.recipes;
		importing_recipes_total = wprm_import.recipes.length;
		import_recipes();
	}
});
