import React from 'react';
import PropTypes from 'prop-types';

const SettingTextarea = (props) => {
    const rows = props.setting.hasOwnProperty('rows') ? props.setting.rows : 5;
    
    return (
        <textarea
            className="wprm-setting-input"
            value={props.value}
            rows={rows}
            onChange={(e) => props.onValueChange(e.target.value)}
        />
    );
}

SettingTextarea.propTypes = {
    setting: PropTypes.object.isRequired,
    value: PropTypes.any.isRequired,
    onValueChange: PropTypes.func.isRequired,
}

export default SettingTextarea;