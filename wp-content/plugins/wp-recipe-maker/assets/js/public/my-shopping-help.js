window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.myShoppingHelp = {
	init: () => {
		// Show shortcode elements if window.msh is available.
		WPRecipeMaker.myShoppingHelp.checkAndShow();

		// Also check periodically in case window.msh loads later.
		let checkInterval = setInterval( () => {
			if ( window.msh ) {
				WPRecipeMaker.myShoppingHelp.checkAndShow();
				clearInterval( checkInterval );
			}
		}, 100 );

		// Stop checking after 10 seconds.
		setTimeout( () => {
			clearInterval( checkInterval );
		}, 10000 );

		document.addEventListener( 'click', function( e ) {
			for ( let target = e.target; target && target !== this; target = target.parentNode ) {
				if ( target.matches( '.wprm-recipe-my-shopping-help' ) ) {
					WPRecipeMaker.myShoppingHelp.onClick( target, e );
					break;
				}
			}
		}, false );
	},
	checkAndShow: () => {
		if ( window.msh && ( typeof window.msh.addRecipe === 'function' || typeof window.msh.AddRecipe === 'function' ) ) {
			const elements = document.querySelectorAll( '.wprm-recipe-my-shopping-help' );
			elements.forEach( ( el ) => {
				if ( el.style.visibility === 'hidden' ) {
					el.style.visibility = 'visible';
				}
			} );
		}
	},
	onClick: ( el, event ) => {
		event.preventDefault();

		const recipe = WPRecipeMaker.myShoppingHelp.getRecipeFromElement( el );

		if ( ! recipe ) {
			console.warn( 'WPRM My Shopping Help: recipe data is missing.' );
			return;
		}

		const handler = WPRecipeMaker.myShoppingHelp.getHandler();

		if ( handler ) {
			handler( recipe );
		} else {
			console.warn( 'WPRM My Shopping Help: widget is not available yet.' );
		}
	},
	getRecipeFromElement: ( el ) => {
		if ( el.dataset.recipe ) {
			try {
				return JSON.parse( el.dataset.recipe );
			} catch ( error ) {
				console.error( 'WPRM My Shopping Help: unable to parse recipe data.', error );
			}
		}

		const recipe = {};

		if ( el.dataset.recipeId ) {
			recipe.id = el.dataset.recipeId;
		}
		if ( el.dataset.recipeType ) {
			recipe.type = el.dataset.recipeType;
		}
		if ( el.dataset.recipeUrl ) {
			recipe.url = el.dataset.recipeUrl;
		}
		if ( el.dataset.recipeName ) {
			recipe.name = el.dataset.recipeName;
		}
		if ( el.dataset.recipeImage ) {
			recipe.image = el.dataset.recipeImage;
		}

		return Object.keys( recipe ).length ? recipe : null;
	},
	getHandler: () => {
		if ( window.msh && typeof window.msh.addRecipe === 'function' ) {
			return window.msh.addRecipe.bind( window.msh );
		}

		if ( window.msh && typeof window.msh.AddRecipe === 'function' ) {
			return window.msh.AddRecipe.bind( window.msh );
		}

		return null;
	},
};

ready( () => {
	window.WPRecipeMaker.myShoppingHelp.init();
} );

function ready( fn ) {
	if ( document.readyState != 'loading' ) {
		fn();
	} else {
		document.addEventListener( 'DOMContentLoaded', fn );
	}
}

