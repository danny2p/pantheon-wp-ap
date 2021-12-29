import React, { Component } from 'react';
import { Draggable } from 'react-beautiful-dnd';
import { isKeyHotkey } from 'is-hotkey';

const isTabHotkey = isKeyHotkey('tab');

import Icon from 'Shared/Icon';
import { __wprm } from 'Shared/Translations';

import FieldRichText from './FieldRichText';

const handle = (provided) => (
    <div
        className="wprm-admin-modal-field-equipment-handle"
        {...provided.dragHandleProps}
        tabIndex="-1"
    ><Icon type="drag" /></div>
);

export default class FieldEquipment extends Component {
    shouldComponentUpdate(nextProps) {
        return JSON.stringify(this.props) !== JSON.stringify(nextProps);
    }

    render() {
        const amount = this.props.amount ? this.props.amount : '';
        const notes = this.props.notes ? this.props.notes : '';

        return (
            <Draggable
                draggableId={ `equipment-${this.props.uid}` }
                index={ this.props.index }
            >
                {(provided, snapshot) => {
                    return (
                        <div
                            className="wprm-admin-modal-field-equipment"
                            ref={provided.innerRef}
                            {...provided.draggableProps}
                        >
                            { handle(provided) }
                            <div className="wprm-admin-modal-field-equipment-text-container">
                                <FieldRichText
                                    singleLine
                                    toolbar={ wprm_admin.addons.premium ? 'all' : 'no-link' }
                                    className="wprm-admin-modal-field-equipment-amount"
                                    value={ amount }
                                    placeholder="1"
                                    onChange={ (amount) => this.props.onChangeEquipment( { amount } ) }
                                />
                                <FieldRichText
                                    singleLine
                                    toolbar="equipment"
                                    value={ this.props.name }
                                    placeholder={ 'howto' === this.props.recipeType ? __wprm( 'Pair of scissors' ) : __wprm( 'Pressure cooker' ) }
                                    onChange={ (name) => this.props.onChangeEquipment( { name } ) }
                                />
                                <FieldRichText
                                    singleLine
                                    toolbar={ wprm_admin.addons.premium ? 'all' : 'no-link' }
                                    value={ notes }
                                    placeholder={ __wprm( 'optional' ) }
                                    onChange={ (notes) => this.props.onChangeEquipment( { notes } ) }
                                    onKeyDown={(event) => {
                                        if ( isTabHotkey(event) ) {
                                            this.props.onTab(event);
                                        }
                                    }}
                                />
                            </div>
                            <div className="wprm-admin-modal-field-equipment-after-container">
                                <div className="wprm-admin-modal-field-equipment-after-container-icons">
                                    <Icon
                                        type="trash"
                                        onClick={ this.props.onDelete }
                                    />
                                    <Icon
                                        type="plus"
                                        title={ __wprm( 'Insert After' ) }
                                        onClick={ this.props.onAdd }
                                    />
                                </div>
                            </div>
                        </div>
                    )
                }}
            </Draggable>
        );
    }
}