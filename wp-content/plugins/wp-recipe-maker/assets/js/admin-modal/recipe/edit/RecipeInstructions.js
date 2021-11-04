import React, { Component, Fragment } from 'react';
import { DragDropContext, Droppable } from 'react-beautiful-dnd';
import he from 'he';

import '../../../../css/admin/modal/recipe/fields/instructions.scss';

import { __wprm } from 'Shared/Translations';
import Icon from 'Shared/Icon';
import Helpers from 'Shared/Helpers';

import EditMode from '../../general/EditMode';
import FieldContainer from '../../fields/FieldContainer';
import FieldRadio from '../../fields/FieldRadio';
import FieldInstruction from '../../fields/FieldInstruction';

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
        }

        this.container = React.createRef();
        this.lastAddedIndex = 0;
    }

    shouldComponentUpdate(nextProps, nextState) {
        return this.state.editMode !== nextState.editMode || this.props.type !== nextProps.type || this.props.allowVideo !== nextProps.allowVideo || JSON.stringify( this.props.instructions ) !== JSON.stringify( nextProps.instructions ) || JSON.stringify( this.props.ingredients ) !== JSON.stringify( nextProps.ingredients );
    }

    componentDidUpdate( prevProps ) {
        if ( this.props.instructions.length > prevProps.instructions.length ) {
            const inputs = this.container.current.querySelectorAll('.wprm-admin-modal-field-richtext:not(.wprm-admin-modal-field-instruction-name)');

            if ( inputs.length && inputs[ this.lastAddedIndex ] ) {
                inputs[ this.lastAddedIndex ].focus();
            }
        }
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
                    className="wprm-admin-modal-field-instruction-container"
                    ref={ this.container }
                >
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
                                                    let newFields = JSON.parse( JSON.stringify( this.props.instructions ) );
                                                    newFields[index].name = name;
                                                    
                                                    this.props.onRecipeChange({
                                                        instructions_flat: newFields,
                                                    });
                                                }}
                                                onChangeText={ ( text ) => {
                                                    let newFields = JSON.parse( JSON.stringify( this.props.instructions ) );
                                                    newFields[index].text = text;

                                                    this.props.onRecipeChange({
                                                        instructions_flat: newFields,
                                                    });
                                                }}
                                                onChangeImage={ ( image, url ) => {
                                                    let newFields = JSON.parse( JSON.stringify( this.props.instructions ) );

                                                    newFields[index].image = image;
                                                    newFields[index].image_url = url;
                                                    
                                                    this.props.onRecipeChange({
                                                        instructions_flat: newFields,
                                                    });
                                                }}
                                                onDelete={() => {
                                                    let newFields = JSON.parse( JSON.stringify( this.props.instructions ) );
                                                    newFields.splice(index, 1);

                                                    this.props.onRecipeChange({
                                                        instructions_flat: newFields,
                                                    });
                                                }}
                                                onAdd={() => {
                                                    this.addField('instruction', index);
                                                }}
                                                allowVideo={ this.props.allowVideo }
                                                onChangeVideo={ ( video ) => {
                                                    let newFields = JSON.parse( JSON.stringify( this.props.instructions ) );
                                                    newFields[index].video = video;
                                                    
                                                    this.props.onRecipeChange({
                                                        instructions_flat: newFields,
                                                    });
                                                }}
                                                allIngredients={ allIngredients }
                                                onChangeIngredients={ ( ingredients ) => {
                                                    let newFields = JSON.parse( JSON.stringify( this.props.instructions ) );
                                                    newFields[index].ingredients = ingredients;
                                                    
                                                    this.props.onRecipeChange({
                                                        instructions_flat: newFields,
                                                    });
                                                }}
                                            />
                                        ))
                                    }
                                    {provided.placeholder}
                                </div>
                            )}
                        </Droppable>
                    </DragDropContext>
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
