jQuery(document).ready(function($) {
	// Edit imported recipe
	jQuery(document).on('click', '.wprm-import-recipes-actions-edit', function(e) {
		e.preventDefault();

		const recipeId = jQuery(this).data('id');
        WPRM_Modal.open( 'recipe', {
            recipeId,
        } );
	});
});