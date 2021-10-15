import React from 'react';

import Tooltip from './Tooltip';

import '../../css/admin/shared/button.scss';

const Button = (props) => {
    let buttonDisabled = false;
    let tooltipContent = props.help ? props.help : false;
    let className = 'button';

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

    // Extra class if disabled. Don't actually use disabled or tooltip won't work.
    if ( buttonDisabled ) {
        className += ' wprm-button-required';
    }

    return (
        <Tooltip content={ tooltipContent }>
            <button
                className={ className }
                tabIndex={ props.disableTab ? '-1' : null }
                onClick={ buttonDisabled ? () => {
                    if ( confirm( 'Want to learn more about the version required for this feature?' ) ) {
                        window.open( 'https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/', '_blank' );
                    } 
                } : props.onClick }
            >
                { props.children }
            </button>
        </Tooltip>
    );
}

export default Button;