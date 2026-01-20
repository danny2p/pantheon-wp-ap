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
            menuPlacement="auto"
            value={selectOptions.filter(({value}) => value === props.value)}
            onChange={(option) => props.onValueChange(option.value)}
            options={selectOptions}
            clearable={false}
            styles={{
                control: (provided) => ({
                    ...provided,
                    minHeight: '32px',
                    height: '32px',
                }),
                valueContainer: (provided) => ({
                    ...provided,
                    padding: '0 8px',
                    height: '30px',
                }),
                input: (provided) => ({
                    ...provided,
                    margin: 0,
                    padding: 0,
                    height: '30px',
                }),
                singleValue: (provided) => ({
                    ...provided,
                    lineHeight: '30px',
                }),
                placeholder: (provided) => ({
                    ...provided,
                    lineHeight: '30px',
                }),
                indicatorsContainer: (provided) => ({
                    ...provided,
                    height: '32px',
                }),
                indicatorSeparator: (provided) => ({
                    ...provided,
                    marginTop: '6px',
                    marginBottom: '6px',
                }),
            }}
        />
    );
}

export default PropertyDropdown;