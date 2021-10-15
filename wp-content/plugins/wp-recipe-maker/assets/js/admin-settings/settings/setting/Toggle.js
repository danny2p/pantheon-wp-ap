import React from 'react';
import PropTypes from 'prop-types';
import Toggle from 'react-toggle';
import 'react-toggle/style.css'

const SettingToggle = (props) => {
    return (
        <Toggle
            className="wprm-setting-input"
            checked={props.value}
            aria-label={props.setting.name}
            onChange={(e) => props.onValueChange(e.target.checked)}
        />
    );
}

SettingToggle.propTypes = {
    setting: PropTypes.object.isRequired,
    value: PropTypes.any.isRequired,
    onValueChange: PropTypes.func.isRequired,
}

export default SettingToggle;