window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

// Documentation: https://www.npmjs.com/package/@smartwithfood/js-sdk
window.WPRecipeMaker.smartwithfood = {
	init: () => {
        if ( window.hasOwnProperty( 'Recipe2Basket' ) && window.hasOwnProperty( 'wprm_smartwithfood_token' ) ) {
            window.Recipe2Basket.initialize({
                token: window.wprm_smartwithfood_token,
                language: 'nl',
            });

            window.WPRecipeMaker.smartwithfood.initButtons();
        }
    },
    initButtons: () => {
        if ( ! window.hasOwnProperty( 'Recipe2Basket' ) || ! 'initialized' === window.Recipe2Basket.status ) {
            return;
        }

        const buttons = document.querySelectorAll( '.wprm-recipe-smart-with-food' );

        for ( let button of buttons ) {
            const recipeId = button.dataset.recipe;
            const recipeName = button.dataset.recipeName;
            const recipeImage = button.dataset.recipeImage;

            if ( recipeId ) {
                const container = document.getElementById( 'wprm-recipe-container-' + recipeId );

                if ( container ) {
                    const image = button.querySelector( '.wprm-recipe-smart-with-food-button' );
                    image.addEventListener( 'click', ( event ) => {
                        event.preventDefault();
                        window.WPRecipeMaker.smartwithfood.purchaseRecipe( recipeId, recipeName, recipeImage );
                    });
                    button.style.display = '';
                }
            }
        }
    },
    purchaseRecipe: ( recipeId, recipeName = '', recipeImage = '' ) => {
        const container = document.getElementById( 'wprm-recipe-container-' + recipeId );

        if ( container ) {
            // Get servings.
            let servings = 1;
            const servingsContainer = container.querySelector( '.wprm-recipe-servings' );
            if ( servingsContainer ) {
                if ( servingsContainer.dataset.hasOwnProperty( 'servings' ) ) {
                    servings = parseInt( servingsContainer.dataset.servings );
                } else {
                    servings = parseInt( servingsContainer.innerText );
                }
            }

            servings = isNaN( servings ) && servings <= 0 ? 1 : servings;

            // Get ingredients.
            let ingredients = [];

            const ingredientContainers = container.querySelectorAll( '.wprm-recipe-ingredient' );
            for ( let ingredientContainer of ingredientContainers ) {
                const ingredientClone = document.createElement( 'div' );
                ingredientClone.innerHTML = ingredientContainer.innerHTML;
                
                // Remove notes.
                const ingredientCloneNotes = ingredientClone.querySelector( '.wprm-recipe-ingredient-notes' );
                if ( ingredientCloneNotes ) { ingredientCloneNotes.remove(); }

                // Remove checkbox.
                const ingredientCloneCheckbox = ingredientClone.querySelector( '.wprm-checkbox-container' );
                if ( ingredientCloneCheckbox ) { ingredientCloneCheckbox.remove(); }

                // Get and clean up remaining text.
                let ingredientString = ingredientClone.innerText;
                ingredientString = ingredientString.replace( /\s\s+/g, ' ' );
                ingredientString = ingredientString.trim();

                if ( ingredientString ) {
                    ingredients.push( ingredientString );
                }
            }

            if ( ingredients.length ) {
                Recipe2Basket.openModal({
                    recipes: [
                      {
                        language: 'nl',
                        name: recipeName,
                        media: recipeImage,
                        ingredients: ingredients,
                        yield: servings,
                      }
                    ]
                });
            }
        }
    },
};


ready(() => {
	window.WPRecipeMaker.smartwithfood.init();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}