window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.grow = {
	init: () => {
        // Make sure growMe is initialized or add temp function.
        if ( ! window.growMe ) {
            window.growMe = function (e) {
              window.growMe._.push(e);
            }
            window.growMe._ = [];
        }

        // Add listener
		document.addEventListener( 'click', function(e) {
			for ( var target = e.target; target && target != this; target = target.parentNode ) {
				if ( target.matches( '.wprm-recipe-grow-not-saved' ) ) {
					WPRecipeMaker.grow.onClickSave( target, e );
					break;
                }
                if ( target.matches( '.wprm-recipe-grow-saved' ) ) {
					WPRecipeMaker.grow.onClickSaved( target, e );
					break;
				}
			}
        }, false );
        
        // Check if already saved.
        let isBookmarked = null;
        window.growMe(() => {
            isBookmarked = window.growMe.getIsBookmarked();

            window.growMe.on("isBookmarkedChanged", (params) => {
                isBookmarked = params.isBookmarked;

                if ( isBookmarked ) {
                    // Don't know which recipe ID exactly, so triggers all.
                    WPRecipeMaker.grow.markAsSaved( false, true );
                }
            });
        });
	},
	onClickSave: ( el, e ) => {
        e.preventDefault()
        let atts = {
            source: 'wprm_save_btn',
        };

        const recipeId = parseInt( el.dataset.recipeId );
        const container = el.closest( '.wprm-recipe-grow-container' );

        if ( container ) {
            atts.tooltipReferenceElement = container;
        }

        window.growMe.addBookmark(atts).then( function(data) {
            WPRecipeMaker.grow.markAsSaved( recipeId );
        } )
        .catch( function(data) {
            WPRecipeMaker.grow.markAsSaved( recipeId );
        } );
    },
    onClickSaved: ( el, e ) => {
        e.preventDefault()
        // Do something when clicking on an already saved recipe?
    },
    markAsSaved: ( recipeId, skipRecipeIdCheck = false ) => {
        const buttons = document.querySelectorAll( '.wprm-recipe-grow' );

        for ( let button of buttons ) {
            if ( skipRecipeIdCheck || recipeId === parseInt( button.dataset.recipeId ) ) {
                if ( button.classList.contains( 'wprm-recipe-grow-not-saved' ) ) {
                    button.style.display = 'none';
                } else if ( button.classList.contains( 'wprm-recipe-grow-saved' ) ) {
                    button.style.display = '';
                }
            }
        }
    },
};

ready(() => {
	window.WPRecipeMaker.grow.init();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}