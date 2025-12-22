import React from 'react';

import Icon from './Icon';
import Tooltip from './Tooltip';

import '../../css/admin/shared/button.scss';

const Button = (props) => {
    let buttonDisabled = false;
    let tooltipContent = props.help ? props.help : false;
    let className = 'button';
    const isExplicitlyDisabled = props.disabled === true;

    // Check if explicitly disabled via prop
    if ( isExplicitlyDisabled ) {
        buttonDisabled = true;
    }

    // Check if there are requirements.
    if ( props.required ) {
        if ( ! wprm_admin.addons.hasOwnProperty( props.required ) || true !== wprm_admin.addons[ props.required ] ) {
            buttonDisabled = true;

            if ( 'premium' !== props.required ) {
                const capitalized = props.required[0].toUpperCase() + props.required.substring(1);
                tooltipContent = `WP Recipe Maker ${capitalized} Bundle Only`;
            } else {
                tooltipContent = 'WP Recipe Maker Premium Only';
            }
        }
    }

    // Extra class if primary button.
    if ( props.isPrimary ) {
        className += ' button-primary';
    }

    // Extra class if AI button.
    if ( props.ai ) {
        className += ' wprm-button-ai';

        // AI features are only available in the Elite Bundle right now.
        if ( ! wprm_admin.addons.elite ) {
            buttonDisabled = true;
            tooltipContent = 'AI features are only available in the Elite Bundle during beta. Click to learn more.';
        } else {
            if ( ! tooltipContent ) {
                tooltipContent = 'AI features are currently in beta and only available with an active Elite Bundle license';
            }
        }
    }

    // Extra class if disabled with tooltip (for help cursor). Don't add if just disabled without tooltip.
    if ( buttonDisabled && tooltipContent ) {
        className += ' wprm-button-required';
    }

    // Don't show tooltip if explicitly disabled without help text
    const showTooltip = tooltipContent && ( !isExplicitlyDisabled || props.help );

    const buttonElement = (
        <button
            type={ props.type || 'button' }
            className={ className }
            tabIndex={ props.disableTab ? '-1' : null }
            onClick={ buttonDisabled ? () => {
                if ( props.ai ) {
                    if ( confirm( 'Want to learn more about the AI features?' ) ) {
                        window.open( 'https://help.bootstrapped.ventures/docs/wp-recipe-maker/ai-assistant/', '_blank' );
                    }
                } else {
                    if ( confirm( 'Want to learn more about the version required for this feature?' ) ) {
                        window.open( 'https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/', '_blank' );
                    }
                }
            } : props.onClick }
        >
            { props.ai && <Icon type="sparks" color="currentColor" /> }
            { props.children }
        </button>
    );

    if ( showTooltip ) {
        return (
            <Tooltip content={ tooltipContent }>
                { buttonElement }
            </Tooltip>
        );
    }

    return buttonElement;
}

export default Button;