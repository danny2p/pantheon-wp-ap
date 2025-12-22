import React, { Component, Fragment } from 'react';

import Api from 'Shared/Api';
import FieldContainer from '../../fields/FieldContainer';
import FieldText from '../../fields/FieldText';
import Button from 'Shared/Button';
import { __wprm } from 'Shared/Translations';
import Loader from 'Shared/Loader';
import Icon from 'Shared/Icon';

import '../../../../css/admin/modal/recipe/fields/nutrition.scss';

const needsCalculation = Object.values(wprm_admin_modal.nutrition).findIndex((options) => 'calculated' === options.type) !== -1;

export default class RecipeNutrition extends Component {
    constructor(props) {
        super(props);
    
        this.state = {
            calculating: false,
        }

        // Store original state when component is created
        this.originalState = null;
        this.justRecalculated = false;
    }

    shouldComponentUpdate(nextProps, nextState) {
        return this.state.calculing !== nextState.calculating
               || JSON.stringify( this.props.servings ) !== JSON.stringify( nextProps.servings )
               || JSON.stringify( this.props.nutrition ) !== JSON.stringify( nextProps.nutrition )
               || JSON.stringify( this.props.ingredients ) !== JSON.stringify( nextProps.ingredients );
    }

    componentDidMount() {
        this.calculateNutrients();
        this.storeOriginalState();
        this.notifyWarningChange();
    }

    componentDidUpdate(prevProps) {
        if ( JSON.stringify( this.props.nutrition ) !== JSON.stringify( prevProps.nutrition ) ) {
            this.calculateNutrients();
        }

        // Update original state if nutrition was just calculated
        if ( this.justRecalculated ) {
            this.justRecalculated = false;
            this.storeOriginalState();
        } else if ( ! this.originalState && this.hasNutritionFacts() ) {
            // Update original state if nutrition was just set for the first time
            this.storeOriginalState();
        }

        // Notify parent if warning state changed
        if ( JSON.stringify( this.props.ingredients ) !== JSON.stringify( prevProps.ingredients ) ||
             JSON.stringify( this.props.servings ) !== JSON.stringify( prevProps.servings ) ||
             JSON.stringify( this.props.nutrition ) !== JSON.stringify( prevProps.nutrition ) ) {
            this.notifyWarningChange();
        }
    }

    notifyWarningChange() {
        if ( this.props.onWarningChange ) {
            const hasWarning = this.hasSignificantChanges() !== null;
            this.props.onWarningChange( hasWarning );
        }
    }

    storeOriginalState() {
        // Only store original state if nutrition facts exist
        if ( this.hasNutritionFacts() ) {
            this.originalState = {
                ingredients: JSON.parse( JSON.stringify( this.props.ingredients ) ),
                servings: JSON.parse( JSON.stringify( this.props.servings ) ),
            };
        }
    }

    hasNutritionFacts() {
        const nutrition = this.props.nutrition;
        if ( ! nutrition || typeof nutrition !== 'object' ) {
            return false;
        }

        // Check if any manually set nutrient field (besides serving_size, serving_unit, and calculated nutrients) has a value
        for ( const nutrient in nutrition ) {
            if ( 'serving_size' !== nutrient && 'serving_unit' !== nutrient ) {
                // Skip calculated nutrients - they're automatically computed and shouldn't trigger warnings
                const nutrientOptions = wprm_admin_modal.nutrition && wprm_admin_modal.nutrition[nutrient];
                if ( nutrientOptions && 'calculated' === nutrientOptions.type ) {
                    continue;
                }
                
                const value = nutrition[ nutrient ];
                if ( value !== false && value !== '' && value !== null && value !== undefined ) {
                    return true;
                }
            }
        }

        return false;
    }

    hasValidIngredient(ingredient) {
        // An ingredient is valid if it has at least a name
        return ingredient && ingredient.name && ingredient.name.trim() !== '';
    }

    hasSignificantChanges() {
        // Only check if we have nutrition facts and original state
        if ( ! this.hasNutritionFacts() || ! this.originalState ) {
            return null;
        }

        const currentIngredients = this.props.ingredients || [];
        const originalIngredients = this.originalState.ingredients || [];
        const currentServings = this.props.servings || { amount: '', unit: '' };
        const originalServings = this.originalState.servings || { amount: '', unit: '' };

        const changes = {
            servingsChanged: false,
            servingsDetails: null,
            ingredientsChanged: [],
            ingredientsAdded: [],
            ingredientsRemoved: [],
        };

        // Check if serving size changed
        if ( currentServings.amount !== originalServings.amount || currentServings.unit !== originalServings.unit ) {
            changes.servingsChanged = true;
            const originalServingsText = originalServings.amount && originalServings.unit 
                ? `${originalServings.amount} ${originalServings.unit}` 
                : (originalServings.amount || originalServings.unit || __wprm( 'not set' ));
            const currentServingsText = currentServings.amount && currentServings.unit 
                ? `${currentServings.amount} ${currentServings.unit}` 
                : (currentServings.amount || currentServings.unit || __wprm( 'not set' ));
            changes.servingsDetails = {
                from: originalServingsText,
                to: currentServingsText,
            };
        }

        // Filter to only ingredient items (not groups) and only valid ingredients (have at least a name)
        const currentIngredientsList = currentIngredients.filter( ing => 'ingredient' === ing.type && this.hasValidIngredient(ing) );
        const originalIngredientsList = originalIngredients.filter( ing => 'ingredient' === ing.type && this.hasValidIngredient(ing) );

        // Create a map of original ingredients by uid for comparison
        const originalMap = {};
        originalIngredientsList.forEach( ing => {
            if ( ing.uid !== undefined ) {
                originalMap[ ing.uid ] = {
                    amount: ing.amount || '',
                    unit: ing.unit || '',
                    name: ing.name || '',
                };
            }
        });

        // Create a map of current ingredients by uid
        const currentMap = {};
        currentIngredientsList.forEach( ing => {
            if ( ing.uid !== undefined ) {
                currentMap[ ing.uid ] = {
                    amount: ing.amount || '',
                    unit: ing.unit || '',
                    name: ing.name || '',
                };
            }
        });

        // Check if any ingredient amount or unit changed
        for ( const currentIng of currentIngredientsList ) {
            if ( currentIng.uid !== undefined && originalMap[ currentIng.uid ] ) {
                const original = originalMap[ currentIng.uid ];
                const currentAmount = currentIng.amount || '';
                const currentUnit = currentIng.unit || '';

                if ( currentAmount !== original.amount || currentUnit !== original.unit ) {
                    const originalText = [ original.amount, original.unit ].filter( v => v ).join( ' ' ) || __wprm( 'not set' );
                    const currentText = [ currentAmount, currentUnit ].filter( v => v ).join( ' ' ) || __wprm( 'not set' );
                    changes.ingredientsChanged.push({
                        name: original.name || __wprm( 'Unnamed ingredient' ),
                        from: originalText,
                        to: currentText,
                    });
                }
            }
        }

        // Check for new ingredients (uid not in original map)
        // Only count ingredients that have at least a name
        for ( const currentIng of currentIngredientsList ) {
            if ( currentIng.uid !== undefined && ! originalMap[ currentIng.uid ] && this.hasValidIngredient(currentIng) ) {
                const name = currentIng.name || __wprm( 'Unnamed ingredient' );
                const amount = [ currentIng.amount, currentIng.unit ].filter( v => v ).join( ' ' ) || __wprm( 'not set' );
                changes.ingredientsAdded.push({
                    name: name,
                    amount: amount,
                });
            }
        }

        // Check for removed ingredients (uid in original map but not in current)
        for ( const originalUid in originalMap ) {
            if ( ! currentMap[ originalUid ] ) {
                const original = originalMap[ originalUid ];
                const name = original.name || __wprm( 'Unnamed ingredient' );
                const amount = [ original.amount, original.unit ].filter( v => v ).join( ' ' ) || __wprm( 'not set' );
                changes.ingredientsRemoved.push({
                    name: name,
                    amount: amount,
                });
            }
        }

        // Return changes object if there are any changes, otherwise return null
        if ( changes.servingsChanged || 
             changes.ingredientsChanged.length > 0 || 
             changes.ingredientsAdded.length > 0 || 
             changes.ingredientsRemoved.length > 0 ) {
            return changes;
        }

        return null;
    }

    markAsOk() {
        // Update original state to current state, effectively dismissing the warning
        this.storeOriginalState();
        // Notify parent that warning state changed
        this.notifyWarningChange();
        // Force update to re-render without warning
        this.forceUpdate();
    }

    calculateNutrients() {
        if ( needsCalculation && wprm_admin.addons.pro ) {
            this.setState({
                calculating: true,
            }, () => {
                Api.nutrition.getCalculated(this.props.nutrition).then((data) => {
                    if ( data ) {
                        if ( Object.keys( data.calculated ).length > 0 ) {
                            this.props.onRecipeChange( {
                                nutrition: {
                                    ...this.props.nutrition,
                                    ...data.calculated,
                                }
                            } );
                        }
                    }
    
                    this.setState({
                        calculating: false,
                    });
                });
            });
        }
    }

    render() {
        const props = this.props;
        const serving_size = props.nutrition.hasOwnProperty('serving_size') && props.nutrition['serving_size'] ? props.nutrition['serving_size'] : '';
        const serving_unit = props.nutrition.hasOwnProperty('serving_unit') && props.nutrition['serving_unit'] ? props.nutrition['serving_unit'] : '';

        const changes = this.hasSignificantChanges();
        const showWarning = changes !== null;

        return (
            <Fragment>
                <p>
                    { __wprm( 'These should be the nutrition facts for 1 serving of your recipe.' ) }<br/>
                    {
                        props.servings.amount
                        ?
                        <Fragment>{ __wprm( 'Total servings for this recipe:' ) } { `${props.servings.amount} ${props.servings.unit}`}</Fragment>
                        :
                        <Fragment>{ __wprm( `You don't have the servings field set for your recipe under "General".` ) }</Fragment>
                    }
                </p>
                {
                    showWarning
                    &&
                    <div className="wprm-admin-modal-field-nutrition-warning">
                        <div className="wprm-admin-modal-field-nutrition-warning-header">
                            <strong>{ __wprm( 'Warning:' ) }</strong>
                        </div>
                        <p>{ __wprm( 'You have made changes that could affect the nutrition facts:' ) }</p>
                        <ul className="wprm-admin-modal-field-nutrition-warning-changes">
                            {
                                changes.servingsChanged
                                &&
                                <li>
                                    <strong>{ __wprm( 'Serving size' ) }:</strong> { __wprm( 'changed from' ) } "{ changes.servingsDetails.from }" { __wprm( 'to' ) } "{ changes.servingsDetails.to }"
                                </li>
                            }
                            {
                                changes.ingredientsChanged.map( (change, index) => (
                                    <li key={ `changed-${index}` }>
                                        <strong>{ change.name }:</strong> { __wprm( 'changed from' ) } "{ change.from }" { __wprm( 'to' ) } "{ change.to }"
                                    </li>
                                ))
                            }
                            {
                                changes.ingredientsAdded.map( (ingredient, index) => (
                                    <li key={ `added-${index}` }>
                                        <strong>{ __wprm( 'Added ingredient' ) }:</strong> { ingredient.name } { ingredient.amount && `(${ingredient.amount})` }
                                    </li>
                                ))
                            }
                            {
                                changes.ingredientsRemoved.map( (ingredient, index) => (
                                    <li key={ `removed-${index}` }>
                                        <strong>{ __wprm( 'Removed ingredient' ) }:</strong> { ingredient.name } { ingredient.amount && `(${ingredient.amount})` }
                                    </li>
                                ))
                            }
                        </ul>
                        <p>{ __wprm( 'Please review and update the nutrition facts if necessary.' ) }</p>
                        <Button
                            type="button"
                            onClick={ () => this.markAsOk() }
                        >{ __wprm( 'Mark as OK' ) }</Button>
                    </div>
                }
                <div className="wprm-admin-modal-field-nutrition-container">
                    {
                        wprm_admin.addons.premium
                        ?
                        <FieldContainer id="nutrition_serving_size" label={ __wprm( 'Serving Size' ) } help={ __wprm( 'The weight of 1 serving. Does not affect the calculation.' ) }>
                            <FieldText
                                type="number"
                                value={ serving_size }
                                onChange={ (serving_size) => {
                                    const nutrition = {
                                        ...props.nutrition,
                                        serving_size,
                                    };

                                    props.onRecipeChange( { nutrition } );
                                }}
                            />
                            <FieldText
                                name="serving-unit"
                                placeholder={ wprm_admin.settings.nutrition_default_serving_unit }
                                value={ serving_unit }
                                onChange={ (serving_unit) => {
                                    const nutrition = {
                                        ...props.nutrition,
                                        serving_unit,
                                    };

                                    props.onRecipeChange( { nutrition } );
                                }}
                            />
                        </FieldContainer>
                        :
                        null
                    }
                    {
                        Object.keys(wprm_admin_modal.nutrition).map((nutrient, index ) => {
                            const options = wprm_admin_modal.nutrition[nutrient];
                            const value = props.nutrition.hasOwnProperty(nutrient) ? props.nutrition[nutrient] : '';

                            if ( 'serving_size' === nutrient ) {
                                return null;
                            }

                            if ( 'calories' !== nutrient && ! wprm_admin.addons.premium ) {
                                return null;
                            }

                            return (
                                <FieldContainer id={ `nutrition_${nutrient}` } label={ options.label } key={ index }>
                                    {
                                        'calculated' === options.type
                                        && this.state.calculating
                                        ?
                                        <Loader />
                                        :
                                        <Fragment>
                                            <FieldText
                                                type="number"
                                                value={ value }
                                                onChange={ (value) => {
                                                    const nutrition = {
                                                        ...props.nutrition,
                                                        [nutrient]: value,
                                                    };

                                                    props.onRecipeChange( { nutrition } );
                                                }}
                                                disabled={ 'calculated' === options.type }
                                            /><span className="wprm-admin-modal-field-nutrition-unit">{ options.unit }</span>
                                        </Fragment>
                                    }
                                </FieldContainer>
                            )
                        })
                    }
                </div>
                {
                    wprm_admin.addons.premium
                    ?
                    null
                    :
                    <p>{ __wprm( 'More nutrients are available in' ) } <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Premium</a>.</p>
                }
                <Button
                    type="button"
                    isPrimary
                    required="pro"
                    onClick={(e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        props.openSecondaryModal('nutrition-calculation', {
                            nutrition: props.nutrition,
                            servings: props.servings.amount,
                            ingredients: props.ingredients,
                            name: props.recipe?.name,
                            onNutritionChange: (calculated) => {
                                let nutrition = {};

                                Object.keys(wprm_admin_modal.nutrition).map((nutrient, index ) => {
                                    if ( calculated.hasOwnProperty( nutrient ) ) {
                                        nutrition[ nutrient ] = calculated[ nutrient ];
                                    } else {
                                        nutrition[ nutrient ] = false;
                                    }
                                });

                                // Keep serving size and unit.
                                nutrition['serving_size'] = props.nutrition && props.nutrition.hasOwnProperty( 'serving_size' ) ? props.nutrition.serving_size : false;
                                nutrition['serving_unit'] = props.nutrition && props.nutrition.hasOwnProperty( 'serving_unit' ) ? props.nutrition.serving_unit : false;

                                // Overwrite recipe nutrition.
                                props.onRecipeChange({
                                    nutrition,
                                });

                                // Mark that nutrition was just recalculated - will update original state in componentDidUpdate
                                this.justRecalculated = true;
                                
                                // Notify parent that warning state may have changed (will be cleared after state update)
                                // Use setTimeout to ensure state has updated
                                setTimeout(() => {
                                    this.notifyWarningChange();
                                }, 0);
                            },
                        });
                    }}
                >{ __wprm( 'Calculate Nutrition Facts' ) }</Button>
            </Fragment>
        );
    }
}
