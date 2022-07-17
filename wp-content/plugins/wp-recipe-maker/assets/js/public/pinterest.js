window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.pinterest = {
	init() {
		document.addEventListener( 'click', function(e) {
			for ( var target = e.target; target && target != this; target = target.parentNode ) {
				if ( target.matches( '.wprm-recipe-pin' ) ) {
					WPRecipeMaker.pinterest.onClick( target, e );
					break;
				}
			}
        }, false );
	},
	onClick( el, e ) {
		e.preventDefault();

        const pinUtils = this.getPinUtils();

        if ( pinUtils ) {
            const url = el.dataset.url;
            const media = el.dataset.media;
            const description = el.dataset.description;
            const repin = el.dataset.repin;

            if ( repin ) {
                PinUtils.repin( repin );
            } else {
                pinUtils.pinOne({
                    url,
                    media,
                    description,
                });
            }
        } else {
            // Default popup to open when pinit.js is not loaded.
            window.open( el.href, 'targetWindow', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=500,height=500' );
        }
    },
    getPinUtils() {
        if ( window.hasOwnProperty( 'PinUtils' ) ) {
            return window.PinUtils;
        }

        return false;
    },
};

ready(() => {
	window.WPRecipeMaker.pinterest.init();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}