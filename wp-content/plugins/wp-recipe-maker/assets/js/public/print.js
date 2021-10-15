window.WPRecipeMaker.print = {
	init: () => {
		document.addEventListener( 'click', function(e) {
			for ( var target = e.target; target && target != this; target = target.parentNode ) {
				if ( target.matches( '.wprm-recipe-print, .wprm-print-recipe-shortcode' ) ) {
					WPRecipeMaker.print.onClick( target, e );
					break;
				}
			}
		}, false );
	},
	onClick: ( el, e ) => {
		let recipeId = el.dataset.recipeId;

		// Backwards compatibility.
		if ( !recipeId ) {
			const container = el.closest( '.wprm-recipe-container' );

			if ( container ) {
				recipeId = container.dataset.recipeId; 
			}
		}

		// Still no recipe ID? Just follow the link. Override otherwise.
		if ( recipeId ) {
			e.preventDefault();
			recipeId = parseInt( recipeId );
			
			// Analytics.
			let location = 'other';

			const template = el.closest( '.wprm-recipe' );
			if ( template ) {
				if ( template.classList.contains( 'wprm-recipe-snippet' ) ) {
					location = 'snippet';
				} else if ( template.classList.contains( 'wprm-recipe-roundup-item' ) ) {
					location = 'roundup';
				} else {
					location = 'recipe';
				}
			}

			window.WPRecipeMaker.analytics.registerAction( recipeId, wprm_public.post_id, 'print', {
				location,
			});

			// Actually print.
			WPRecipeMaker.print.recipeAsIs( recipeId );
		}
	},
	recipeAsIs: ( id ) => {
		let servings = false,
			system = 1;

		// Get recipe servings.
		if ( window.WPRecipeMaker.hasOwnProperty( 'quantities' ) ) {
			const recipe = WPRecipeMaker.quantities.getRecipe( id );

			if ( recipe ) {
				system = recipe.system;

				// Only if servings changed.
				if ( recipe.servings !== recipe.originalServings ) {
					servings = recipe.servings;
				}
			}
		}

		WPRecipeMaker.print.recipe( id, servings, system );
	},
	recipe: ( id, servings = false, system = 1 ) => {
		const url = WPRecipeMaker.print.getUrl( id );
		const target = wprm_public.settings.print_new_tab ? '_blank' : '_self';
		const printWindow = window.open( url, target );

		printWindow.onload = () => {
			printWindow.focus();
			printWindow.WPRMPrint.setArgs({
				system,
				servings,
			});
		};
	},
	getUrl: ( args ) => {
		const urlParts = wprm_public.home_url.split(/\?(.+)/);
		let printUrl = urlParts[0];

		if ( wprm_public.permalinks ) {
			printUrl += wprm_public.print_slug + '/' + args;

			if ( urlParts[1] ) {
				printUrl += '?' + urlParts[1];
			}
		} else {
			printUrl += '?' + wprm_public.print_slug + '=' + args;

			if ( urlParts[1] ) {
				printUrl += '&' + urlParts[1];
			}
		}

		return printUrl;
	},
};

ready(() => {
	window.WPRecipeMaker.print.init();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}