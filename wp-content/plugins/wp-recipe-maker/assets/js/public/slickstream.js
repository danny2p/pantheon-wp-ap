window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

// Source: https://github.com/slickstream/docs/tree/main/engagement/1.x#slickstream-javascript-api-v10.
window.WPRecipeMaker.slickstream = {
	init: () => {
        // Add click listener.
		document.addEventListener( 'click', function(e) {
			for ( var target = e.target; target && target != this; target = target.parentNode ) {
				if ( target.matches( '.wprm-recipe-slickstream-not-saved' ) ) {
					WPRecipeMaker.slickstream.onClickSave( target, e );
					break;
                }
                if ( target.matches( '.wprm-recipe-slickstream-saved' ) ) {
					WPRecipeMaker.slickstream.onClickSaved( target, e );
					break;
				}
			}
        }, false );

        // Listen for favorites change to update button.
        document.addEventListener( 'slickstream-favorite-change', () => {
            WPRecipeMaker.slickstream.favoriteChanged();
        });

        // Trigger changed to set initial state.
        WPRecipeMaker.slickstream.favoriteChanged();

        // Show buttons once Slickstream is ready.
        WPRecipeMaker.slickstream.show();
    },
    show: async () => {
        const slickstream = await WPRecipeMaker.slickstream.getSlickstream();
        if ( slickstream ) {
            const buttons = document.querySelectorAll( '.wprm-recipe-slickstream' );

            for ( let button of buttons ) {
                button.style.visibility = '';
            }
        }
    },
    getSlickstream: async () => {
        if (window.slickstream) {
            return window.slickstream.v1;
        }
        return new Promise((resolve, reject) => {
           document.addEventListener('slickstream-ready', () => {
              resolve(window.slickstream.v1);
           });
        }); 
    },
	onClickSave: async ( el, e ) => {
        e.preventDefault()
        WPRecipeMaker.slickstream.setFavoriteStatus( true );
    },
    onClickSaved: async ( el, e ) => {
        e.preventDefault()
        WPRecipeMaker.slickstream.setFavoriteStatus( false );
    },
    favoriteChanged: async () => {
        const slickstream = await WPRecipeMaker.slickstream.getSlickstream();
        const isFavorite = slickstream.favorites.getState();

        // Update buttons.
        const buttons = document.querySelectorAll( '.wprm-recipe-slickstream' );

        for ( let button of buttons ) {
            if ( button.classList.contains( 'wprm-recipe-slickstream-not-saved' ) ) {
                button.style.display = true === isFavorite ? 'none' : '';
            } else if ( button.classList.contains( 'wprm-recipe-slickstream-saved' ) ) {
                button.style.display = true === isFavorite ? '' : 'none';
            }
        }
    },
    setFavoriteStatus: async ( status ) => {
        const slickstream = await WPRecipeMaker.slickstream.getSlickstream();
        slickstream.favorites.setState( status );
    },
};

ready(() => {
	window.WPRecipeMaker.slickstream.init();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}