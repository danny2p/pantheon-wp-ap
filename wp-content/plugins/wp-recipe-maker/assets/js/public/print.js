window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

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

			// Optional template to print.
			const template = el.dataset.hasOwnProperty( 'template' ) ? el.dataset.template : '';
			
			// Analytics.
			let location = 'other';

			const parent = el.closest( '.wprm-recipe' );
			if ( parent ) {
				if ( parent.classList.contains( 'wprm-recipe-snippet' ) ) {
					location = 'snippet';
				} else if ( parent.classList.contains( 'wprm-recipe-roundup-item' ) ) {
					location = 'roundup';
				} else {
					location = 'recipe';
				}
			}

			window.WPRecipeMaker.analytics.registerAction( recipeId, wprm_public.post_id, 'print', {
				location,
			});

			// Actually print.
			WPRecipeMaker.print.recipeAsIs( recipeId, template );
		}
	},
	recipeAsIs: ( id, template = '' ) => {
		let servings = false,
			system = 1,
			advancedServings = false;

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

		// Get advanced servings.
		if ( window.WPRecipeMaker.hasOwnProperty( 'advancedServings' ) ) {
			advancedServings = WPRecipeMaker.advancedServings.getRecipe( id );
		}

		WPRecipeMaker.print.recipe( id, servings, system, advancedServings, template );
	},
	recipe: ( id, servings = false, system = 1, advancedServings = false, template = '' ) => {
		let urlArgs = id;
		if ( template ) {
			urlArgs += `/${template}`;
		}

		const url = WPRecipeMaker.print.getUrl( urlArgs );
		const target = wprm_public.settings.print_new_tab ? '_blank' : '_self';
		const printWindow = window.open( url, target );

		printWindow.onload = () => {
			printWindow.focus();
			printWindow.WPRMPrint.setArgs({
				system,
				servings,
				advancedServings,
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