import React, { Component } from 'react';
import he from 'he';
import Select from 'react-select';
import CreatableSelect from 'react-select/creatable';

import { __wprm } from 'Shared/Translations';

export default class FieldCategory extends Component {
    shouldComponentUpdate(nextProps) {
        return this.props.id !== nextProps.id
               || JSON.stringify(this.props.value) !== JSON.stringify(nextProps.value);
    }

    render() {
        const categories = wprm_admin_modal.categories[ this.props.id ].terms;
        let categoryOptions = [];
        let selectedCategories = [];

        for ( let category of categories ) {
            const categoryOption = {
                value: category.term_id,
                label: he.decode( category.name ),
            };

            categoryOptions.push(categoryOption);

            if ( this.props.value.find((elem) => elem.term_id === category.term_id || elem.name === category.term_id ) ) {
                selectedCategories.push(categoryOption);
            }
        }

        const customProps = this.props.custom ? this.props.custom : {};
        const SelectElem = this.props.creatable ? CreatableSelect : Select;

        return (
            <SelectElem
                isMulti
                options={categoryOptions}
                value={selectedCategories}
                placeholder={ this.props.creatable ? __wprm( 'Select from list or type to create...' ) : __wprm( 'Select from list...' ) }
                onChange={(value) => {
                    let newValue = [];

                    if ( value ) {
                        for ( let category of value ) {
                            if ( category.hasOwnProperty('__isNew__') && category.__isNew__ ) {
                                wprm_admin_modal.categories[ this.props.id ].terms.push({
                                    term_id: category.label,
                                    name: category.label,
                                });
                            }
    
                            let selectedCategory = wprm_admin_modal.categories[ this.props.id ].terms.find((cat) => cat.term_id === category.value);
    
                            if ( selectedCategory ) {
                                newValue.push(selectedCategory);
                            }
                        }
                    }

                    this.props.onChange(newValue);
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