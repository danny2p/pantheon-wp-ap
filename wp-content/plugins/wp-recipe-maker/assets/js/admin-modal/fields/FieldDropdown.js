import React, { Component } from 'react';
import Select from 'react-select';

export default class FieldDropdown extends Component {
    shouldComponentUpdate(nextProps) {
        return JSON.stringify(this.props.options) !== JSON.stringify(nextProps.options) || this.props.value !== nextProps.value || this.props.isDisabled !== nextProps.isDisabled;
    }

    render() {
        let selectedOption = false;

        if ( this.props.options ) {
            const allOptions = this.props.options.reduce((acc, cur) => {
                if ( cur.hasOwnProperty('options') ) {
                    acc = acc.concat( cur.options );
                } else {
                    acc.push(cur);
                }
        
                return acc;
            }, []);

            selectedOption = allOptions.find((option) => option.value === this.props.value);
        }

        const customProps = this.props.custom ? this.props.custom : {};

        return (
            <Select
                isDisabled={ this.props.isDisabled }
                options={this.props.options}
                value={selectedOption}
                placeholder={this.props.placeholder}
                onChange={(option) => {
                    this.props.onChange(option.value);
                }}
                styles={{
                    control: (provided) => ({
                        ...provided,
                        backgroundColor: 'white',
                    }),
                    container: (provided) => ({
                        ...provided,
                        width: '100%',
                        maxWidth: this.props.width ? this.props.width : '100%',
                    }),
                }}
                { ...customProps }
            />
        );
    }
}