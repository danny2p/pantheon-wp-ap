import React from 'react';
import PropTypes from 'prop-types';
import CodeMirror from 'react-codemirror';
require('codemirror/lib/codemirror.css');
require('codemirror/mode/css/css');

const SettingCode = (props) => {
    return (
        <CodeMirror
            className="wprm-setting-input"
            value={props.value}
            onChange={(value) => props.onValueChange(value)}
            options={{
                lineNumbers: true,
                mode: props.setting.code,
            }}
        />
    );
}

SettingCode.propTypes = {
    setting: PropTypes.object.isRequired,
    value: PropTypes.any.isRequired,
    onValueChange: PropTypes.func.isRequired,
}

export default SettingCode;