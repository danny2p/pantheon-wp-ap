import React from 'react';
import Select from 'react-select';


const PropertyDropdown = (props) => {
    let selectOptions = [];

    for (let option in props.property.options) {
        selectOptions.push({
            value: option,
            label: props.property.options[option],
        });
    }

    return (
        <Select
            className="wprm-template-property-input"
            menuPlacement="top"
            value={selectOptions.filter(({value}) => value === props.value)}
            onChange={(option) => props.onValueChange(option.value)}
            options={selectOptions}
            clearable={false}
        />
    );
}

export default PropertyDropdown;