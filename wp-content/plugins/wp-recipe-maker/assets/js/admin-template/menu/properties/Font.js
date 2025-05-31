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
        label: 'Google Fonts - Serif',
        options: [
            {
                value: '"Asar", serif',
                label: 'Asar',
                loadFont: 'asar',
            },
            {
                value: '"Cormorant Garamond", serif',
                label: 'Cormorant Garamond',
                loadFont: 'cormorant-garamond',
            },
            {
                value: '"Gloock", serif',
                label: 'Gloock',
                loadFont: 'gloock',
            },
            {
                value: '"Lora", serif',
                label: 'Lora',
                loadFont: 'lora',
            },
            {
                value: '"Playfair Display", serif',
                label: 'Playfair Display',
                loadFont: 'playfair-display',
            },
        ],
    },{
        label: 'Google Fonts - Sans Serif',
        options: [
            {
                value: '"Inter", sans-serif',
                label: 'Inter',
                loadFont: 'inter',
            },
            {
                value: '"Jost", sans-serif',
                label: 'Jost',
                loadFont: 'jost',
            },
            {
                value: '"Mulish", sans-serif',
                label: 'Mulish',
                loadFont: 'mulish',
            },
            {
                value: '"Nunito", sans-serif',
                label: 'Nunito',
                loadFont: 'nunito',
            },
            {
                value: '"Open Sans", sans-serif',
                label: 'Open Sans',
                loadFont: 'open-sans',
            },
            {
                value: '"Poppins", sans-serif',
                label: 'Poppins',
                loadFont: 'poppins',
            },
            {
                value: '"Quicksand", sans-serif',
                label: 'Quicksand',
                loadFont: 'quicksand',
            },
        ],
    },{
        label: 'Google Fonts - Cursive',
        options: [
            {
                value: '"Amatic SC", cursive',
                label: 'Amatic SC',
                loadFont: 'amatic-sc',
            },
            {
                value: '"Caveat", cursive',
                label: 'Caveat',
                loadFont: 'caveat',
            },
            {
                value: '"Dancing Script", cursive',
                label: 'Dancing Script',
                loadFont: 'dancing-script',
            },
            {
                value: '"Pacifico", cursive',
                label: 'Pacifico',
                loadFont: 'pacifico',
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
    const selectedOption = selectOptions.find(({value}) => value === selectValue);

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
                value={selectedOption}
                onChange={(option) => {
                    const newValue = 'custom' === option.value ? '' : option.value;

                    // Check if new font should be saved.
                    if ( option.hasOwnProperty( 'loadFont' ) ) {
                        let newFonts = props.fonts;
                        newFonts.push( option.loadFont );
                        props.onChangeFonts(newFonts);
                    }

                    // Check if old font should be removed.
                    if ( selectedOption.hasOwnProperty( 'loadFont' ) && props.fonts.includes( selectedOption.loadFont ) ) {
                        let newFonts = props.fonts;

                        // Only remove once, could be multiple times in array.
                        const index = newFonts.indexOf( selectedOption.loadFont );
                        if ( index !== -1 ) {
                            newFonts.splice(index, 1);
                        }

                        props.onChangeFonts(newFonts);
                    }

                    return props.onValueChange(newValue);
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