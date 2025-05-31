window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.jumpToSection = {
    initOnce: () => {
        window.addEventListener( 'resize', () => {
            const containers = document.querySelectorAll('.wprm-recipe-jump-to-section-container-scroll');
            for ( let container of containers ) {
                window.WPRecipeMaker.jumpToSection.updateFade( container );
            }
        } );
        window.WPRecipeMaker.jumpToSection.init();
    },
	init: () => {
        const containers = document.querySelectorAll('.wprm-recipe-jump-to-section-container-scroll');

        function handleScroll( e ) {
            window.WPRecipeMaker.jumpToSection.updateFade( e.currentTarget );
        }

        for ( let container of containers ) {
            // Remove if already exists.
            container.removeEventListener('scroll', handleScroll);
            container.addEventListener('scroll', handleScroll);

            // Initial state.
            window.WPRecipeMaker.jumpToSection.updateFade( container );
        }
	},
	updateFade: ( container ) => {
        const scrollLeft = container.scrollLeft;
        const maxScrollLeft = container.scrollWidth - container.clientWidth;
    
        container.classList.toggle( 'scrolled-left', scrollLeft <= 0 );
        container.classList.toggle( 'scrolled-right', scrollLeft >= maxScrollLeft - 1 );
	},
};

ready(() => {
	window.WPRecipeMaker.jumpToSection.initOnce();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}