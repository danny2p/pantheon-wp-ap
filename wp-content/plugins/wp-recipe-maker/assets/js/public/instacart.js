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

        // Get recipe ID.
        const recipeId = parseInt( el.dataset.recipe );

        if ( recipeId ) {
            window.WPRecipeMaker.manager.getRecipe( recipeId ).then( ( recipe ) => {
                console.log( recipe );
                if ( recipe ) {

                    // Get current ingredient
                    // TODO Should work in free plugin as well.
                    const currentIngredients = window.WPRecipeMaker.managerPremiumIngredients.getCurrentIngredients( recipe );
                    const currentSystemIngredients = currentIngredients.map( ingredient => ingredient[`unit-system-${ recipe.data.currentSystem }`] );

                    console.log( 'currentSystemIngredients', currentSystemIngredients );

                    let ingredients = [];
                    for ( let ingredient of currentSystemIngredients ) {
                        ingredients.push( {
                            name: ingredient.name,
                            quantity: ingredient.amountParsed,
                            unit: ingredient.unit,
                        } );
                    }

                    let data = {
                        title: recipe.data.name,
                        image_url: recipe.data.image_url,
                        link_type: 'recipe',
                        ingredients,
                    };

                    console.log( 'instacart data', data );

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
                    } ).then( ( json ) => {
                        console.log( json );
                        if ( json ) {
                        }
                    } );

                    // fetch( instacart.url, {
                    //     method: 'POST',
                    //     headers: {
                    //         'Authorization: Bearer ': instacart.key,
                    //         'Accept': 'application/json',
                    //         'Content-Type': 'application/json',
                    //     },
                    //     body: JSON.stringify(data),
                    // })
                    // .then((response) => response.json())
                    // .then((json) => {
                    //     return json.data.lists_with_id;
                    // });
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