import React, { Fragment } from 'react';
import Select from 'react-select';


const PropertyFont = (props) => {
    const groupedOptions = [{
        label: 'General',
        options: [
            {
                value: 'custom',
                label: 'Set custom font',
            },{
                value: 'inherit',
                label: 'Inherit from parent',
            },
            {
                value: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif',
                label: 'System Font',
            },
        ],
    },{
        label: 'Default Serif Fonts',
        options: [
            {
                value: 'Georgia, serif',
                label: 'Georgia',
            },{
                value: '"Palatino Linotype", "Book Antiqua", Palatino, serif',
                label: 'Palatino',
            },{
                value: '"Times New Roman", Times, serif',
                label: 'Times New Roman',
            },
        ],
    },{
        label: 'Default Sans-Serif Fonts',
        options: [
            {
                value: 'Arial, Helvetica, sans-serif',
                label: 'Arial',
            },{
                value: '"Arial Black", Gadget, sans-serif',
                label: 'Arial Black',
            },{
                value: '"Comic Sans MS", cursive, sans-serif',
                label: 'Comic Sans MS',
                
            },{
                value: 'Helvetica, sans-serif',
                label: 'Helvetica',
            },{
                value: 'Impact, Charcoal, sans-serif',
                label: 'Impact',
            },{
                value: '"Lucida Sans Unicode", "Lucida Grande", sans-serif',
                label: 'Lucida',
            },{
                value: 'Tahoma, Geneva, sans-serif',
                label: 'Tahoma',
            },{
                value: '"Trebuchet MS", Helvetica, sans-serif',
                label: 'Trebuchet MS',
            },{
                value: 'Verdana, Geneva, sans-serif',
                label: 'Verdana',
            },
        ],
    },{
        label: 'Default Monospace Fonts',
        options: [
            {
                value: '"Courier New", Courier, monospace',
                label: 'Courier New',
            },{
                value: '"Lucida Console", Monaco, monospace',
                label: 'Lucida Console',
            },
        ],
    }];

    const selectOptions = groupedOptions.reduce((groups, group) => groups.concat(group.options), []);
    const selectValues = selectOptions.map(option => option.value);
    const custom = ! props.value || ! selectValues.includes(props.value);
    const selectValue = custom ? 'custom' : props.value;

    const selectStyles = {
        option: (styles, { data, isDisabled, isFocused, isSelected }) => {
          const fontFamily = 'custom' === data.value ? 'inherit' : data.value;

          return {
            ...styles,
            fontFamily,
          };
        },
    };

    return (
        <Fragment>
            <Select
                className="wprm-template-property-input"
                menuPlacement="top"
                value={selectOptions.filter(({value}) => value === selectValue)}
                onChange={(option) => {
                    const value = 'custom' === option.value ? '' : option.value;
                    return props.onValueChange(value);
                }}
                options={groupedOptions}
                styles={selectStyles}
                clearable={false}
            />
            {
                custom
                &&
                <input
                    className="wprm-template-property-input"
                    type="text"
                    value={props.value}
                    onChange={(e) => props.onValueChange(e.target.value)}
                />
            }
        </Fragment>
    );
}

export default PropertyFont;