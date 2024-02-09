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
		const container = el.closest( '.wprm-comment-ratings-container, .wprm-user-ratings-modal-stars' );
		const oldValue = container ? parseInt( container.dataset.currentRating ) : 0;

		const newValue = parseInt( el.value );

		if ( newValue === oldValue ) {
			el.checked = false;
			document.querySelector( 'input[name="' + el.name + '"][value="0"]').checked = true;
			container.dataset.currentRating = 0;
		} else {
			container.dataset.currentRating = newValue;

			// Maybe track via analytics.
			if ( window.WPRecipeMaker.hasOwnProperty( 'analytics' ) ) {
				let recipeId = 0;

				const recipe = document.querySelector( '.wprm-recipe-container' );
				if ( recipe && recipe.dataset.hasOwnProperty( 'recipeId' ) ) {
					recipeId = parseInt( recipe.dataset.recipeId );
				}

				window.WPRecipeMaker.analytics.registerAction( recipeId, wprm_public.post_id, 'comment-rating', { rating: newValue } );
			}
		}

		// Optionally update admin rating.
		if ( window.WPRecipeMaker.hasOwnProperty( 'comments' ) && window.WPRecipeMaker.comments.hasOwnProperty( 'change' ) ) {
			window.WPRecipeMaker.comments.change( container );
		}

		// Trigger stars change event.
		document.dispatchEvent( new CustomEvent( 'wprm-comment-rating-change', { detail: { el, container, rating: newValue } } ) );
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