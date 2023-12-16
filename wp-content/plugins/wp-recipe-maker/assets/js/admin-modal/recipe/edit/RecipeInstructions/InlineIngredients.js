import React, { Fragment, useEffect } from 'react';
import ReactDOM from 'react-dom';
import { Editor, Transforms } from 'slate';
import { useFocused, useSlate } from 'slate-react';
import he from 'he';

import { __wprm } from 'Shared/Translations';
import InlineIngredientsHelper from './InlineIngredientsHelper';

import { serialize } from '../../../fields/FieldRichText/html';
import { off } from 'medium-editor';

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
    let allIngredients = props.hasOwnProperty( 'allIngredients' ) && props.allIngredients ? props.allIngredients : [];
    allIngredients = allIngredients.filter( ( ingredient ) => 'ingredient' === ingredient.type );

    // Set to false further down if we find an unused ingredient
    let allIngredientsAreUsed = allIngredients.length ? true : false;

    // Show inline ingredients next to active element.
    const activeElement = document.activeElement;

    // Get position of the parent instruction, relative to its parent.
    const instruction = activeElement.closest( '.wprm-admin-modal-field-instruction' );
    const instructionOffset = instruction.offsetTop;

    // Get offset the portal already has.
    const portal = document.getElementById( 'wprm-admin-modal-field-instruction-inline-ingredients-portal' );
    const portalOffset = portal.offsetTop;

    const ingredientsMiddle = allIngredients.length * 18 / 2;
    let offsetToAdd = instructionOffset - portalOffset - ingredientsMiddle;

    // Maximum offset to add.
    const instructionsContainer = document.getElementsByClassName( 'wprm-admin-modal-field-instruction-container' )[0];
    const maxOffset = instructionsContainer.offsetHeight - 2 * ingredientsMiddle - portalOffset - 20;

    offsetToAdd = Math.min( offsetToAdd, maxOffset );

    return ReactDOM.createPortal(
        <Fragment>
            {
                offsetToAdd > 0
                && <div className="wprm-admin-modal-field-instruction-inline-ingredients-offset" style={{ height: offsetToAdd }}></div>
            }
            <div
                className="wprm-admin-modal-field-instruction-inline-ingredients"
                onMouseDown={ (event) => {
                    event.preventDefault();
                }}
            >
                {
                    allIngredients.map( ( ingredient, index ) => {
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
                                            children: [{ text: he.decode( ingredientString ) }],
                                        };

                                        Transforms.insertNodes( editor, node );
                                    }}
                                    key={ index }
                                >{ he.decode( ingredientString ) }</a>
                            );
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