import React from 'react';
import PropTypes from 'prop-types';
import Select from 'react-select';

const SettingDropdown = (props) => {
    let selectOptions = [];

    for (let option in props.setting.options) {
        selectOptions.push({
            value: option,
            label: props.setting.options[option],
        });
    }

    return (
        <Select
            className="wprm-setting-input"
            value={selectOptions.filter(({value}) => value === props.value)}
            onChange={(option) => props.onValueChange(option.value)}
            options={selectOptions}
            clearable={false}
        />
    );
}

SettingDropdown.propTypes = {
    setting: PropTypes.object.isRequired,
    value: PropTypes.any.isRequired,
    onValueChange: PropTypes.func.isRequired,
}

export default SettingDropdown;