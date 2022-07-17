import '../../css/public/comment_rating.scss';

window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.rating = {
	init() {
		const ratingFormElem = document.querySelector( '.comment-form-wprm-rating' );

		if ( ratingFormElem ) {
			const recipes = document.querySelectorAll( '.wprm-recipe-container' );
			const admin = document.querySelector( 'body.wp-admin' );

			if ( recipes.length > 0 || admin ) {
				ratingFormElem.style.display = '';
			} else {
				// Hide when no recipe is found.
				ratingFormElem.style.display = 'none';
			}
		}
	},
	settings: {
		enabled: typeof window.wprm_public !== 'undefined' ? wprm_public.settings.features_comment_ratings : wprm_admin.settings.features_comment_ratings,
	},
	onClick( el ) {
		const container = el.closest( '.wprm-comment-ratings-container' );
		const oldValue = container ? parseInt( container.dataset.currentRating ) : 0;

		const newValue = parseInt( el.value );

		if ( newValue === oldValue ) {
			el.checked = false;
			document.querySelector( 'input[name="' + el.name + '"][value="0"]').checked = true;
			container.dataset.currentRating = 0;
		} else {
			container.dataset.currentRating = newValue;
		}

		// Optionally update admin rating.
		if ( window.WPRecipeMaker.hasOwnProperty( 'comments' ) && window.WPRecipeMaker.comments.hasOwnProperty( 'change' ) ) {
			window.WPRecipeMaker.comments.change( container );
		}
	},
};

ready(() => {
	window.WPRecipeMaker.rating.init();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}