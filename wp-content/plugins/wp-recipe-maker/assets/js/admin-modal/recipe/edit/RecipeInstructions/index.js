import React, { Component, Fragment, useEffect } from 'react';
import { DragDropContext, Droppable } from 'react-beautiful-dnd';
import he from 'he';

import '../../../../../css/admin/modal/recipe/fields/instructions.scss';

import { __wprm } from 'Shared/Translations';
import Icon from 'Shared/Icon';
import Helpers from 'Shared/Helpers';

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
            let newFields = JSON.parse( JSON.stringify( this.props.instructions ) );
            const sourceIndex = result.source.index;
            const destinationIndex = result.destination.index;

            const field = newFields.splice(sourceIndex, 1)[0];
            newFields.splice(destinationIndex, 0, field);

            this.props.onRecipeChange({
                instructions_flat: newFields,
            });
        }
    }

    addField(type, afterIndex = false) {
        let newFields = JSON.parse( JSON.stringify( this.props.instructions ) );
        let newField;

        if ( 'group' === type ) {
            newField = {
                type: 'group',
                name: '',
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

        this.props.onRecipeChange({
            instructions_flat: newFields,
        });
    }
  
    render() {
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
                usedIngredients = usedIngredients.concat( instruction.ingredients );
            }
        }

        // Now get unused ingredients.
        let unusedIngredients = [];

        for ( let ingredient of this.props.ingredients ) {
            if ( 'ingredient' === ingredient.type ) {
                if ( ! usedIngredients.includes( ingredient.uid ) ) {
                    const ingredientString = Helpers.getIngredientString( ingredient );

                    if ( ingredientString ) {
                        unusedIngredients.push( ingredientString );
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
                                                    onTab={(event) => {
                                                        // Create new instruction if we're tabbing in the last one.
                                                        if ( index === this.props.instructions.length - 1) {
                                                            event.preventDefault();
                                                            // Use timeout to fix focus problem (because of preventDefault?).
                                                            setTimeout(() => {
                                                                this.addField( 'instruction' );
                                                            });
                                                        }
                                                    }}
                                                    editMode={ this.state.editMode }
                                                    onChangeName={ ( name ) => {
                                                        const findIndex = this.props.instructions.findIndex( ( i ) => field.uid === i.uid );
                                                        const instructionIndex = 0 <= findIndex ? findIndex : index;

                                                        let newFields = JSON.parse( JSON.stringify( this.props.instructions ) );
                                                        newFields[instructionIndex].name = name;
                                                        
                                                        this.props.onRecipeChange({
                                                            instructions_flat: newFields,
                                                        });
                                                    }}
                                                    onChangeText={ ( text ) => {
                                                        const findIndex = this.props.instructions.findIndex( ( i ) => field.uid === i.uid );
                                                        const instructionIndex = 0 <= findIndex ? findIndex : index;

                                                        let newFields = JSON.parse( JSON.stringify( this.props.instructions ) );
                                                        newFields[instructionIndex].text = text;

                                                        this.props.onRecipeChange({
                                                            instructions_flat: newFields,
                                                        });
                                                    }}
                                                    onChangeImage={ ( image, url ) => {
                                                        const findIndex = this.props.instructions.findIndex( ( i ) => field.uid === i.uid );
                                                        const instructionIndex = 0 <= findIndex ? findIndex : index;

                                                        let newFields = JSON.parse( JSON.stringify( this.props.instructions ) );

                                                        newFields[instructionIndex].image = image;
                                                        newFields[instructionIndex].image_url = url;
                                                        
                                                        this.props.onRecipeChange({
                                                            instructions_flat: newFields,
                                                        });
                                                    }}
                                                    onDelete={() => {
                                                        const findIndex = this.props.instructions.findIndex( ( i ) => field.uid === i.uid );
                                                        const instructionIndex = 0 <= findIndex ? findIndex : index;

                                                        let newFields = JSON.parse( JSON.stringify( this.props.instructions ) );
                                                        newFields.splice(instructionIndex, 1);

                                                        this.props.onRecipeChange({
                                                            instructions_flat: newFields,
                                                        });
                                                    }}
                                                    onAdd={() => {
                                                        this.addField('instruction', index);
                                                    }}
                                                    onAddGroup={() => {
                                                        this.addField('group', index);
                                                    }}
                                                    allowVideo={ this.props.allowVideo }
                                                    onChangeVideo={ ( video ) => {
                                                        const findIndex = this.props.instructions.findIndex( ( i ) => field.uid === i.uid );
                                                        const instructionIndex = 0 <= findIndex ? findIndex : index;

                                                        let newFields = JSON.parse( JSON.stringify( this.props.instructions ) );
                                                        newFields[instructionIndex].video = video;
                                                        
                                                        this.props.onRecipeChange({
                                                            instructions_flat: newFields,
                                                        });
                                                    }}
                                                    instructions={ this.props.instructions }
                                                    allIngredients={ allIngredients }
                                                    usedIngredients={ usedIngredients }
                                                    onChangeIngredients={ ( ingredients ) => {
                                                        const findIndex = this.props.instructions.findIndex( ( i ) => field.uid === i.uid );
                                                        const instructionIndex = 0 <= findIndex ? findIndex : index;

                                                        let newFields = JSON.parse( JSON.stringify( this.props.instructions ) );
                                                        newFields[instructionIndex].ingredients = ingredients;
                                                        
                                                        this.props.onRecipeChange({
                                                            instructions_flat: newFields,
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
                        <p>{ __wprm( 'Tip: use the TAB key to move from field to field and easily add instructions.' ) }</p>
                    </div>
                </div>
            </Fragment>
        );
    }
}
