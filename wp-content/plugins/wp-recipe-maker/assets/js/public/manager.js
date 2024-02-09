window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.manager = {
	init: () => {
	},
	recipes: {},
	getRecipe: ( id ) => {
		id = parseInt( id );

		if ( ! window.WPRecipeMaker.manager.recipes.hasOwnProperty( `recipe-${id}` ) ) {
			return window.WPRecipeMaker.manager.loadRecipe( id );
		}

		return Promise.resolve( window.WPRecipeMaker.manager.recipes[ `recipe-${id}` ] );
	},
	loadRecipe: ( id ) => {
		return new Promise((resolveRecipe, rejectRecipe) => {
			// Check for data added in PHP of try loading through API.
			const getRecipeData = new Promise((resolveData, rejectData) => {
				if ( window.hasOwnProperty( 'wprm_recipes' ) && window.wprm_recipes.hasOwnProperty( `recipe-${id}` ) ) {
					resolveData( window.wprm_recipes[ `recipe-${id}` ] );
				} else {
					window.WPRecipeMaker.manager.loadRecipeDataFromAPI( id ).then( ( result ) => {
						resolveData( result );
					});
				}
			});

			getRecipeData.then( ( recipeData ) => {
				// Create recipe object from recipe data.
				let recipe = false;

				if ( recipeData ) {
					recipe = window.WPRecipeMaker.manager.loadRecipeObject( id, recipeData );
				}

				window.WPRecipeMaker.manager.recipes[ `recipe-${id}` ] = recipe;
				resolveRecipe( recipe );
			});
		});
	},
	loadRecipeDataFromAPI: ( id ) => {
		let headers = {
			'Accept': 'application/json',
			'Content-Type': 'application/json',
		};

		// Only require nonce when logged in to prevent caching problems for regular visitors.
		if ( 0 < parseInt( wprmp_public.user ) ) {
			headers['X-WP-Nonce'] = wprm_public.api_nonce;
		}

		return new Promise((resolve, reject) => {
			fetch(`${wprm_public.endpoints.manage}/recipe/${ id }`, {
				method: 'POST',
				headers,
				credentials: 'same-origin',
				body: JSON.stringify({
					format: 'frontend',
				}),
			}).then( (response) => {
				if ( response.ok ) {
					return response.json();
				}
				return false;
			}).then( ( result ) => {
				resolve( result );
			});
		});
	},
	loadRecipeObject: ( id, data ) => {
		let recipe = {
			id,
			data,
			// Functions to be overwritten. Define here to prevent errors.
			setServings: ( ...args ) => {},
			setAdvancedServings: ( ...args ) => {},
			setUnitSystem: ( ...args ) => {},
			addRating: ( ...args ) => {},
		}

		// Check if premium is active and should add functionality.
		if ( window.WPRecipeMaker.hasOwnProperty( 'managerPremium' ) ) {
			recipe = window.WPRecipeMaker.managerPremium.loadRecipeObject( id, recipe );
		}

		return recipe;
	},
	triggerChangeEvent: ( id, type ) => {
		document.dispatchEvent( new CustomEvent( 'wprm-recipe-change', { detail: { id, type } } ) );
	},
	changeRecipeData: ( id, data ) => {
		if ( window.WPRecipeMaker.manager.recipes.hasOwnProperty( `recipe-${id}` ) ) {
			window.WPRecipeMaker.manager.recipes[ `recipe-${id}` ].data = {
				...window.WPRecipeMaker.manager.recipes[ `recipe-${id}` ].data,
				...data,
			};

			// Raw data change event.
			document.dispatchEvent( new CustomEvent( 'wprm-recipe-change-data', { detail: { id } } ) );
		}
	},
};

ready(() => {
	window.WPRecipeMaker.manager.init();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}