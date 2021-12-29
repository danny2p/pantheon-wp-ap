import React, { Component } from 'react';
import { DragDropContext, Droppable } from 'react-beautiful-dnd';

import '../../../../../css/admin/modal/recipe/fields/equipment.scss';

import { __wprm } from 'Shared/Translations';
import FieldEquipment from '../../../fields/FieldEquipment';

export default class EquipmentEdit extends Component {
    constructor(props) {
        super(props);

        this.container = React.createRef();
        this.lastAddedIndex = 0;
    }

    shouldComponentUpdate(nextProps) {
        return this.props.type !== nextProps.type
               || JSON.stringify( this.props.equipment ) !== JSON.stringify( nextProps.equipment );
    }

    componentDidUpdate( prevProps ) {
        if ( this.props.equipment.length > prevProps.equipment.length ) {
            const inputs = this.container.current.querySelectorAll('.wprm-admin-modal-field-equipment-amount');

            if ( inputs.length && inputs[ this.lastAddedIndex ] ) {
                inputs[ this.lastAddedIndex ].focus();
            }
        }
    }

    onDragEnd(result) {
        if ( result.destination ) {
            let newFields = JSON.parse( JSON.stringify( this.props.equipment ) );
            const sourceIndex = result.source.index;
            const destinationIndex = result.destination.index;

            const field = newFields.splice(sourceIndex, 1)[0];
            newFields.splice(destinationIndex, 0, field);

            this.props.onRecipeChange({
                equipment: newFields,
            });
        }
    }

    addField( afterIndex = false ) {
        let newFields = JSON.parse( JSON.stringify( this.props.equipment ) );
        let newField = {
            name: '',
        };

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
            equipment: newFields,
        });
    }
  
    render() {
        return (
            <div
                className="wprm-admin-modal-field-equipment-edit-container"
                ref={ this.container }
            >
                <DragDropContext
                    onDragEnd={this.onDragEnd.bind(this)}
                >
                    <Droppable
                        droppableId="wprm-equipment"
                    >
                        {(provided, snapshot) => (
                            <div
                                className={`${ snapshot.isDraggingOver ? ' wprm-admin-modal-field-equipment-container-draggingover' : ''}`}
                                ref={provided.innerRef}
                                {...provided.droppableProps}
                            >
                                <div className="wprm-admin-modal-field-equipment-header-container">
                                    <div className="wprm-admin-modal-field-equipment-header">{ __wprm( 'Amount' ) }</div>
                                    <div className="wprm-admin-modal-field-equipment-header">{ __wprm( 'Name' ) } <span className="wprm-admin-modal-field-equipment-header-required">({ __wprm( 'required' ) })</span></div>
                                    <div className="wprm-admin-modal-field-equipment-header">{ __wprm( 'Notes' ) }</div>
                                </div>
                                {
                                    this.props.equipment.map((field, index) => (
                                        <FieldEquipment
                                            { ...field }
                                            recipeType={ this.props.type }
                                            index={ index }
                                            key={ `equipment-${field.uid}` }
                                            onTab={(event) => {
                                                // Create new equipment if we're tabbing in the last one.
                                                if ( index === this.props.equipment.length - 1) {
                                                    event.preventDefault();
                                                    // Use timeout to fix focus problem (because of preventDefault?).
                                                    setTimeout(() => {
                                                        this.addField();
                                                    });
                                                }
                                            }}
                                            onAdd={ () => {
                                                this.addField(index);
                                            }}
                                            onChangeEquipment={ ( equipment ) => {
                                                let newFields = JSON.parse( JSON.stringify( this.props.equipment ) );

                                                newFields[index] = {
                                                    ...newFields[index],
                                                    ...equipment,
                                                }
                                                
                                                this.props.onRecipeChange({
                                                    equipment: newFields,
                                                });
                                            }}
                                            onDelete={() => {
                                                let newFields = JSON.parse( JSON.stringify( this.props.equipment ) );
                                                newFields.splice(index, 1);

                                                this.props.onRecipeChange({
                                                    equipment: newFields,
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
                <div
                    className="wprm-admin-modal-field-equipment-actions"
                >
                    <button
                        className="button"
                        onClick={(e) => {
                            e.preventDefault();
                            this.addField();
                        } }
                    >{ __wprm( 'Add Equipment' ) }</button>
                    <p>{ __wprm( 'Tip: use the TAB key to move from field to field and easily add equipment.' ) }</p>
                </div>
            </div>
        );
    }
}
