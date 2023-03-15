import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';

window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.tooltip = {
	init() {
		WPRecipeMaker.tooltip.addTooltips();
	},
	addTooltips() {
        const containers = document.querySelectorAll('.wprm-tooltip');

        for ( let container of containers ) {
            // Remove any existing tippy.
            const existingTippy = container._tippy;

            if ( existingTippy ) {
                existingTippy.destroy();
            }

            // Check for tooltip.
            const tooltip = container.dataset.hasOwnProperty( 'tooltip' ) ? container.dataset.tooltip : false;

            if ( tooltip ) {
                tippy( container, {
                    content: tooltip,
                });
            }
        }
    },
};

ready(() => {
	window.WPRecipeMaker.tooltip.init();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}