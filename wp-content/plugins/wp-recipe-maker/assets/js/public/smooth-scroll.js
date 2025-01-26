import animateScrollTo from 'animated-scroll-to';

window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.jump = {
	init: () => {
		document.addEventListener( 'click', function(e) {
			for ( var target = e.target; target && target != this; target = target.parentNode ) {
				if ( target.matches( '.wprm-recipe-jump, .wprm-recipe-jump-to-comments, .wprm-recipe-jump-video, .wprm-jump-smooth-scroll' ) ) {
					WPRecipeMaker.jump.onClick( target, e );
					break;
				}
			}
		}, false );
	},
	onClick: ( el, e ) => {
        const target = el.getAttribute('href');

        // Maybe uses smooth scroll.
        if ( el.matches( '.wprm-jump-smooth-scroll' ) ) {
            e.preventDefault();

            let speed = parseInt( el.dataset.smoothScroll );

            if ( speed < 0 ) {
                speed = 500;
            }

            animateScrollTo( document.querySelector(target), {
                verticalOffset: -100,
                speed,
            } );
        } else {
            // Check if hash should not be shown.
            if ( ! wprm_public.settings.jump_output_hash ) {
                const elementToJumpTo = document.querySelector( target );

                if ( elementToJumpTo ) {
                    e.preventDefault();
                    elementToJumpTo.scrollIntoView();
                }
            }
        }
	},
};

ready(() => {
	window.WPRecipeMaker.jump.init();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}