window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.media = {
	init: () => {
		document.addEventListener( 'click', function(e) {
			for ( var target = e.target; target && target != this; target = target.parentNode ) {
				if ( target.matches( '.wprm-recipe-media-toggle' ) ) {
					WPRecipeMaker.media.onClick( target, e );
					break;
				}
			}
        }, false );

        // Set starting state.
        WPRecipeMaker.media.setState( wprm_public.settings.instruction_media_toggle_default );
	},
	onClick: ( el, e ) => {
		e.preventDefault();

        const newState = el.dataset.state;
        WPRecipeMaker.media.setState( newState, el );
    },
    setState: ( newState, el = false ) => {
        const currentState = WPRecipeMaker.media.state;
        if ( ( 'on' === newState || 'off' === newState ) && newState !== currentState ) {
            // Check position of element before toggle.
            let elDistanceToTopBefore = 0;
            if ( el ) {
                elDistanceToTopBefore = window.pageYOffset + el.getBoundingClientRect().top;
            }

            // Toggle images.
            const medias = document.querySelectorAll( '.wprm-recipe-instruction-media' );

            for ( let media of medias ) {
                if ( 'off' === newState ) {
                    media.style.display = 'none';
                } else {
                    media.style.display = '';
                }
            }

            // Check position of element after toggle.
            let elDistanceToTopafter = 0;
            if ( el ) {
                elDistanceToTopafter = window.pageYOffset + el.getBoundingClientRect().top;
            }

            // Scroll up/down as needed so element stays in view.
            const scrollDiff = elDistanceToTopafter - elDistanceToTopBefore;
            if ( scrollDiff ) {
                scrollBy( 0, scrollDiff );
            }
            
            // Toggle buttons.
            const buttons = document.querySelectorAll( '.wprm-recipe-media-toggle' );

            for ( let button of buttons ) {
                if ( newState === button.dataset.state ) {
                    button.classList.add( 'wprm-toggle-active' );
				} else {
					button.classList.remove( 'wprm-toggle-active' );
				}
            }

            // Update current state.
            WPRecipeMaker.media.state = newState;
        }
    },
    state: 'on',
};

ready(() => {
	window.WPRecipeMaker.media.init();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}