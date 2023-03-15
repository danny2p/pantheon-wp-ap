import React, { Component, Fragment } from 'react';
import { Draggable } from 'react-beautiful-dnd';
import { isKeyHotkey } from 'is-hotkey';

const isTabHotkey = isKeyHotkey('tab');

import Icon from 'Shared/Icon';
import { __wprm } from 'Shared/Translations';

import FieldInstructionMedia from './FieldInstructionMedia';
import FieldInstructionIngredients from './FieldInstructionIngredients';

import FieldRichText from './FieldRichText';
import FieldText from './FieldText';
import FieldTextarea from './FieldTextarea';
import FieldVideoTime from './FieldVideoTime';
import Media from '../general/Media';

const handle = (provided) => (
    <div
        className="wprm-admin-modal-field-instruction-handle"
        {...provided.dragHandleProps}
        tabIndex="-1"
    ><Icon type="drag" /></div>
);

const group = (props, provided) => (
    <div
        className="wprm-admin-modal-field-instruction-group"
        ref={provided.innerRef}
        {...provided.draggableProps}
    >
        <div className="wprm-admin-modal-field-instruction-main-container">
            { handle(provided) }
            <div className="wprm-admin-modal-field-instruction-group-name-container">
                <FieldRichText
                    singleLine
                    toolbar="no-styling"
                    value={ props.name }
                    placeholder={ __wprm( 'Instruction Group Header' ) }
                    onChange={(value) => props.onChangeName(value)}
                    onKeyDown={(event) => {
                        if ( isTabHotkey(event) ) {
                            props.onTab(event);
                        }
                    }}
                />
            </div>
        </div>
        <div className="wprm-admin-modal-field-instruction-after-container">
            <div className="wprm-admin-modal-field-instruction-after-container-icons">
                <Icon
                    type="trash"
                    title={ __wprm( 'Delete' ) }
                    onClick={ props.onDelete }
                />
                <Icon
                    type="plus-text"
                    title={ __wprm( 'Insert Group After' ) }
                    onClick={ props.onAddGroup }
                />
                <Icon
                    type="plus"
                    title={ __wprm( 'Insert Instruction After' ) }
                    onClick={ props.onAdd }
                />
            </div>
        </div>
    </div>
);

const instruction = (props, provided) => {
    let video = {
        type: 'none',
        embed: '',
        id: '',
        thumb: '',
        start: '',
        end: '',
        name: '',
    };

    if ( props.video ) {
        video = {
            ...video,
            ...props.video,
        };

        // For backwards compatibility.
        if ( 'none' === video.type && ( video.start || video.end ) ) {
            video.type = 'part';
        }
    }

    return (
        <div
            className="wprm-admin-modal-field-instruction"
            ref={provided.innerRef}
            {...provided.draggableProps}
        >
            <div className="wprm-admin-modal-field-instruction-main-container">
                { handle(provided) }
                <div className="wprm-admin-modal-field-instruction-text-container">
                    <div className="wprm-admin-modal-field-instruction-text-name-container">
                        <FieldRichText
                            className="wprm-admin-modal-field-instruction-text"
                            ingredients={ props.ingredients }
                            instructions={ props.instructions }
                            allIngredients={ props.hasOwnProperty( 'allIngredients' ) ? props.allIngredients : null }
                            inlineIngredientsPortal={ props.hasOwnProperty( 'inlineIngredientsPortal' ) ? props.inlineIngredientsPortal : null }
                            value={ props.text }
                            placeholder={ __wprm( 'This is one step of the instructions.' ) }
                            onChange={(value) => props.onChangeText(value)}
                            onKeyDown={(event) => {
                                if ( isTabHotkey(event) ) {
                                    props.onTab(event);
                                }
                            }}
                            key={ props.hasOwnProperty( 'externalUpdate' ) ? props.externalUpdate : null }
                        />
                    </div>
                    {
                        props.allowVideo
                        && 'part' === video.type
                        && 'media' === props.editMode
                        &&
                        <div className="wprm-admin-modal-field-instruction-video-container">
                            <FieldVideoTime
                                value={ video.start }
                                onChange={ (start) => {
                                    props.onChangeVideo({
                                        ...video,
                                        start,
                                    });
                                }}
                            />
                            <FieldVideoTime
                                value={ video.end }
                                onChange={ (end) => {
                                    props.onChangeVideo({
                                        ...video,
                                        end,
                                    });
                                }}
                            />
                            {
                                video.start && video.end
                                ?
                                <FieldText
                                    placeholder={ __wprm( 'Name for this video part' ) }
                                    value={ video.name }
                                    onChange={ (name) => {
                                        props.onChangeVideo({
                                            ...video,
                                            name,
                                        });
                                    }}
                                />
                                :
                                <Icon
                                    type="movie"
                                    title={ __wprm( 'Add video start and end time (in seconds or minutes:seconds format) if this instruction step is part of the recipe video.' ) }
                                />
                            }
                        </div>
                    }
                </div>
            </div>
            <div className="wprm-admin-modal-field-instruction-after-container">
                <div className="wprm-admin-modal-field-instruction-after-container-icons">
                    <Icon
                        type="trash"
                        title={ __wprm( 'Delete' ) }
                        onClick={ props.onDelete }
                    />
                    <Icon
                        type="plus-text"
                        title={ __wprm( 'Insert Group After' ) }
                        onClick={ props.onAddGroup }
                    />
                    <Icon
                        type="plus"
                        title={ __wprm( 'Insert Instruction After' ) }
                        onClick={ props.onAdd }
                    />
                </div>
                {
                    'summary' === props.editMode
                    &&
                    <div className="wprm-admin-modal-field-instruction-after-container-summary">
                        <FieldRichText
                            singleLine
                            className="wprm-admin-modal-field-instruction-name"
                            toolbar={ 'none' }
                            value={ props.hasOwnProperty( 'name' ) ? props.name : '' }
                            placeholder={ __wprm( 'Step Summary' ) }
                            onChange={(value) => props.onChangeName(value)}
                        />
                    </div>
                }
                {
                    'media' === props.editMode
                    &&
                    <FieldInstructionMedia
                        { ...props }
                        video={ video }
                    />
                }
                {
                    'ingredients' === props.editMode
                    &&
                    <FieldInstructionIngredients
                        { ...props }
                    />
                }
            </div>
        </div>
    )
};

export default class FieldInstruction extends Component {
    shouldComponentUpdate(nextProps) {
        return JSON.stringify(this.props) !== JSON.stringify(nextProps);
    }

    render() {
        return (
            <Draggable
                draggableId={ `instruction-${this.props.uid}` }
                index={ this.props.index }
            >
                {(provided, snapshot) => {
                    if ( 'group' === this.props.type ) {
                        return group(this.props, provided);
                    } else {
                        return instruction(this.props, provided);
                    }
                }}
            </Draggable>
        );
    }
}