import React from 'react';
import PropTypes from 'prop-types';

const SettingText = (props) => {
    return (
        <input
            className="wprm-setting-input"
            type="text"
            value={props.value}
            onChange={(e) => props.onValueChange(e.target.value)}
        />
    );
}

SettingText.propTypes = {
    setting: PropTypes.object.isRequired,
    value: PropTypes.any.isRequired,
    onValueChange: PropTypes.func.isRequired,
}

export default SettingText;