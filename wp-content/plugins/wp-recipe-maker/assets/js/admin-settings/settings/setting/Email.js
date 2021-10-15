import React from 'react';
import PropTypes from 'prop-types';

const SettingEmail = (props) => {
    return (
        <input
            className="wprm-setting-input"
            type="email"
            value={props.value}
            onChange={(e) => props.onValueChange(e.target.value)}
        />
    );
}

SettingEmail.propTypes = {
    setting: PropTypes.object.isRequired,
    value: PropTypes.any.isRequired,
    onValueChange: PropTypes.func.isRequired,
}

export default SettingEmail;