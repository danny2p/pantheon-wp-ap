import React, { Fragment, useEffect } from 'react';
import ReactDOM from 'react-dom';
import { Editor, Transforms } from 'slate';
import { useFocused, useSlate } from 'slate-react';
import he from 'he';

import { __wprm } from 'Shared/Translations';
import InlineIngredientsHelper from './InlineIngredientsHelper';

import { serialize } from '../../../fields/FieldRichText/html';

const InlineIngredients = (props) => {
    const inlineIngredientsPortal = document.getElementById( 'wprm-admin-modal-field-instruction-inline-ingredients-portal' );

    if ( ! inlineIngredientsPortal ) {
        return null;
    }

    // Only show when focussed (needs to be after useSlate()).
	const focused = useFocused();
	if ( ! focused ) {
		return null;
	}

	// Get values for suggestions.
	let editor;
	let value = '';
    
	editor = useSlate();
    value = serialize( editor );

    // Get all ingredients used in this instruction step.
    const ingredientUidsInCurrent = InlineIngredientsHelper.findAll( value ).map( (ingredient) => ingredient.uid );

    // Get all ingredients used in any instruction step.
    let ingredientUidsInAll = [];

    for ( let instruction of props.instructions ) {
        if ( instruction.hasOwnProperty( 'type' ) && 'instruction' === instruction.type && instruction.hasOwnProperty( 'text' ) ) {
            ingredientUidsInAll = ingredientUidsInAll.concat( InlineIngredientsHelper.findAll( instruction.text ).map( (ingredient) => ingredient.uid ) );
        }
    }

    // Get All Ingredients.
    const allIngredients = props.hasOwnProperty( 'allIngredients' ) && props.allIngredients ? props.allIngredients : [];

    // Set to false further down if we find an unused ingredient
    let allIngredientsAreUsed = allIngredients.length ? true : false;

    return ReactDOM.createPortal(
        <Fragment>
            <div
                className="wprm-admin-modal-field-instruction-inline-ingredients"
                onMouseDown={ (event) => {
                    event.preventDefault();
                }}
            >
                {
                    allIngredients.map( ( ingredient, index ) => {
                        if ( 'ingredient' === ingredient.type ) {
                            const ingredientString = InlineIngredientsHelper.getIngredientText( ingredient );
                
                            if ( ingredientString ) {
                                let classes = [
                                    'wprm-admin-modal-field-instruction-inline-ingredient',
                                ];

                                // Check if ingredient is already used.
                                if ( ingredientUidsInCurrent.includes( ingredient.uid ) ) {
                                    classes.push( 'wprm-admin-modal-field-instruction-inline-ingredient-in-current' );
                                } else if ( ingredientUidsInAll.includes( ingredient.uid ) ) {
                                    classes.push( 'wprm-admin-modal-field-instruction-inline-ingredient-in-other' );
                                } else {
                                    allIngredientsAreUsed = false;
                                }

                                return (
                                    <a
                                        href="#"
                                        className={ classes.join( ' ' ) }
                                        onMouseDown={ (e) => {
                                            e.preventDefault();

                                            let node = {
                                                type: 'ingredient',
                                                uid: ingredient.uid,
                                                children: [{ text: ingredientString }],
                                            };

                                            Transforms.insertNodes( editor, node );
                                        }}
                                        key={ index }
                                    >{ he.decode( ingredientString ) }</a>
                                );
                            }
                        }

                        return null;
                    })
                }
            </div>
            {
                allIngredientsAreUsed
                && <div className="wprm-admin-modal-field-instruction-inline-ingredients-info">{ __wprm( 'All ingredients have been added in a step!' ) }</div>
            }
        </Fragment>,
        inlineIngredientsPortal,
    );
}
export default InlineIngredients;