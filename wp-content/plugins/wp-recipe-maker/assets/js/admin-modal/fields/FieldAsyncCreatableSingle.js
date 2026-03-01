import React, { Component } from 'react';
import AsyncCreatableSelect from 'react-select/async-creatable';

import { __wprm } from 'Shared/Translations';

export default class FieldAsyncCreatableSingle extends Component {
    normalizeOptions( options = [] ) {
        const normalized = [];
        const seen = {};

        options.forEach( ( option ) => {
            let value = '';
            let label = '';

            if ( 'string' === typeof option ) {
                value = option.trim();
                label = value;
            } else if ( option && option.hasOwnProperty( 'value' ) ) {
                value = `${ option.value }`.trim();
                label = option.hasOwnProperty( 'label' ) ? `${ option.label }`.trim() : value;
            }

            if ( value ) {
                const key = value.toLowerCase();

                if ( ! seen.hasOwnProperty( key ) ) {
                    seen[ key ] = true;
                    normalized.push({
                        value,
                        label: label ? label : value,
                    });
                }
            }
        });

        return normalized;
    }

    getSelectedOption() {
        const value = 'string' === typeof this.props.value ? this.props.value.trim() : '';

        if ( ! value ) {
            return null;
        }

        return {
            value,
            label: value,
        };
    }

    loadOptions( inputValue ) {
        if ( 'function' !== typeof this.props.loadOptions ) {
            return Promise.resolve([]);
        }

        return Promise.resolve( this.props.loadOptions( inputValue ) ).then( ( options ) => {
            return this.normalizeOptions( options );
        }).catch(() => {
            return [];
        });
    }

    render() {
        const customProps = this.props.custom ? this.props.custom : {};
        const defaultOptions = this.normalizeOptions( this.props.defaultOptions );

        return (
            <AsyncCreatableSelect
                defaultOptions={ defaultOptions }
                loadOptions={ this.loadOptions.bind( this ) }
                value={ this.getSelectedOption() }
                isClearable
                placeholder={ this.props.placeholder ? this.props.placeholder : __wprm( 'Select from list or type to create...' ) }
                onChange={ ( option ) => {
                    let value = '';

                    if ( option ) {
                        value = option.hasOwnProperty( '__isNew__' ) && option.__isNew__ ? option.label : option.value;
                        value = `${ value }`.trim();
                    }

                    this.props.onChange( value );
                }}
                styles={{
                    placeholder: (provided) => ({
                        ...provided,
                        color: '#444',
                        opacity: '0.333',
                    }),
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
