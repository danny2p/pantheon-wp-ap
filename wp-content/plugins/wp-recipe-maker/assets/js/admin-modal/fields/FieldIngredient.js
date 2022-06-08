import React, { Component } from 'react';
import { Draggable } from 'react-beautiful-dnd';
import { isKeyHotkey } from 'is-hotkey';

const isTabHotkey = isKeyHotkey('tab');

import Icon from 'Shared/Icon';
import { __wprm } from 'Shared/Translations';

import FieldRichText from './FieldRichText';
 
const handle = (provided) => (
    <div
        className="wprm-admin-modal-field-ingredient-handle"
        {...provided.dragHandleProps}
        tabIndex="-1"
    ><Icon type="drag" /></div>
);

const group = (props, provided) => (
    <div
        className="wprm-admin-modal-field-ingredient-group"
        ref={provided.innerRef}
        {...provided.draggableProps}
    >
        { handle(provided) }
        <div className="wprm-admin-modal-field-ingredient-group-name-container">
            <FieldRichText
                singleLine
                className="wprm-admin-modal-field-ingredient-group-name"
                toolbar="no-styling"
                value={ props.name }
                placeholder={ 'howto' === props.recipeType ? __wprm( 'Material Group Header' ) : __wprm( 'Ingredient Group Header' ) }
                onChange={(value) => props.onChangeName(value)}
                onKeyDown={(event) => {
                    if ( isTabHotkey(event) ) {
                        props.onTab(event);
                    }
                }}
            />
        </div>
        <div className="wprm-admin-modal-field-ingredient-after-container">
            <div className="wprm-admin-modal-field-ingredient-after-container-icons">
                <Icon
                    type="trash"
                    title={ __wprm( 'Remove' ) }
                    onClick={ props.onDelete }
                />
                <Icon
                    type="plus-text"
                    title={ __wprm( 'Insert Group After' ) }
                    onClick={ props.onAddGroup }
                />
                <Icon
                    type="plus"
                    title={ __wprm( 'Insert Ingredient After' ) }
                    onClick={ props.onAdd }
                />
            </div>
        </div>
    </div>
);

const ingredient = (props, provided) => {
    let amount = props.amount;
    let unit = props.unit;

    return (
        <div
            className="wprm-admin-modal-field-ingredient"
            ref={provided.innerRef}
            {...provided.draggableProps}
        >
            { handle(provided) }
            <div className="wprm-admin-modal-field-ingredient-text-container">
                <FieldRichText
                    singleLine
                    toolbar={ wprm_admin.addons.premium ? 'all' : 'no-link' }
                    className="wprm-admin-modal-field-ingredient-amount"
                    value={ amount }
                    placeholder="1"
                    onChange={(amount) => {
                        props.onChangeIngredient({amount});
                    }}
                />
                <FieldRichText
                    singleLine
                    toolbar={ wprm_admin.addons.premium ? 'all' : 'no-link' }
                    value={ unit }
                    placeholder={ 'howto' === props.recipeType ? __wprm( 'piece' ) : __wprm( 'tbsp' ) }
                    onChange={(unit) => {
                        props.onChangeIngredient({unit});
                    }}
                />
                <FieldRichText
                    singleLine
                    toolbar="ingredient"
                    value={ props.name }
                    placeholder={ 'howto' === props.recipeType ? __wprm( 'paper' ) : __wprm( 'olive oil' ) }
                    onChange={(name) => {
                        props.onChangeIngredient({
                            name,
                            globalLink: false, // Changing names will lead to a different global link.
                        })
                }}
                />
                <FieldRichText
                    singleLine
                    toolbar={ wprm_admin.addons.premium ? 'all' : 'no-link' }
                    value={ props.notes }
                    placeholder={ 'howto' === props.recipeType ? __wprm( 'any color' ) : __wprm( 'extra virgin' ) }
                    onChange={(notes) => props.onChangeIngredient({notes})}
                    onKeyDown={(event) => {
                        if ( isTabHotkey(event) ) {
                            props.onTab(event);
                        }
                    }}
                />
            </div>
            <div className="wprm-admin-modal-field-ingredient-after-container">
                <div className="wprm-admin-modal-field-ingredient-after-container-icons">
                    <Icon
                        type="trash"
                        title={ __wprm( 'Remove' ) }
                        onClick={ props.onDelete }
                    />
                    <Icon
                        type="plus-text"
                        title={ __wprm( 'Insert Group After' ) }
                        onClick={ props.onAddGroup }
                    />
                    <Icon
                        type="plus"
                        title={ __wprm( 'Insert Ingredient After' ) }
                        onClick={ props.onAdd }
                    />
                </div>
            </div>
        </div>
    );
};

export default class FieldIngredient extends Component {
    shouldComponentUpdate(nextProps) {
        return JSON.stringify(this.props) !== JSON.stringify(nextProps);
    }

    render() {
        return (
            <Draggable
                draggableId={ `ingredient-${this.props.uid}` }
                index={ this.props.index }
            >
                {(provided, snapshot) => {
                    if ( 'group' === this.props.type ) {
                        return group(this.props, provided);
                    } else {
                        return ingredient(this.props, provided);
                    }
                }}
            </Draggable>
        );
    }
}