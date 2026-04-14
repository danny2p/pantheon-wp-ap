import '../../css/admin/ai-assistant.scss';

import tippy from 'tippy.js';

const initAdminTooltips = () => {
    const tooltips = document.querySelectorAll('.wprm-ai-assistant-tippy, .wprm-admin-tippy');

    for ( let tooltip of tooltips ) {
        if ( tooltip._tippy ) {
            tooltip._tippy.destroy();
        }

        const content = tooltip.dataset.hasOwnProperty( 'wprmTooltip' ) ? tooltip.dataset.wprmTooltip : '';

        if ( ! content ) {
            continue;
        }

        tippy( tooltip, {
            content,
            allowHTML: true,
            placement: 'top',
            popperOptions: {
                modifiers: [
                    {
                        name: 'addZIndex',
                        enabled: true,
                        phase: 'write',
                        fn: ( { state } ) => {
                            state.styles.popper.zIndex = '100000';
                        },
                    },
                    {
                        name: 'preventOverflow',
                        options: {
                            boundary: 'window',
                        },
                    },
                ],
            },
        } );
    }
};

if ( document.readyState !== 'loading' ) {
    initAdminTooltips();
} else {
    document.addEventListener( 'DOMContentLoaded', initAdminTooltips );
}
