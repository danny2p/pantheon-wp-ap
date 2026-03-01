import React, { Component, Fragment, useEffect } from 'react';
import { DragDropContext, Droppable } from 'react-beautiful-dnd';
import he from 'he';

import '../../../../../css/admin/modal/recipe/fields/instructions.scss';

import { __wprm } from 'Shared/Translations';
import Icon from 'Shared/Icon';
import Helpers from 'Shared/Helpers';
import { parseQuantity, formatQuantity } from 'Shared/quantities';

import EditMode from '../../../general/EditMode';
import FieldContainer from '../../../fields/FieldContainer';
import FieldRadio from '../../../fields/FieldRadio';
import FieldInstruction from '../../../fields/FieldInstruction';

export default class RecipeInstructions extends Component {
    constructor(props) {
        super(props);

        // Stored edit mode.
        let editMode = 'media';
        let savedEditMode = localStorage.getItem( 'wprm-modal-edit-mode' );
        if ( savedEditMode ) {
            editMode = savedEditMode;
        }

        this.state = {
            editMode,
            inlineIngredientsPortalRendered: false,
        }

        this.container = React.createRef();
        this.lastAddedIndex = 0;

        // Mutable ref to store instructions without causing re-renders.
        this.instructionsRef = { current: props.instructions };
    }

    shouldComponentUpdate(nextProps, nextState) {
        return this.state.inlineIngredientsPortalRendered !== nextState.inlineIngredientsPortalRendered
            || this.state.editMode !== nextState.editMode
            || this.props.type !== nextProps.type
            || this.props.allowVideo !== nextProps.allowVideo
            || JSON.stringify( this.props.instructions ) !== JSON.stringify( nextProps.instructions )
            || JSON.stringify( this.props.ingredients ) !== JSON.stringify( nextProps.ingredients );
    }
    
    componentDidUpdate( prevProps ) {
        // Update ref on every update.
        this.instructionsRef.current = this.props.instructions;

        if ( this.props.instructions.length > prevProps.instructions.length ) {
            const inputs = this.container.current.querySelectorAll('.wprm-admin-modal-field-richtext:not(.wprm-admin-modal-field-instruction-name)');

            if ( inputs.length && inputs[ this.lastAddedIndex ] ) {
                inputs[ this.lastAddedIndex ].focus();
            }
        }
    }

    componentDidMount() {
        // Wait until div portal is actually rendered.
        this.setState({
            inlineIngredientsPortalRendered: true,
        });
    }

    onDragEnd(result) {
        if ( result.destination ) {
            const sourceIndex = result.source.index;
            const destinationIndex = result.destination.index;
            this.props.onRecipeChange((recipe) => {
                const instructions = recipe && recipe.instructions_flat ? recipe.instructions_flat : [];
                let newFields = JSON.parse( JSON.stringify( instructions ) );

                const field = newFields.splice(sourceIndex, 1)[0];
                newFields.splice(destinationIndex, 0, field);

                return {
                    instructions_flat: newFields,
                };
            }, {
                historyMode: 'immediate',
                historyBoundary: true,
                historyKey: 'instructions:reorder',
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
        } else if ( 'tip' === type ) {
            newField = {
                type: 'tip',
                name: '',
                text: '',
                tip_icon: '',
                tip_style: '',
                tip_accent: '',
                tip_text_color: '',
            };
        } else {
            newField = {
                type: 'instruction',
                name: '',
                text: '',
                image: 0,
                image_url: '',
                ingredients: [],
            }
        }

        this.props.onRecipeChange((recipe) => {
            const instructions = recipe && recipe.instructions_flat ? recipe.instructions_flat : [];
            let newFields = JSON.parse( JSON.stringify( instructions ) );

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
                instructions_flat: newFields,
            };
        }, {
            historyMode: 'immediate',
            historyBoundary: true,
            historyKey: `instructions:add:${ type }`,
        });
    }
  
    render() {
        // Update ref on every render too, to be safe.
        this.instructionsRef.current = this.props.instructions;

        let editModes = {
            media: { label: __wprm( 'Instruction Media' ) },
        };

        // Summary field, in some cases.
        if ( 'ignore' !== wprm_admin.settings.metadata_instruction_name && 'other' !== this.props.type ) {
            editModes.summary = {
                label: __wprm( 'Metadata' ),
                help: __wprm( 'For guided recipes, Google wants a short (usually 1 word) summary for each instruction step. This will be the "name" in the HowToStep metadata. This is not shown in the recipe template.' ),
            };
        }

        // Show ingredients field last.
        editModes.ingredients = { label: __wprm( 'Associated Ingredients' ) };
        editModes.inline = { label: __wprm( 'Inline Ingredients' ) };

        // Get all ingredients.
        const allIngredients = this.props.ingredients.filter( (ingredient) => 'ingredient' === ingredient.type && '' !== ingredient.name );

        // Get used ingredients in instructions.
        let usedIngredients = [];

        for ( let instruction of this.props.instructions ) {
            if ( instruction.hasOwnProperty( 'ingredients' ) ) {
                // Convert all to strings for consistent comparison
                const instructionIngredients = instruction.ingredients.map( ing => String( ing ) );
                usedIngredients = usedIngredients.concat( instructionIngredients );
            }
        }

        // Now get unused ingredients (for display purposes only).
        let unusedIngredients = [];

        for ( let ingredient of this.props.ingredients ) {
            if ( 'ingredient' === ingredient.type ) {
                const uidStr = String( ingredient.uid );
                const hasSplits = ingredient.splits && Array.isArray( ingredient.splits ) && ingredient.splits.length >= 2;
                
                // Check if ingredient is used
                const isIngredientUsed = usedIngredients.includes( uidStr );
                
                // Check which splits are used
                const usedSplitIds = [];
                if ( hasSplits ) {
                    usedIngredients.forEach( used => {
                        const usedStr = String( used );
                        if ( usedStr.includes( ':' ) ) {
                            const parts = usedStr.split( ':' );
                            if ( parts[0] === uidStr ) {
                                usedSplitIds.push( parseInt( parts[1] ) );
                            }
                        }
                    });
                }
                
                // Count valid splits
                const validSplits = hasSplits ? ingredient.splits.filter( split => split.percentage !== undefined && split.percentage !== null ) : [];
                const allSplitsUsed = hasSplits && validSplits.length > 0 && validSplits.every( split => usedSplitIds.includes( split.id ) );
                
                // Add full ingredient if not used AND not all splits are used
                if ( ! isIngredientUsed && ! allSplitsUsed ) {
                    const ingredientString = Helpers.getIngredientString( ingredient );

                    if ( ingredientString ) {
                        unusedIngredients.push( ingredientString );
                    }
                }
                
                // Add unused splits
                if ( hasSplits ) {
                    for ( let split of ingredient.splits ) {
                        if ( split.percentage !== undefined && split.percentage !== null && ! usedSplitIds.includes( split.id ) ) {
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
                            const splitString = splitAmount ? `  └ ${splitAmount} ${splitUnit} ${splitName}`.trim() : `  └ ${percentage}% ${splitName}`.trim();
                            unusedIngredients.push( splitString );
                        }
                    }
                }
            }
        }

        return (
            <Fragment>
                <EditMode
                    modes={ editModes }
                    mode={ this.state.editMode }
                    onModeChange={(mode) => {
                        localStorage.setItem( 'wprm-modal-edit-mode', mode );
                        this.setState({ editMode: mode });
                    }}
                />
                <div
                    className={ `wprm-admin-modal-field-instruction-container wprm-admin-modal-field-instruction-container-${this.state.editMode}` }
                    ref={ this.container }
                >
                    <div className="wprm-admin-modal-field-instructions">
                        <DragDropContext
                            onDragEnd={this.onDragEnd.bind(this)}
                        >
                            <Droppable
                                droppableId="wprm-instructions"
                            >
                                {(provided, snapshot) => (
                                    <div
                                        className={`${ snapshot.isDraggingOver ? ' wprm-admin-modal-field-instruction-container-draggingover' : ''}`}
                                        ref={provided.innerRef}
                                        {...provided.droppableProps}
                                    >
                                        {
                                            this.props.instructions.map((field, index) => (
                                                <FieldInstruction
                                                    { ...field }
                                                    index={ index }
                                                    key={ `instruction-${field.uid}` }
                                                    instructionsRef={ this.instructionsRef }
                                                    onTab={(event) => {
                                                        // Only if edit mode is not metadata summary or associated ingredients.
                                                        if ( this.state.editMode !== 'summary' && this.state.editMode !== 'ingredients' ) {
                                                            // Create new instruction if we're tabbing in the last one.
                                                            if ( index === this.props.instructions.length - 1) {
                                                                event.preventDefault();
                                                                // Use timeout to fix focus problem (because of preventDefault?).
                                                                setTimeout(() => {
                                                                    this.addField( 'instruction' );
                                                                });
                                                            }
                                                        }
                                                    }}
                                                    editMode={ this.state.editMode }
                                                    openSecondaryModal={ this.props.openSecondaryModal }
                                                    onChangeName={ ( name, changeOptions = {} ) => {
                                                        this.props.onRecipeChange((recipe) => {
                                                            const instructions = recipe && recipe.instructions_flat ? recipe.instructions_flat : [];
                                                            const findIndex = instructions.findIndex( ( i ) => field.uid === i.uid );
                                                            const instructionIndex = 0 <= findIndex ? findIndex : index;

                                                            if ( ! instructions[ instructionIndex ] ) {
                                                                return {};
                                                            }

                                                            let newFields = JSON.parse( JSON.stringify( instructions ) );
                                                            newFields[instructionIndex].name = name;

                                                            return {
                                                                instructions_flat: newFields,
                                                            };
                                                        }, {
                                                            historyMode: 'debounced',
                                                            historyBoundary: !! changeOptions.historyBoundary,
                                                            historyKey: `instructions:${ field.uid }:name`,
                                                        });
                                                    }}
                                                    onChangeText={ ( text, changeOptions = {} ) => {
                                                        this.props.onRecipeChange((recipe) => {
                                                            const instructions = recipe && recipe.instructions_flat ? recipe.instructions_flat : [];
                                                            const findIndex = instructions.findIndex( ( i ) => field.uid === i.uid );
                                                            const instructionIndex = 0 <= findIndex ? findIndex : index;

                                                            if ( ! instructions[ instructionIndex ] ) {
                                                                return {};
                                                            }

                                                            let newFields = JSON.parse( JSON.stringify( instructions ) );
                                                            newFields[instructionIndex].text = text;

                                                            return {
                                                                instructions_flat: newFields,
                                                            };
                                                        }, {
                                                            historyMode: 'debounced',
                                                            historyBoundary: !! changeOptions.historyBoundary,
                                                            historyKey: `instructions:${ field.uid }:text`,
                                                        });
                                                    }}
                                                    onChangeImage={ ( image, url ) => {
                                                        this.props.onRecipeChange((recipe) => {
                                                            const instructions = recipe && recipe.instructions_flat ? recipe.instructions_flat : [];
                                                            const findIndex = instructions.findIndex( ( i ) => field.uid === i.uid );
                                                            const instructionIndex = 0 <= findIndex ? findIndex : index;

                                                            if ( ! instructions[ instructionIndex ] ) {
                                                                return {};
                                                            }

                                                            let newFields = JSON.parse( JSON.stringify( instructions ) );

                                                            newFields[instructionIndex].image = image;
                                                            newFields[instructionIndex].image_url = url;

                                                            return {
                                                                instructions_flat: newFields,
                                                            };
                                                        }, {
                                                            historyMode: 'immediate',
                                                            historyBoundary: true,
                                                            historyKey: `instructions:${ field.uid }:image`,
                                                        });
                                                    }}
                                                    onDelete={() => {
                                                        this.props.onRecipeChange((recipe) => {
                                                            const instructions = recipe && recipe.instructions_flat ? recipe.instructions_flat : [];
                                                            const findIndex = instructions.findIndex( ( i ) => field.uid === i.uid );
                                                            const instructionIndex = 0 <= findIndex ? findIndex : index;

                                                            if ( ! instructions[ instructionIndex ] ) {
                                                                return {};
                                                            }

                                                            let newFields = JSON.parse( JSON.stringify( instructions ) );
                                                            newFields.splice(instructionIndex, 1);

                                                            return {
                                                                instructions_flat: newFields,
                                                            };
                                                        }, {
                                                            historyMode: 'immediate',
                                                            historyBoundary: true,
                                                            historyKey: `instructions:${ field.uid }:delete`,
                                                        });
                                                    }}
                                                    onAdd={() => {
                                                        this.addField('instruction', index);
                                                    }}
                                                    onAddGroup={() => {
                                                        this.addField('group', index);
                                                    }}
                                                    allowVideo={ this.props.allowVideo }
                                                    onChangeVideo={ ( video, changeOptions = {} ) => {
                                                        this.props.onRecipeChange((recipe) => {
                                                            const instructions = recipe && recipe.instructions_flat ? recipe.instructions_flat : [];
                                                            const findIndex = instructions.findIndex( ( i ) => field.uid === i.uid );
                                                            const instructionIndex = 0 <= findIndex ? findIndex : index;

                                                            if ( ! instructions[ instructionIndex ] ) {
                                                                return {};
                                                            }

                                                            let newFields = JSON.parse( JSON.stringify( instructions ) );
                                                            newFields[instructionIndex].video = video;

                                                            return {
                                                                instructions_flat: newFields,
                                                            };
                                                        }, {
                                                            historyMode: changeOptions.historyMode ? changeOptions.historyMode : 'debounced',
                                                            historyBoundary: !! changeOptions.historyBoundary,
                                                            historyKey: `instructions:${ field.uid }:video`,
                                                        });
                                                    }}
                                                    instructions={ this.props.instructions }
                                                    allIngredients={ allIngredients }
                                                    usedIngredients={ usedIngredients }
                                                    onChangeIngredients={ ( ingredients ) => {
                                                        this.props.onRecipeChange((recipe) => {
                                                            const instructions = recipe && recipe.instructions_flat ? recipe.instructions_flat : [];
                                                            const findIndex = instructions.findIndex( ( i ) => field.uid === i.uid );
                                                            const instructionIndex = 0 <= findIndex ? findIndex : index;

                                                            if ( ! instructions[ instructionIndex ] ) {
                                                                return {};
                                                            }

                                                            let newFields = JSON.parse( JSON.stringify( instructions ) );
                                                            newFields[instructionIndex].ingredients = ingredients;

                                                            return {
                                                                instructions_flat: newFields,
                                                            };
                                                        }, {
                                                            historyMode: 'immediate',
                                                            historyBoundary: true,
                                                            historyKey: `instructions:${ field.uid }:ingredients`,
                                                        });
                                                    }}
                                                    onChangeTipIcon={ ( tipIcon ) => {
                                                        this.props.onRecipeChange((recipe) => {
                                                            const instructions = recipe && recipe.instructions_flat ? recipe.instructions_flat : [];
                                                            const findIndex = instructions.findIndex( ( i ) => field.uid === i.uid );
                                                            const instructionIndex = 0 <= findIndex ? findIndex : index;

                                                            if ( ! instructions[ instructionIndex ] ) {
                                                                return {};
                                                            }

                                                            let newFields = JSON.parse( JSON.stringify( instructions ) );
                                                            newFields[instructionIndex].tip_icon = tipIcon;

                                                            return {
                                                                instructions_flat: newFields,
                                                            };
                                                        }, {
                                                            historyMode: 'immediate',
                                                            historyBoundary: true,
                                                            historyKey: `instructions:${ field.uid }:tip_icon`,
                                                        });
                                                    }}
                                                    onChangeTipAccent={ ( tipAccent, changeOptions = {} ) => {
                                                        this.props.onRecipeChange((recipe) => {
                                                            const instructions = recipe && recipe.instructions_flat ? recipe.instructions_flat : [];
                                                            const findIndex = instructions.findIndex( ( i ) => field.uid === i.uid );
                                                            const instructionIndex = 0 <= findIndex ? findIndex : index;

                                                            if ( ! instructions[ instructionIndex ] ) {
                                                                return {};
                                                            }

                                                            let newFields = JSON.parse( JSON.stringify( instructions ) );
                                                            newFields[instructionIndex].tip_accent = tipAccent;

                                                            return {
                                                                instructions_flat: newFields,
                                                            };
                                                        }, {
                                                            historyMode: 'debounced',
                                                            historyBoundary: !! changeOptions.historyBoundary,
                                                            historyKey: `instructions:${ field.uid }:tip_accent`,
                                                        });
                                                    }}
                                                    onChangeTipStyle={ ( tipIcon, tipAccent, tipStyle, tipTextColor ) => {
                                                        this.props.onRecipeChange((recipe) => {
                                                            const instructions = recipe && recipe.instructions_flat ? recipe.instructions_flat : [];
                                                            const findIndex = instructions.findIndex( ( i ) => field.uid === i.uid );
                                                            const instructionIndex = 0 <= findIndex ? findIndex : index;

                                                            if ( ! instructions[ instructionIndex ] ) {
                                                                return {};
                                                            }

                                                            let newFields = JSON.parse( JSON.stringify( instructions ) );
                                                            newFields[instructionIndex].tip_icon = tipIcon;
                                                            newFields[instructionIndex].tip_accent = tipAccent;
                                                            newFields[instructionIndex].tip_style = tipStyle;
                                                            newFields[instructionIndex].tip_text_color = tipTextColor || '';

                                                            return {
                                                                instructions_flat: newFields,
                                                            };
                                                        }, {
                                                            historyMode: 'immediate',
                                                            historyBoundary: true,
                                                            historyKey: `instructions:${ field.uid }:tip_style`,
                                                        });
                                                    }}
                                                    inlineIngredientsPortalRendered={ this.state.inlineIngredientsPortalRendered }
                                                />
                                            ))
                                        }
                                        {provided.placeholder}
                                    </div>
                                )}
                            </Droppable>
                        </DragDropContext>
                    </div>
                    <div
                        className="wprm-admin-modal-field-instruction-inline-ingredients-container"
                        style={ 'inline' === this.state.editMode ? {} : { display: 'none' } }
                    >
                        <div className="wprm-admin-modal-field-instruction-inline-ingredients-info">
                            {
                                0 === allIngredients.length
                                ?
                                __wprm( "This recipe doesn't have any ingredients." )
                                :
                                __wprm( 'Put your cursor in the instruction text and click on an ingredient to add it.' )
                            }
                        </div>
                        <div id="wprm-admin-modal-field-instruction-inline-ingredients-portal"></div>
                    </div>
                    {
                        'ingredients' === this.state.editMode
                        &&
                        <div className="wprm-admin-modal-field-instruction-unused-ingredients">
                            {
                                0 === allIngredients.length
                                ?
                                <div className="wprm-admin-modal-field-instruction-unused-ingredients-info">{ __wprm( "This recipe doesn't have any ingredients." ) }</div>
                                :
                                <Fragment>
                                    {
                                        0 === unusedIngredients.length
                                        ?
                                        <div className="wprm-admin-modal-field-instruction-unused-ingredients-info">{ __wprm( 'All ingredients are associated with a step!' ) }</div>
                                        :
                                        <Fragment>
                                            <div className="wprm-admin-modal-field-instruction-unused-ingredients-info">{ __wprm( 'Unused ingredients:' ) }</div>
                                            {
                                                unusedIngredients.map( ( ingredient, index ) => {
                                                    return <div className="wprm-admin-modal-field-instruction-unused-ingredients-ingredient" key={ index }>{ he.decode( ingredient ) }</div>
                                                })
                                            }
                                        </Fragment>
                                    }
                                </Fragment>
                            }
                        </div>
                    }
                    <div
                        className="wprm-admin-modal-field-instruction-actions"
                    >
                        <button
                            className="button"
                            onClick={(e) => {
                                e.preventDefault();
                                this.addField( 'instruction' );
                            } }
                        >{ __wprm( 'Add Instruction' ) }</button>
                        <button
                            className="button"
                            onClick={(e) => {
                                e.preventDefault();
                                this.addField( 'group' );
                            } }
                        >{ __wprm( 'Add Instruction Group' ) }</button>
                        <button
                            className="button"
                            onClick={(e) => {
                                e.preventDefault();
                                this.addField( 'tip' );
                            } }
                        >{ __wprm( 'Add Tip' ) }</button>
                        <button
                            className="button"
                            onClick={(e) => {
                                e.preventDefault();
                                this.props.openSecondaryModal('bulk-add-instructions', {
                                    field: 'instructions',
                                    onBulkAdd: (instructions_flat) => {
                                        this.props.onRecipeChange((recipe) => {
                                            const currentInstructions = recipe && recipe.instructions_flat ? JSON.parse( JSON.stringify( recipe.instructions_flat ) ) : [];
                                            const newInstructions = this.props.setUids( currentInstructions, instructions_flat );

                                            return {
                                                instructions_flat: [
                                                    ...currentInstructions,
                                                    ...newInstructions,
                                                ],
                                            };
                                        }, {
                                            historyMode: 'immediate',
                                            historyBoundary: true,
                                            historyKey: 'instructions:bulk_add',
                                        });
                                    }
                                });
                            } }
                        >{ __wprm( 'Bulk Add Instructions' ) }</button>
                        <p>{ __wprm( 'Tip: use the TAB key to move from field to field and easily add instructions.' ) }</p>
                    </div>
                </div>
            </Fragment>
        );
    }
}
