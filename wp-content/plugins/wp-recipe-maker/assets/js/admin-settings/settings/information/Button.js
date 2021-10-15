import React from 'react';
import PropTypes from 'prop-types';

const InformationButton = (props) => {
    return (
        <button
            className="button"
            onClick={(e) => {
                e.preventDefault();
                if ( props.settingsChanged ) {
                    alert( 'You will be leaving the settings page. Please save or cancel your changes first.' );
                } else {
                    window.location = props.setting.link;
                }
            } }
        >{props.setting.button}</button>
    );
}

InformationButton.propTypes = {
    setting: PropTypes.object.isRequired,
    settingsChanged: PropTypes.bool.isRequired,
}

export default InformationButton;