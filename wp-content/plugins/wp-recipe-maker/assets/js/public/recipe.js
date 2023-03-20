window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.recipe = {
	init: () => {
		window.addEventListener( 'wpupgInitReady', function (e) {
			const grid = e.detail;
			window.WPRecipeMaker.recipe.wpupgGridCompatibility( grid );
		} );
	},
	wpupgGridCompatibility: ( grid ) => {
		if ( grid ) {
			grid.on( 'itemsLoaded', () => {
				window.WPRecipeMaker.recipe.initFeatures();
			} );
		}
	},
	initFeatures: ( args = {} ) => {
		if ( window.WPRecipeMaker ) {
			if ( window.WPRecipeMaker.hasOwnProperty( 'advancedServings' ) ) {
				window.WPRecipeMaker.advancedServings.init();
			}
			if ( window.WPRecipeMaker.hasOwnProperty( 'quantities' ) ) {
				window.WPRecipeMaker.quantities.init();
	
				if ( args.hasOwnProperty( 'id' ) ) {
					window.WPRecipeMaker.quantities.initRecipe( args.id );
	
					if ( args.hasOwnProperty( 'servings' ) ) {
						const servings = parseInt( args.servings );
	
						if ( servings ) {
							window.WPRecipeMaker.quantities.setServings( args.id, servings );
						}
					}
				}
			}
	
			if ( window.WPRecipeMaker.hasOwnProperty( 'timer' ) ) {
				window.WPRecipeMaker.timer.init();
			}
	
			if ( window.WPRecipeMaker.hasOwnProperty( 'preventSleep' ) ) {
				window.WPRecipeMaker.preventSleep.init();
			}
	
			if ( window.WPRecipeMaker.hasOwnProperty( 'privateNotes' ) ) {
				window.WPRecipeMaker.privateNotes.init();
			}
	
			if ( window.WPRecipeMaker.hasOwnProperty( 'tooltip' ) ) {
				window.WPRecipeMaker.tooltip.init();
			}
			if ( window.WPRecipeMaker.hasOwnProperty( 'video' ) ) {
				if ( wprm_public.settings.video_force_ratio ) {
					window.WPRecipeMaker.video.init();
				}
			}
		}

		document.dispatchEvent( new CustomEvent( 'wprmRecipeInit', { detail: args } ) );
	},
};

// Don't wait for DOMContentLoaded. Listener needs to be added as soon as possible.
window.WPRecipeMaker.recipe.init();