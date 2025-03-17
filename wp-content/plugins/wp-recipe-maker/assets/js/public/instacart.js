window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

// Source: https://docs.instacart.com/developer_platform_api.
window.WPRecipeMaker.instacart = {
	init: () => {
        // Add click listener.
		document.addEventListener( 'click', function(e) {
			for ( var target = e.target; target && target != this; target = target.parentNode ) {
				if ( target.matches( '.wprm-recipe-shop-instacart' ) ) {
					WPRecipeMaker.instacart.onClickButton( target, e );
					break;
                }
			}
        }, false );

        // Javascript is loaded, so make sure to show buttons.
        WPRecipeMaker.instacart.show();
    },
    show: () => {
        const buttons = document.querySelectorAll( '.wprm-recipe-shop-instacart' );

        for ( let button of buttons ) {
            button.style.visibility = '';
        }
    },
	onClickButton: ( el, e ) => {
        e.preventDefault()

        // Prevent double clicks.
        if ( el.classList.contains( 'wprm-recipe-shop-instacart-loading' ) ) {
            return;
        }

        // Get recipe ID.
        const recipeId = parseInt( el.dataset.recipe );

        if ( recipeId ) {
            el.classList.add( 'wprm-recipe-shop-instacart-loading' );

            // Open window immediately on click to prevent popup blocking.
            const newWindow = window.open('about:blank', '_blank');

            // Add loading message with some basic styling
            newWindow.document.write(`
                <html>
                    <head>
                        <title>Loading Instacart...</title>
                        <style>
                            body {
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                height: 100vh;
                                margin: 0;
                                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                            }
                            .loading {
                                font-size: 24px;
                                color: #333;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="loading">Loading Instacart...</div>
                    </body>
                </html>
            `);

            window.WPRecipeMaker.manager.getRecipe( recipeId ).then( ( recipe ) => {
                if ( recipe ) {
                    const servingsSystemCombination = '' + recipe.data.currentServings + '-' + recipe.data.currentSystem;
                    let ingredients = [];

                    // Get the ingredients.
                    if ( window.WPRecipeMaker.hasOwnProperty( 'managerPremiumIngredients' ) ) {
                        // Get current ingredients, maybe in a different system and after adjusting servings.
                        const currentIngredients = window.WPRecipeMaker.managerPremiumIngredients.getCurrentIngredients( recipe );
                        const currentSystemIngredients = currentIngredients.map( ingredient => ingredient[`unit-system-${ recipe.data.currentSystem }`] );

                        for ( let ingredient of currentSystemIngredients ) {
                            ingredients.push( {
                                name: ingredient.name,
                                quantity: ingredient.amountParsed,
                                unit: ingredient.unit,
                            } );
                        }
                    } else {
                        for ( let ingredient of recipe.data.ingredients ) {
                            ingredients.push( {
                                name: ingredient.name,
                                quantity: ingredient.amount,
                                unit: ingredient.unit,
                            } );
                        }
                    }

                    let data = {
                        recipeId,
                        title: recipe.data.name,
                        image_url: recipe.data.image_url,
                        ingredients,
                        servingsSystemCombination,
                    };

                    fetch( `${wprm_public.endpoints.integrations}/instacart`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify( { data } ),
                    } ).then( ( response ) => {
                        if ( response.ok ) {
                            return response.json();
                        } else {
                            return false;
                        }
                    } ).then( ( link ) => {
                        // Remove loading class.
                        el.classList.remove( 'wprm-recipe-shop-instacart-loading' );

                        // Open link in new tab.
                        if ( link ) {
                            newWindow.location.href = link;
                        } else {
                            alert( 'Something went wrong. Please try again later.' );
                        }
                    } );
                }
            } );
        }
    },
};

ready(() => {
	window.WPRecipeMaker.instacart.init();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}