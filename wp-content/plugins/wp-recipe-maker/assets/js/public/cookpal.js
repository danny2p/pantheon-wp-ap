window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.cookPal = {
	init: () => {
		if ( ! WPRecipeMaker.cookPal.hasShortcode() ) {
			return;
		}

		WPRecipeMaker.cookPal.checkAndShow();

		let checkInterval = setInterval( () => {
			if ( WPRecipeMaker.cookPal.hasButton() ) {
				WPRecipeMaker.cookPal.checkAndShow();
				clearInterval( checkInterval );
			}
		}, 100 );

		setTimeout( () => {
			clearInterval( checkInterval );
		}, 10000 );

		document.addEventListener( 'click', function( e ) {
			for ( let target = e.target; target && target !== this; target = target.parentNode ) {
				if ( target.matches( '.wprm-recipe-cookpal' ) ) {
					WPRecipeMaker.cookPal.onClick( e );
					break;
				}
			}
		}, false );
	},
	hasButton: () => {
		return !! document.getElementById( 'cookpal-chat-button' );
	},
	hasShortcode: () => {
		return !! document.querySelector( '.wprm-recipe-cookpal' );
	},
	checkAndShow: () => {
		if ( WPRecipeMaker.cookPal.hasButton() ) {
			const elements = document.querySelectorAll( '.wprm-recipe-cookpal' );
			elements.forEach( ( el ) => {
				if ( el.style.visibility === 'hidden' ) {
					el.style.visibility = 'visible';
				}
			} );
		}
	},
	onClick: ( event ) => {
		event.preventDefault();

		const button = document.getElementById( 'cookpal-chat-button' );

		if ( button ) {
			button.click();
		} else {
			console.warn( 'WPRM CookPal: widget is not available yet.' );
		}
	},
};

ready( () => {
	window.WPRecipeMaker.cookPal.init();
} );

function ready( fn ) {
	if ( document.readyState != 'loading' ) {
		fn();
	} else {
		document.addEventListener( 'DOMContentLoaded', fn );
	}
}
