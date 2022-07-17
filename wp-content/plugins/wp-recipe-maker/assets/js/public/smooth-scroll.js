import animateScrollTo from 'animated-scroll-to';

window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.jump = {
	init: () => {
		document.addEventListener( 'click', function(e) {
			for ( var target = e.target; target && target != this; target = target.parentNode ) {
				if ( target.matches( '.wprm-jump-smooth-scroll' ) ) {
					WPRecipeMaker.jump.onClick( target, e );
					break;
				}
			}
		}, false );
	},
	onClick: ( el, e ) => {
		e.preventDefault();

        const target = el.getAttribute('href');

        // Get smooth scroll speed.
        let speed = parseInt( el.dataset.smoothScroll );
        if ( speed < 0 ) {
            speed = 500;
        }

        animateScrollTo( document.querySelector(target), {
            verticalOffset: -100,
            speed,
        } );
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