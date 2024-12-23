import sanitizeHtml from 'sanitize-html';
import tippy, { inlinePositioning } from 'tippy.js';
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

                const sanitized = sanitizeHtml( tooltip, {
                    allowedTags: [
                        'b', 'i', 'em', 'strong', 'a', 'img', 'p', 'ul', 'ol', 'li', 'br',
                    ],
                    allowedAttributes: {
                        a: ['href', 'title', 'target', 'rel'],
                        img: ['src', 'alt', 'title', 'width', 'height'],
                        '*': ['style', 'class'],
                    },
                    allowedSchemes: ['http', 'https'],
                    allowedSchemesByTag: {
                        img: ['data', 'http', 'https'],
                    },
                } );

                tippy( container, {
                    theme: 'wprm',
                    content: sanitized,
                    allowHTML: true,
                    interactive: true,
                    onCreate(instance) {
                        // Prevents the tooltip from breaking ingredients into multiple lines.
                        instance.popper.style.display = 'inline-block';
                    },
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