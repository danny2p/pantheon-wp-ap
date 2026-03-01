import React, { Component, Fragment } from 'react';
import { DragDropContext, Droppable } from 'react-beautiful-dnd';

import { __wprm } from 'Shared/Translations';
import InlineIngredientsHelper from '../RecipeInstructions/InlineIngredientsHelper';
import FieldIngredient from '../../../fields/FieldIngredient';

export default class IngredientsEdit extends Component {
    constructor(props) {
        super(props);

        this.container = React.createRef();
        this.lastAddedIndex = 0;
    }

    shouldComponentUpdate(nextProps, nextState) {
        return this.props.type !== nextProps.type
               || JSON.stringify( this.props.ingredients ) !== JSON.stringify( nextProps.ingredients )
               || JSON.stringify( this.props.instructions ) !== JSON.stringify( nextProps.instructions );
    }

    componentDidUpdate( prevProps ) {
        if ( this.props.ingredients.length > prevProps.ingredients.length ) {
            const inputs = this.container.current.querySelectorAll('.wprm-admin-modal-field-ingredient-group-name, .wprm-admin-modal-field-ingredient-amount');

            if ( inputs.length && inputs[ this.lastAddedIndex ] ) {
                inputs[ this.lastAddedIndex ].focus();
            }
        }
    }

    onDragEnd(result) {
        if ( result.destination ) {
            const sourceIndex = result.source.index;
            const destinationIndex = result.destination.index;
            this.props.onRecipeChange((recipe) => {
                const ingredients = recipe && recipe.ingredients_flat ? recipe.ingredients_flat : [];
                let newFields = JSON.parse( JSON.stringify( ingredients ) );

                const field = newFields.splice(sourceIndex, 1)[0];
                newFields.splice(destinationIndex, 0, field);

                return {
                    ingredients_flat: newFields,
                };
            }, {
                historyMode: 'immediate',
                historyBoundary: true,
                historyKey: 'ingredients:reorder',
            });
        }
    }

    addField(type, afterIndex = false) {
        let newField;

        if ( 'group' === type ) {
            newField = {
                type: 'group',
                name: '',
            };
        } else {
            newField = {
                type: 'ingredient',
                amount: '',
                unit: '',
                name: '',
                notes: '',
            }
        }

        this.props.onRecipeChange((recipe) => {
            const ingredients = recipe && recipe.ingredients_flat ? recipe.ingredients_flat : [];
            let newFields = JSON.parse( JSON.stringify( ingredients ) );

            // Give unique UID.
            let maxUid = Math.max.apply( Math, newFields.map( function(field) { return field.uid; } ) );
            maxUid = maxUid < 0 ? -1 : maxUid;
            newField.uid = maxUid + 1;

            if ( false === afterIndex ) {
                newFields.push(newField);
                this.lastAddedIndex = newFields.length - 1;
            } else {
                newFields.splice(afterIndex + 1, 0, newField);
                this.lastAddedIndex = afterIndex + 1;
            }

            return {
                ingredients_flat: newFields,
            };
        }, {
            historyMode: 'immediate',
            historyBoundary: true,
            historyKey: `ingredients:add:${ type }`,
        });
    }
  
    render() {
        return (
            <div
                className="wprm-admin-modal-field-ingredient-edit-container"
                ref={ this.container }
            >
                <DragDropContext
                    onDragEnd={ this.onDragEnd.bind(this) }
                >
                    <Droppable
                        droppableId="wprm-ingredients"
                    >
                        {(provided, snapshot) => (
                            <div
                                className={`${ snapshot.isDraggingOver ? ' wprm-admin-modal-field-ingredient-container-draggingover' : ''}`}
                                ref={provided.innerRef}
                                {...provided.droppableProps}
                            >
                                <div className="wprm-admin-modal-field-ingredient-header-container">
                                    <div className="wprm-admin-modal-field-ingredient-header">{ __wprm( 'Amount' ) }</div>
                                    <div className="wprm-admin-modal-field-ingredient-header">{ __wprm( 'Unit' ) }</div>
                                    <div className="wprm-admin-modal-field-ingredient-header">{ __wprm( 'Name' ) } <span className="wprm-admin-modal-field-ingredient-header-required">({ __wprm( 'required' ) })</span></div>
                                    <div className="wprm-admin-modal-field-ingredient-header">{ __wprm( 'Notes' ) }</div>
                                </div>
                                {
                                    this.props.ingredients.map((field, index) => {
                                        return (
                                        <FieldIngredient
                                            { ...field }
                                            recipeType={ this.props.type }
                                            index={ index }
                                            key={ `ingredient-${field.uid}` }
                                            onTab={(event) => {
                                                // Create new ingredient if we're tabbing in the last one.
                                                if ( index === this.props.ingredients.length - 1) {
                                                    event.preventDefault();
                                                    // Use timeout to fix focus problem (because of preventDefault?).
                                                    setTimeout(() => {
                                                        this.addField( 'ingredient' );
                                                    });
                                                }
                                            }}
                                            onChangeName={ ( name, changeOptions = {} ) => {
                                                this.props.onRecipeChange((recipe) => {
                                                    const ingredients = recipe && recipe.ingredients_flat ? recipe.ingredients_flat : [];
                                                    const findIndex = ingredients.findIndex( ( i ) => field.uid === i.uid );
                                                    const ingredientIndex = 0 <= findIndex ? findIndex : index;

                                                    if ( ! ingredients[ ingredientIndex ] ) {
                                                        return {};
                                                    }

                                                    let newFields = JSON.parse( JSON.stringify( ingredients ) );
                                                    newFields[ingredientIndex].name = name;

                                                    return {
                                                        ingredients_flat: newFields,
                                                    };
                                                }, {
                                                    historyMode: 'debounced',
                                                    historyBoundary: !! changeOptions.historyBoundary,
                                                    historyKey: `ingredients:${ field.uid }:name`,
                                                });
                                            }}
                                            onChangeIngredient={ ( ingredient, changeOptions = {} ) => {
                                                this.props.onRecipeChange((recipe) => {
                                                    const ingredients = recipe && recipe.ingredients_flat ? recipe.ingredients_flat : [];
                                                    const instructions = recipe && recipe.instructions_flat ? recipe.instructions_flat : [];
                                                    const findIndex = ingredients.findIndex( ( i ) => field.uid === i.uid );
                                                    const ingredientIndex = 0 <= findIndex ? findIndex : index;

                                                    if ( ! ingredients[ ingredientIndex ] ) {
                                                        return {};
                                                    }

                                                    let newFields = JSON.parse( JSON.stringify( ingredients ) );

                                                    newFields[ingredientIndex] = {
                                                        ...newFields[ingredientIndex],
                                                        ...ingredient,
                                                    }

                                                    // Need to update text for inline ingredients.
                                                    let newInstructions = JSON.parse( JSON.stringify( instructions ) );

                                                    newInstructions.map( ( instruction, i ) => {
                                                        if ( instruction.hasOwnProperty( 'type' ) && 'instruction' === instruction.type && instruction.hasOwnProperty( 'text' ) ) {
                                                            const updatedText = InlineIngredientsHelper.updateInlineIngredientInText( newFields[ingredientIndex], instruction.text );
                                                            if ( instruction.text !== updatedText ) {
                                                                newInstructions[ i ].text = updatedText;
                                                                newInstructions[ i ].externalUpdate = Date.now();
                                                            }
                                                        }
                                                    } );

                                                    return {
                                                        ingredients_flat: newFields,
                                                        instructions_flat: newInstructions,
                                                    };
                                                }, {
                                                    historyMode: 'debounced',
                                                    historyBoundary: !! changeOptions.historyBoundary,
                                                    historyKey: `ingredients:${ field.uid }:fields`,
                                                });
                                            }}
                                            onDelete={() => {
                                                this.props.onRecipeChange((recipe) => {
                                                    const ingredients = recipe && recipe.ingredients_flat ? recipe.ingredients_flat : [];
                                                    const instructions = recipe && recipe.instructions_flat ? recipe.instructions_flat : [];
                                                    const findIndex = ingredients.findIndex( ( i ) => field.uid === i.uid );
                                                    const ingredientIndex = 0 <= findIndex ? findIndex : index;

                                                    if ( ! ingredients[ ingredientIndex ] ) {
                                                        return {};
                                                    }

                                                    let newFields = JSON.parse( JSON.stringify( ingredients ) );
                                                    let newInstructions = JSON.parse( JSON.stringify( instructions ) );

                                                    // Delete ingredient and retrieve.
                                                    const deletedIngredient = newFields.splice(ingredientIndex, 1);
                                                    
                                                    // Need to remove ingredient UID from associated instructions and maybe update inline ingredients.
                                                    if ( deletedIngredient[0] && deletedIngredient[0].hasOwnProperty( 'uid' ) ) {
                                                        const deletedUid = deletedIngredient[0].uid;

                                                        // Need to update inline ingredients.
                                                        newInstructions.map( ( instruction, i ) => {
                                                            // Associated ingredients.
                                                            if ( instruction.hasOwnProperty( 'ingredients' ) ) {
                                                                newInstructions[ i ].ingredients = instruction.ingredients.filter( ( ingredient ) => ingredient !== deletedUid );
                                                            }

                                                            // Inline ingredients.
                                                            if ( instruction.hasOwnProperty( 'type' ) && 'instruction' === instruction.type && instruction.hasOwnProperty( 'text' ) ) {
                                                                const updatedText = InlineIngredientsHelper.updateInlineIngredientInText( deletedIngredient[0], instruction.text, true );
                                                                if ( instruction.text !== updatedText ) {
                                                                    newInstructions[ i ].text = updatedText;
                                                                    newInstructions[ i ].externalUpdate = Date.now();
                                                                }
                                                            }
                                                        } );
                                                    }

                                                    return {
                                                        ingredients_flat: newFields,
                                                        instructions_flat: newInstructions,
                                                    };
                                                }, {
                                                    historyMode: 'immediate',
                                                    historyBoundary: true,
                                                    historyKey: `ingredients:${ field.uid }:delete`,
                                                });
                                            }}
                                            onAdd={() => {
                                                this.addField('ingredient', index);
                                            }}
                                            onAddGroup={() => {
                                                this.addField('group', index);
                                            }}
                                                onSplit={() => {
                                                    this.props.openSecondaryModal('split-ingredient', {
                                                        ingredient: field,
                                                        onSave: (splits) => {
                                                            this.props.onRecipeChange((recipe) => {
                                                                const ingredients = recipe && recipe.ingredients_flat ? recipe.ingredients_flat : [];
                                                                const findIndex = ingredients.findIndex( ( i ) => field.uid === i.uid );
                                                                const ingredientIndex = 0 <= findIndex ? findIndex : index;

                                                                if ( ! ingredients[ ingredientIndex ] ) {
                                                                    return {};
                                                                }

                                                                let newFields = JSON.parse( JSON.stringify( ingredients ) );
                                                                newFields[ingredientIndex].splits = splits;

                                                                return {
                                                                    ingredients_flat: newFields,
                                                                };
                                                            }, {
                                                                historyMode: 'immediate',
                                                                historyBoundary: true,
                                                                historyKey: `ingredients:${ field.uid }:split`,
                                                            });
                                                        }
                                                    });
                                                }}
                                        />
                                    )})
                                }
                                {provided.placeholder}
                            </div>
                        )}
                    </Droppable>
                </DragDropContext>
                <div
                    className="wprm-admin-modal-field-ingredient-actions"
                >
                    <button
                        className="button"
                        onClick={(e) => {
                            e.preventDefault();
                            this.addField( 'ingredient' );
                        } }
                    >{ 'howto' === this.props.type ? __wprm( 'Add Material' ) : __wprm( 'Add Ingredient' ) }</button>
                    <button
                        className="button"
                        onClick={(e) => {
                            e.preventDefault();
                            this.addField( 'group' );
                        } }
                    >{ 'howto' === this.props.type ? __wprm( 'Add Material Group' ) : __wprm( 'Add Ingredient Group' ) }</button>
                    <button
                        className="button"
                        onClick={(e) => {
                            e.preventDefault();
                            this.props.openSecondaryModal('bulk-add-ingredients', {
                                field: 'ingredients',
                                onBulkAdd: (ingredients_flat) => {
                                    this.props.onRecipeChange((recipe) => {
                                        const currentIngredients = recipe && recipe.ingredients_flat ? JSON.parse( JSON.stringify( recipe.ingredients_flat ) ) : [];
                                        const newIngredients = this.props.setUids( currentIngredients, ingredients_flat );

                                        return {
                                            ingredients_flat: [
                                                ...currentIngredients,
                                                ...newIngredients,
                                            ],
                                        };
                                    }, {
                                        historyMode: 'immediate',
                                        historyBoundary: true,
                                        historyKey: 'ingredients:bulk_add',
                                    });
                                }
                            });
                        } }
                    >{ 'howto' === this.props.type ? __wprm( 'Bulk Add Materials' ) : __wprm( 'Bulk Add Ingredients' ) }</button>
                    <p>{ __wprm( 'Tip: use the TAB key to move from field to field and easily add ingredients.' ) }</p>
                </div>
            </div>
        );
    }
}
