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
                            <div className="wprm-admin-modal-field-equipment-main-container">
                                { handle(provided) }
                                <div className="wprm-admin-modal-field-equipment-name-container">
                                    <FieldRichText
                                        singleLine
                                        toolbar="equipment"
                                        value={ this.props.name }
                                        placeholder={ 'howto' === this.props.recipeType ? __wprm( 'Pair of scissors' ) : __wprm( 'Pressure cooker' ) }
                                        onChange={(value) => this.props.onChangeName(value)}
                                        onKeyDown={(event) => {
                                            if ( isTabHotkey(event) ) {
                                                this.props.onTab(event);
                                            }
                                        }}
                                    />
                                </div>
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