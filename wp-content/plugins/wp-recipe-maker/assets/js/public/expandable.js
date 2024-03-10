window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.expandable = {
	init: () => {
		document.addEventListener( 'click', function(e) {
			for ( var target = e.target; target && target != this; target = target.parentNode ) {
				if ( target.matches( '.wprm-expandable-button' ) ) {
					WPRecipeMaker.expandable.onClick( target, e );
					break;
				}
			}
        }, false );
	},
	onClick: ( el, e ) => {
		e.preventDefault();
        const container = el.closest( '.wprm-expandable-container' );

        if ( el.classList.contains( 'wprm-expandable-button-show' ) ) {
            container.classList.remove( 'wprm-expandable-collapsed' );
            container.classList.add( 'wprm-expandable-expanded' );
        } else {
            container.classList.add( 'wprm-expandable-collapsed' );
            container.classList.remove( 'wprm-expandable-expanded' );
        }
    },
};

ready(() => {
	window.WPRecipeMaker.expandable.init();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}