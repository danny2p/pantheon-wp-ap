window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.manager = {
	init: () => {
		window.addEventListener( 'wpupgInitReady', function (e) {
			const grid = e.detail;
			window.WPRecipeMaker.recipe.wpupgGridCompatibility( grid );
		} );
	},
	recipes: {},
	getRecipe: ( id ) => {
		id = parseInt( id );

		if ( ! window.WPRecipeMaker.manager.recipes.hasOwnProperty( `recipe-${id}` ) ) {
			return window.WPRecipeMaker.manager.loadRecipe( id );
		}

		return window.WPRecipeMaker.manager.recipes[ id ];
	},
	loadRecipe: ( id ) => {
		if ( window.hasOwnProperty( 'wprm_recipes' ) && window.wprm_recipes.hasOwnProperty( `recipe-${id}` ) ) {
			return window.WPRecipeMaker.manager.getRecipeObject( window.wprm_recipes[ `recipe-${id}` ] );
		} else {
			// TODO API.
		}
	},
	getRecipeObject: ( data ) => {
		return {
			data,
			setServings: ( servings ) => {
				alert( 'setServings to ' + servings );
			},
		};
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