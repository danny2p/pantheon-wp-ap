import React, { Component, Fragment } from 'react';
import { Draggable } from 'react-beautiful-dnd';

import Api from 'Shared/Api';
import Icon from 'Shared/Icon';
import Loader from 'Shared/Loader';
import { __wprm } from 'Shared/Translations';

import FieldRichText from './FieldRichText';

const handle = (provided) => (
    <div
        className="wprm-admin-modal-field-item-handle"
        {...provided.dragHandleProps}
        tabIndex="-1"
    ><Icon type="drag" /></div>
);

export default class FieldListItem extends Component {
    constructor(props) {
        super(props);

        const { item, post } = this.props;
        let loading = false;

        if ( 'roundup' === item.type ) {
            if ( ( 'internal' === item.data.type || 'post' === item.data.type ) && ! post ) {
                loading = true;
                Api.utilities.getPostSummary( item.data.id ).then((data) => {
                    if ( data ) {
                        const post = JSON.parse( JSON.stringify( data.post ) );
    
                        this.setState({
                            loading: false,
                        }, () => {
                            this.props.onLoadPost( post );
                        });
                    } else {
                        // Loading post failed.
                        this.setState({
                            loading: false,
                        });
                    }
                });
            }
        }

        this.state = {
            loading,
        };
    }

    render() {
        const { item, post } = this.props;

        // Get image to display.
        let image_url = item.data.image_url;
        if ( post && post.image_url && ! image_url ) {
            image_url = post.image_url;
        }

        // Get name to use.
        let name = '?';
        if ( item.data.name ) {
            name = item.data.name;
        }
        if ( post && post.name ) {
            name = post.name;

            if ( item.data.name && item.data.name !== post.name ) {
                name += ` | ${item.data.name}`;
            }
        }

        return (
            <Draggable
                draggableId={ `item-${item.uid}` }
                index={ this.props.index }
            >
                {(provided, snapshot) => {
                    return (
                        <div
                            className={ `wprm-admin-modal-field-item wprm-admin-modal-field-item-${ item.type }` }
                            ref={provided.innerRef}
                            {...provided.draggableProps}
                        >
                            { handle(provided) }
                            <div className="wprm-admin-modal-field-item-container">
                                {
                                    'text' === item.type
                                    &&
                                    <FieldRichText
                                        className="wprm-admin-modal-field-item-text"
                                        toolbar="all"
                                        value={ item.data.text }
                                        placeholder=""
                                        onChange={ (value) => this.props.onChange( { text: value } ) }
                                        key={ this.props.hasOwnProperty( 'externalUpdate' ) ? this.props.externalUpdate : null }
                                    />
                                }
                                {
                                    'roundup' === item.type
                                    &&
                                    <Fragment>
                                        <div className="wprm-admin-modal-field-item-value wprm-admin-modal-field-item-number"></div>
                                        <div className="wprm-admin-modal-field-item-value wprm-admin-modal-field-item-image">
                                            {
                                                image_url
                                                ?
                                                <img src={ image_url } />
                                                :
                                                <div className="wprm-admin-modal-field-item-noimage"/>
                                            }
                                        </div>
                                        <div className="wprm-admin-modal-field-item-value wprm-admin-modal-field-item-name">
                                            {
                                                ( 'internal' === item.data.type || 'post' === item.data.type )
                                                ?
                                                <Fragment>
                                                    {
                                                        this.state.loading
                                                        ?
                                                        <Loader/>
                                                        :
                                                        `#${item.data.id} - ${name}`
                                                    }
                                                </Fragment>
                                                :
                                                <a href={ item.data.link } target="_blank">
                                                    { name }
                                                </a>
                                            }
                                        </div>
                                    </Fragment>
                                }
                            </div>
                            <div className="wprm-admin-modal-field-item-after-container">
                                {
                                    ! this.state.loading
                                    &&
                                    <div className="wprm-admin-modal-field-item-after-container-icons">
                                        <div className="wprm-admin-modal-field-item-after-container-icon">
                                            {
                                                'roundup' === item.type
                                                &&
                                                <Icon
                                                    type="pencil"
                                                    title={ __wprm( 'Edit List Item' ) }
                                                    onClick={ this.props.onEdit }
                                                />
                                            }
                                        </div>
                                        <div className="wprm-admin-modal-field-item-after-container-icon">
                                            <Icon
                                                type="trash"
                                                title={ __wprm( 'Remove List Item' ) }
                                                onClick={ this.props.onDelete }
                                            />
                                        </div>
                                        <div className="wprm-admin-modal-field-item-after-container-icon">
                                            <Icon
                                                type="plus"
                                                title={ __wprm( 'Insert After' ) }
                                                onClick={ this.props.onAdd }
                                            />
                                        </div>
                                    </div>
                                }
                            </div>
                        </div>
                    )
                }}
            </Draggable>
        );
    }
}