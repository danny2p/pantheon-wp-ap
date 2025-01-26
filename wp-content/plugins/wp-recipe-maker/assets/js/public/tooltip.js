import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';

import '../../css/public/tooltip.scss';

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
                container.role = "button"; // Needed for accessibility.

                const hasHtml = container.dataset.hasOwnProperty( 'tooltipHtml' ) && '1' === container.dataset.tooltipHtml;

                let content = tooltip;
                if ( hasHtml ) {
                    // Strip HTML tags.
                    content = content.replace( /<[^>]*>/g, '' );
                }

                tippy( container, {
                    theme: 'wprm',
                    content,
                    allowHTML: false,
                    interactive: true,
                    onCreate(instance) {
                        // Prevents the tooltip from breaking ingredients into multiple lines.
                        instance.popper.style.display = 'inline-block';

                        // State of fetching.
                        instance._isFetching = false;
                        instance._fetchedContent = false;
                    },
                    onShow(instance) {
                        if ( instance._isFetching || instance._fetchedContent ) {
                            return;
                        }

                        if ( hasHtml ) {
                            instance._isFetching = true;

                            fetch( `${wprm_public.endpoints.utilities}/sanitize`, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify( { text: tooltip } ),
                            } ).then( ( response ) => {
                                if ( response.ok ) {
                                    return response.json();
                                } else {
                                    return false;
                                }
                            } ).then( ( html ) => {
                                instance._isFetching = false;
                                instance._fetchedContent = true;

                                if ( html ) {
                                    instance.setContent( html );

                                    // Change allowHTML to true to show HTML.
                                    instance.setProps( { allowHTML: true } );
                                }
                            } );
                        }
                    }
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