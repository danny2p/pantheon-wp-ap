import React, { Fragment, useMemo, useRef } from 'react';
import ReactDOM from 'react-dom';
import { Editor, Transforms } from 'slate';
import { useFocused, useSlate } from 'slate-react';
import he from 'he';

import { __wprm } from 'Shared/Translations';
import InlineIngredientsHelper from './InlineIngredientsHelper';

import { serialize } from '../../../fields/FieldRichText/html';
import { off } from 'medium-editor';

const InlineIngredientsInner = (props) => {
	// Get editor instance.
	const editor = useSlate();
    
    // Memoize serialization - only recalculate when editor content changes.
    // Using editor.children as dependency since that's what actually changes on content updates.
    // Note: editor.children is a new array reference when content changes in Slate.
    const value = useMemo(() => {
        return serialize( editor );
    }, [editor.children]);

    // Memoize ingredient UIDs in current instruction - only recalculate when value changes.
    const ingredientUidsInCurrent = useMemo(() => {
        return InlineIngredientsHelper.findAll( value ).map( (ingredient) => ingredient.uid );
    }, [value]);

    // Cache for parsed instructions to avoid re-parsing unchanged text on every render.
    const parsedInstructionsCache = useRef({});

    // Get instructions from ref or prop.
    let instructions = props.instructionsRef ? props.instructionsRef.current : props.instructions;
    
    // Fallback if instructions is somehow null/undefined.
    if ( ! Array.isArray( instructions ) ) {
        instructions = [];
    }

    // Memoize ingredient UIDs in all instructions - only recalculate when instructions change.
    const ingredientUidsInAll = useMemo(() => {
        // console.time('calcIngredients'); // Start timer
        let uids = [];
        // let cacheHits = 0; // Count hits
        const cache = parsedInstructionsCache.current;
        
        for ( let instruction of instructions ) {
            if ( instruction.hasOwnProperty( 'type' ) && 'instruction' === instruction.type && instruction.hasOwnProperty( 'text' ) ) {
                // Use UID as key if available, otherwise fallback to index or something else? 
                // Instructions usually have UIDs.
                const key = instruction.uid;
                
                if ( key !== undefined ) {
                    // Check cache.
                    if ( cache[key] && cache[key].text === instruction.text ) {
                        // cacheHits++; // Increment hits
                        uids = uids.concat( cache[key].uids );
                    } else {
                        // Parse and cache.
                        const foundUids = InlineIngredientsHelper.findAll( instruction.text ).map( (ingredient) => ingredient.uid );
                        cache[key] = {
                            text: instruction.text,
                            uids: foundUids,
                        };
                        uids = uids.concat( foundUids );
                    }
                } else {
                    // Fallback if no UID (shouldn't happen based on data structure).
                    uids = uids.concat( InlineIngredientsHelper.findAll( instruction.text ).map( (ingredient) => ingredient.uid ) );
                }
            }
        }
        // console.timeEnd('calcIngredients'); // End timer
        // console.log(`Cache hits: ${cacheHits}/${instructions.length}`);

        return uids;
    }, [instructions]); // If instructionsRef changes (mutated), this won't trigger re-calc?
    // Wait, useMemo dependency on mutable ref.current doesn't work if the ref object itself is stable.
    // But we mount a NEW InlineIngredientsInner when focus changes?
    // OR we rely on parent re-rendering?
    
    // If FieldInstruction does NOT re-render, then InlineIngredientsInner does NOT re-render.
    // BUT: The issue is "outdated version in others".
    // This means when I switch focus to B, B mounts InlineIngredients.
    // At mount time, it reads instructionsRef.current.
    // instructionsRef.current IS up to date because Parent updated it.
    // So initial render is correct.
    
    // What about staying focused in A and typing?
    // Typing in A -> A re-renders -> A's InlineIngredients updates.
    
    // So this pattern works!
    // Only caveat: useMemo won't update if we were somehow keeping this mounted and only ref.current changed.
    // But we only care about "fresh data on mount".
    // And "update on typing in THIS field".
    
    // If I type in A, `instructions` array updates.
    // But `FieldInstruction` A *does* re-render (because text changed).
    // So `InlineIngredients` in A receives new props/renders.
    
    // So it seems fine.

    // Memoize filtered ingredients - only recalculate when allIngredients prop changes.
    const allIngredients = useMemo(() => {
        let ingredients = props.hasOwnProperty( 'allIngredients' ) && props.allIngredients ? props.allIngredients : [];
        return ingredients.filter( ( ingredient ) => 'ingredient' === ingredient.type );
    }, [props.allIngredients]);

    // Memoize the calculation of whether all ingredients are used.
    // This depends on allIngredients, ingredientUidsInCurrent, and ingredientUidsInAll.
    const allIngredientsAreUsed = useMemo(() => {
        if ( ! allIngredients.length ) {
            return false;
        }
        
        // Check if all ingredients are used in current or other instructions.
        for ( let ingredient of allIngredients ) {
            if ( ! ingredientUidsInCurrent.includes( ingredient.uid ) && ! ingredientUidsInAll.includes( ingredient.uid ) ) {
                return false;
            }
        }
        return true;
    }, [allIngredients, ingredientUidsInCurrent, ingredientUidsInAll]);

    // Calculate DOM positioning - these need to happen on every render for accurate positioning.
    // DOM queries are relatively fast compared to serialization, so we don't memoize these.
    const activeElement = document.activeElement;
    const instruction = activeElement.closest( '.wprm-admin-modal-field-instruction' );
    const instructionOffset = instruction ? instruction.offsetTop : 0;

    const portal = props.portal;
    const portalOffset = portal ? portal.offsetTop : 0;

    const ingredientsMiddle = allIngredients.length * 18 / 2;
    let offsetToAdd = instructionOffset - portalOffset - ingredientsMiddle;

    // Maximum offset to add.
    const instructionsContainer = document.getElementsByClassName( 'wprm-admin-modal-field-instruction-container' )[0];
    if ( instructionsContainer ) {
        const maxOffset = instructionsContainer.offsetHeight - 2 * ingredientsMiddle - portalOffset - 20;
        offsetToAdd = Math.min( offsetToAdd, maxOffset );
    }

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
        portal,
    );
}

const InlineIngredients = (props) => {
    const inlineIngredientsPortal = document.getElementById( 'wprm-admin-modal-field-instruction-inline-ingredients-portal' );

    if ( ! inlineIngredientsPortal ) {
        return null;
    }

    // Only show when focussed.
	const focused = useFocused();
	if ( ! focused ) {
		return null;
	}

    return <InlineIngredientsInner {...props} portal={inlineIngredientsPortal} />;
}

export default InlineIngredients;
