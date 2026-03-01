import React, { Fragment } from 'react';
import Select from 'react-select';
import he from 'he';

import Helpers from 'Shared/Helpers';
import { __wprm } from 'Shared/Translations';
import { parseQuantity, formatQuantity } from 'Shared/quantities';

const FieldInstructionIngredients = (props) => {
    const ingredients = props.hasOwnProperty( 'ingredients' ) ? props.ingredients : [];

    let usedIngredientOptions = [];
    let unusedIngredientOptions = [];
    let selectedIngredients = [];

    // Helper to check if a value (uid or uid:splitId) is in the used list
    const isUsed = (value) => {
        // Convert value to string for comparison
        const valueStr = String( value );
        // Check exact match
        if ( props.usedIngredients.includes( valueStr ) || props.usedIngredients.includes( value ) ) {
            return true;
        }
        // If it's a split, also check if parent ingredient is used
        if ( valueStr.includes( ':' ) ) {
            const parentUid = parseInt( valueStr.split( ':' )[0] );
            if ( props.usedIngredients.includes( parentUid ) || props.usedIngredients.includes( String( parentUid ) ) ) {
                return true;
            }
        }
        // For full ingredients, only check exact match (don't check splits - that's handled separately)
        return false;
    };

    // Helper to check if a value is selected
    const isSelected = (value) => {
        const valueStr = String( value );
        return ingredients.includes( valueStr ) || ingredients.includes( value ) ||
               ( typeof value === 'number' && ingredients.includes( String( value ) ) );
    };

    for ( let ingredient of props.allIngredients ) {
        if ( 'ingredient' === ingredient.type ) {
            const ingredientString = Helpers.getIngredientString( ingredient );
            const hasSplits = ingredient.splits && Array.isArray( ingredient.splits ) && ingredient.splits.length >= 2;

            if ( ingredientString ) {
                const ingredientIsUsed = isUsed( ingredient.uid );
                
                // Check splits usage - check directly in usedIngredients array
                let hasUsedSplits = false;
                let hasUnusedSplits = false;
                
                if ( hasSplits ) {
                    for ( let split of ingredient.splits ) {
                        if ( split.percentage !== undefined && split.percentage !== null ) {
                            const splitValue = `${ingredient.uid}:${split.id}`;
                            const splitValueStr = String( splitValue );
                            // Check if this specific split is used - check all possible formats
                            const splitIsUsed = props.usedIngredients.some( used => {
                                const usedStr = String( used );
                                return usedStr === splitValueStr || usedStr === splitValue || used === splitValue || used === splitValueStr;
                            });
                            if ( splitIsUsed ) {
                                hasUsedSplits = true;
                            } else {
                                hasUnusedSplits = true;
                            }
                        }
                    }
                }

                // If ingredient has splits and they're in different states, show ingredient in both groups
                const showInBothGroups = hasSplits && hasUsedSplits && hasUnusedSplits;
                
                // Add to unused group if ingredient is not used and:
                // - No splits exist, OR
                // - Has unused splits (which includes showInBothGroups case)
                const shouldShowInUnused = ! ingredientIsUsed && ( ! hasSplits || hasUnusedSplits );
                
                if ( shouldShowInUnused ) {
                    const ingredientOptionUnused = {
                        value: ingredient.uid,
                        label: he.decode( ingredientString ),
                    };
                    unusedIngredientOptions.push( ingredientOptionUnused );

                    // Add unused splits under unused group
                    if ( hasSplits ) {
                        for ( let split of ingredient.splits ) {
                            if ( split.percentage !== undefined && split.percentage !== null ) {
                                const splitValue = `${ingredient.uid}:${split.id}`;
                                const splitValueStr = String( splitValue );
                                // Check directly if this split is used - check all possible formats
                                const splitIsUsed = props.usedIngredients.some( used => {
                                    const usedStr = String( used );
                                    return usedStr === splitValueStr || usedStr === splitValue || used === splitValue || used === splitValueStr;
                                });
                                if ( ! splitIsUsed ) {
                                    // Calculate split amount from parent amount and percentage
                                    const parentAmount = parseQuantity( ingredient.amount || '0' );
                                    const percentage = parseFloat( split.percentage ) || 0;
                                    let splitAmount = '';
                                    if ( parentAmount > 0 && ! isNaN( percentage ) ) {
                                        const calculated = ( parentAmount * percentage ) / 100;
                                        const decimals = typeof wprm_admin !== 'undefined' && wprm_admin.settings ? parseInt( wprm_admin.settings.adjustable_servings_round_to_decimals ) || 2 : 2;
                                        const allowFractions = typeof wprm_admin !== 'undefined' && wprm_admin.settings ? ( wprm_admin.settings.fractions_enabled || false ) : false;
                                        splitAmount = formatQuantity( calculated, decimals, allowFractions );
                                    }
                                    const splitUnit = ingredient.unit || '';
                                    const splitName = ingredient.name || '';
                                    const splitString = splitAmount ? `${splitAmount} ${splitUnit} ${splitName}`.trim() : `${percentage}% ${splitName}`.trim();
                                    
                                    const splitOption = {
                                        value: splitValue,
                                        label: `  └ ${he.decode( splitString )}`,
                                    };
                                    unusedIngredientOptions.push( splitOption );
                                    
                                    if ( isSelected( splitValue ) ) {
                                        selectedIngredients.push( splitOption );
                                    }
                                }
                            }
                        }
                    }
                }

                // Add to used group if used or has used splits (or show in both if needed)
                if ( ingredientIsUsed || hasUsedSplits || showInBothGroups ) {
                    const ingredientOptionUsed = {
                        value: ingredient.uid,
                        label: he.decode( ingredientString ),
                    };
                    usedIngredientOptions.push( ingredientOptionUsed );

                    // Add used splits under used group
                    if ( hasSplits ) {
                        for ( let split of ingredient.splits ) {
                            if ( split.percentage !== undefined && split.percentage !== null ) {
                                const splitValue = `${ingredient.uid}:${split.id}`;
                                const splitValueStr = String( splitValue );
                                // Check directly if this split is used - check all possible formats
                                const splitIsUsed = props.usedIngredients.some( used => {
                                    const usedStr = String( used );
                                    return usedStr === splitValueStr || usedStr === splitValue || used === splitValue || used === splitValueStr;
                                });
                                if ( splitIsUsed ) {
                                    // Calculate split amount from parent amount and percentage
                                    const parentAmount = parseQuantity( ingredient.amount || '0' );
                                    const percentage = parseFloat( split.percentage ) || 0;
                                    let splitAmount = '';
                                    if ( parentAmount > 0 && ! isNaN( percentage ) ) {
                                        const calculated = ( parentAmount * percentage ) / 100;
                                        const decimals = typeof wprm_admin !== 'undefined' && wprm_admin.settings ? parseInt( wprm_admin.settings.adjustable_servings_round_to_decimals ) || 2 : 2;
                                        const allowFractions = typeof wprm_admin !== 'undefined' && wprm_admin.settings ? ( wprm_admin.settings.fractions_enabled || false ) : false;
                                        splitAmount = formatQuantity( calculated, decimals, allowFractions );
                                    }
                                    const splitUnit = ingredient.unit || '';
                                    const splitName = ingredient.name || '';
                                    const splitString = splitAmount ? `${splitAmount} ${splitUnit} ${splitName}`.trim() : `${percentage}% ${splitName}`.trim();
                                    
                                    const splitOption = {
                                        value: splitValue,
                                        label: `  └ ${he.decode( splitString )}`,
                                    };
                                    usedIngredientOptions.push( splitOption );
                                    
                                    if ( isSelected( splitValue ) ) {
                                        selectedIngredients.push( splitOption );
                                    }
                                }
                            }
                        }
                    }
                }

                // Handle selection for ingredient (only add once, prefer used group if in both)
                if ( isSelected( ingredient.uid ) ) {
                    if ( showInBothGroups ) {
                        // If in both groups, use the used group option
                        const usedOption = usedIngredientOptions.find( opt => opt.value === ingredient.uid );
                        if ( usedOption ) {
                            selectedIngredients.push( usedOption );
                        }
                    } else if ( ingredientIsUsed || hasUsedSplits ) {
                        const usedOption = usedIngredientOptions.find( opt => opt.value === ingredient.uid );
                        if ( usedOption ) {
                            selectedIngredients.push( usedOption );
                        }
                    } else {
                        const unusedOption = unusedIngredientOptions.find( opt => opt.value === ingredient.uid );
                        if ( unusedOption ) {
                            selectedIngredients.push( unusedOption );
                        }
                    }
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
                formatOptionLabel={({ label, value }) => {
                    // Check if this is a split (value contains ':')
                    const isSplit = String( value ).includes( ':' );
                    return (
                        <div style={{ 
                            color: isSplit ? '#646970' : 'inherit',
                            fontStyle: isSplit ? 'italic' : 'normal',
                        }}>
                            {label}
                        </div>
                    );
                }}
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