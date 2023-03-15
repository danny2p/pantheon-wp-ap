window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.analytics = {
	init: () => {
		if ( wprm_public.settings.analytics_enabled ) {
			document.addEventListener( 'click', function(e) {
				for ( var target = e.target; target && target != this; target = target.parentNode ) {
					if ( window.WPRecipeMaker.analytics.checkClick( target, e ) ) {
						break;
					}
				}
			}, false );
		}
	},
	checkClick: ( target, e ) => {
		if ( target.matches( '.wprm-recipe-jump' ) ) {
			const recipeId = target.dataset.hasOwnProperty( 'recipe' ) ? target.dataset.recipe : false;

			if ( recipeId ) {
				window.WPRecipeMaker.analytics.registerAction( recipeId, wprm_public.post_id, 'jump-to-recipe' );
			}
			return true;
		} else if ( target.matches( '.wprm-recipe-jump-video' ) ) {
			const recipeId = target.dataset.hasOwnProperty( 'recipe' ) ? target.dataset.recipe : false;

			if ( recipeId ) {
				window.WPRecipeMaker.analytics.registerAction( recipeId, wprm_public.post_id, 'jump-to-video' );
			}
			return true;
		} else if ( target.matches( '.wprm-recipe-pin' ) ) {
			const recipeId = target.dataset.hasOwnProperty( 'recipe' ) ? target.dataset.recipe : false;

			if ( recipeId ) {
				window.WPRecipeMaker.analytics.registerAction( recipeId, wprm_public.post_id, 'pin-button' );
			}
			return true;
		} else if ( target.matches( '.wprm-recipe-facebook-share' ) ) {
			const recipeId = target.dataset.hasOwnProperty( 'recipe' ) ? target.dataset.recipe : false;

			if ( recipeId ) {
				window.WPRecipeMaker.analytics.registerAction( recipeId, wprm_public.post_id, 'facebook-share-button' );
			}
			return true;
		} else if ( target.matches( '.wprm-recipe-twitter-share' ) ) {
			const recipeId = target.dataset.hasOwnProperty( 'recipe' ) ? target.dataset.recipe : false;

			if ( recipeId ) {
				window.WPRecipeMaker.analytics.registerAction( recipeId, wprm_public.post_id, 'twitter-share-button' );
			}
			return true;
		} else if ( target.matches( '.wprm-recipe-text-share' ) ) {
			const recipeId = target.dataset.hasOwnProperty( 'recipe' ) ? target.dataset.recipe : false;

			if ( recipeId ) {
				window.WPRecipeMaker.analytics.registerAction( recipeId, wprm_public.post_id, 'text-share-button' );
			}
			return true;
		} else if ( target.matches( '.wprm-recipe-email-share' ) ) {
			const recipeId = target.dataset.hasOwnProperty( 'recipe' ) ? target.dataset.recipe : false;

			if ( recipeId ) {
				window.WPRecipeMaker.analytics.registerAction( recipeId, wprm_public.post_id, 'email-share-button' );
			}
			return true;
		} else if ( target.matches( '.wprm-recipe-add-to-collection-recipe' ) ) {
			const recipeId = target.dataset.hasOwnProperty( 'recipe' ) ? target.dataset.recipeId : false;

			if ( recipeId ) {
				window.WPRecipeMaker.analytics.registerAction( recipeId, wprm_public.post_id, 'add-to-collections-button' );
			}
			return true;
		} else if ( target.matches( '.wprm-recipe-add-to-shopping-list' ) ) {
			const recipeId = target.dataset.hasOwnProperty( 'recipe' ) ? target.dataset.recipeId : false;

			if ( recipeId ) {
				window.WPRecipeMaker.analytics.registerAction( recipeId, wprm_public.post_id, 'add-to-shopping-list-button' );
			}
			return true;
		} else if ( target.matches( '.wprm-recipe-equipment a' ) ) {
			const container = target.closest( '.wprm-recipe-equipment-container' );

			if ( container ) {
				const item = target.closest( '.wprm-recipe-equipment-item' );
				const type = item && item.classList.contains( 'wprm-recipe-equipment-item-has-image' ) ? 'image' : 'text';
				const name = item ? item.querySelector( '.wprm-recipe-equipment-name' ) : target;
				const recipeId = container.dataset.recipe; 

				if ( recipeId ) {
					window.WPRecipeMaker.analytics.registerAction( recipeId, wprm_public.post_id, 'equipment-link', {
						url: target.href,
						type,
						name: name ? name.innerText : 'unknown',
					} );
				}
			}
			return true;
		} else if ( target.matches( '.wprm-recipe-ingredient a' ) ) {
			const container = target.closest( '.wprm-recipe-ingredients-container' );

			// Get ingredient name.
			let name = false;
			const parent = target.closest( '.wprm-recipe-ingredient' );
			if ( parent) {
				name = parent.querySelector( '.wprm-recipe-ingredient-name' );
			}

			if ( container ) {
				const recipeId = container.dataset.recipe; 

				if ( recipeId ) {
					window.WPRecipeMaker.analytics.registerAction( recipeId, wprm_public.post_id, 'ingredient-link', {
						url: target.href,
						name: name ? name.innerText : 'unknown',
					} );
				}
			}
			return true;
		} else if ( target.matches( '.wprm-recipe-instruction a' ) ) {
			const container = target.closest( '.wprm-recipe-instructions-container' );

			if ( container ) {
				const recipeId = container.dataset.recipe; 

				if ( recipeId ) {
					window.WPRecipeMaker.analytics.registerAction( recipeId, wprm_public.post_id, 'instruction-link', {
						url: target.href,
					} );
				}
			}
			return true;
		}

		return false;
	},
	registerAction: ( recipeId, postId, type, meta = {} ) => {		
		if ( wprm_public.settings.analytics_enabled ) {
			fetch( wprm_public.endpoints.analytics, {
				method: 'POST',
				headers: {
					'X-WP-Nonce': wprm_public.api_nonce,
					'Accept': 'application/json',
					'Content-Type': 'application/json',
				},
				credentials: 'same-origin',
				body: JSON.stringify({
					recipeId,
					postId,
					type,
					meta,
					uid: getCookieValue( 'wprm_analytics_visitor' ),
					nonce: wprm_public.nonce,
				}),
			});
		}
	},
	registerActionOnce: ( recipeId, postId, type, meta = {} ) => {		
		if ( wprm_public.settings.analytics_enabled ) {
			if ( window.WPRecipeMaker.analytics.registeredActions.hasOwnProperty( `recipe-${recipeId}` ) && window.WPRecipeMaker.analytics.registeredActions[`recipe-${recipeId}`].hasOwnProperty( type ) ) {
				// Already tracked this action for this recipe on this pageload, ignore.
				return;
			}

			// Track action as already registered for this recipe.
			if ( ! window.WPRecipeMaker.analytics.registeredActions.hasOwnProperty( `recipe-${recipeId}` ) ) {
				window.WPRecipeMaker.analytics.registeredActions[`recipe-${recipeId}`] = {};
			}
			window.WPRecipeMaker.analytics.registeredActions[`recipe-${recipeId}`][ type ] = true;

			// Register action through API.
			fetch( wprm_public.endpoints.analytics, {
				method: 'POST',
				headers: {
					'X-WP-Nonce': wprm_public.api_nonce,
					'Accept': 'application/json',
					'Content-Type': 'application/json',
				},
				credentials: 'same-origin',
				body: JSON.stringify({
					recipeId,
					postId,
					type,
					meta,
					uid: getCookieValue( 'wprm_analytics_visitor' ),
					nonce: wprm_public.nonce,
				}),
			});
		}
	},
	registeredActions: {},
};

ready(() => {
	window.WPRecipeMaker.analytics.init();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}

function getCookieValue( a ) {
    var b = document.cookie.match('(^|;)\\s*' + a + '\\s*=\\s*([^;]+)');
    return b ? b.pop() : '';
}