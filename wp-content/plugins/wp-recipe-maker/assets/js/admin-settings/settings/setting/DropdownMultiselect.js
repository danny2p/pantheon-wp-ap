import React from 'react';
import PropTypes from 'prop-types';
import Select from 'react-select';


const SettingDropdownMultiselect = (props) => {
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
            value={selectOptions.filter(({value}) => props.value.includes(value))}
            isMulti
            onChange={(options) => {
                const selected = Array.isArray(options) ? options : [options];
                return props.onValueChange(selected.map(option => option.value));
            }}
            options={selectOptions}
        />
    );
}

SettingDropdownMultiselect.propTypes = {
    setting: PropTypes.object.isRequired,
    value: PropTypes.any.isRequired,
    onValueChange: PropTypes.func.isRequired,
}

export default SettingDropdownMultiselect;