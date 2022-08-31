import React, { Fragment } from 'react';
import Select from 'react-select';
import he from 'he';

import Helpers from 'Shared/Helpers';
import { __wprm } from 'Shared/Translations';

const FieldInstructionIngredients = (props) => {
    const ingredients = props.hasOwnProperty( 'ingredients' ) ? props.ingredients : [];

    let usedIngredientOptions = [];
    let unusedIngredientOptions = [];
    let selectedIngredients = [];

    for ( let ingredient of props.allIngredients ) {
        if ( 'ingredient' === ingredient.type ) {
            const ingredientString = Helpers.getIngredientString( ingredient );

            if ( ingredientString ) {
                const ingredientOption = {
                    value: ingredient.uid,
                    label: he.decode( ingredientString ),
                };

                // Put in correct group.
                if ( props.usedIngredients.includes( ingredient.uid ) ) {
                    usedIngredientOptions.push( ingredientOption );                    
                } else {
                    unusedIngredientOptions.push( ingredientOption );
                }

                // Maybe mark as selected.
                if ( ingredients.includes( ingredient.uid ) ) {
                    selectedIngredients.push( ingredientOption );
                }
            }
        }
    }

    const ingredientOptions = [{
        label: __wprm( 'Not associated yet' ),
        options: unusedIngredientOptions,
    },{
        label: __wprm( 'Already Associated' ),
        options: usedIngredientOptions,
    }];

    return (
        <div className="wprm-admin-modal-field-instruction-after-container-ingredient">
            <Select
                isMulti
                options={ingredientOptions}
                value={selectedIngredients}
                placeholder={ __wprm( 'Select ingredients...' ) }
                onChange={(value) => {
                    let newIngredients = [];

                    if ( value ) {
                        for ( let ingredient of value ) {
                            newIngredients.push( ingredient.value );
                        }
                    }

                    props.onChangeIngredients( newIngredients );
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
                        maxWidth: '100%',
                    }),
                }}
            />
        </div>
    );
}
export default FieldInstructionIngredients;