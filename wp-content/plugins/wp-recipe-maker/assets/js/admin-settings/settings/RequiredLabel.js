import React from 'react';
import PropTypes from 'prop-types';

const pluginUrl = "https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/";

const RequiredLabel = (props) => {

    if (!props.object.hasOwnProperty('required')) {        
        return null;
    }

    // Don't show if addon is active.
    if ( wprm_admin.addons.hasOwnProperty( props.object.required ) && wprm_admin.addons[ props.object.required ] ) {
        return null;
    }

    let requiredLabelName = 'Premium Only';
        if ( 'premium' !== props.object.required ) {
            const capitalized = props.object.required[0].toUpperCase() + props.object.required.substring(1);
            requiredLabelName = `${capitalized} Bundle Only`;
        }

    return (
        <a href={pluginUrl} target="_blank" className="wprm-setting-required">{requiredLabelName}</a>
    );
}

RequiredLabel.propTypes = {
    object: PropTypes.object.isRequired,
}

export default RequiredLabel;