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
                this.loadPost( item.data.id );
            }
        }

        this.state = {
            loading,
        };
    }

    loadPost(postId) {
        if ( ! postId || 0 >= postId ) {
            this.setState({ loading: false });
            return;
        }

        Api.utilities.getPostSummary( postId ).then((data) => {
            if ( data && data.post ) {
                const post = JSON.parse( JSON.stringify( data.post ) );

                // Ensure post has required fields
                if ( post && post.id ) {
                    this.setState({
                        loading: false,
                    }, () => {
                        this.props.onLoadPost( post );
                    });
                } else {
                    // Invalid post data
                    this.setState({
                        loading: false,
                    });
                }
            } else {
                // Loading post failed or invalid response structure.
                this.setState({
                    loading: false,
                });
            }
        }).catch(() => {
            // Loading post failed.
            this.setState({
                loading: false,
            });
        });
    }

    componentDidUpdate(prevProps) {
        const { item, post } = this.props;
        const prevItem = prevProps.item;
        const prevPost = prevProps.post;

        // Check if we need to load the post (item ID changed or post is missing)
        if ( 'roundup' === item.type && ( 'internal' === item.data.type || 'post' === item.data.type ) ) {
            const itemId = item.data.id;
            const prevItemId = prevItem.data.id;

            // Only load if:
            // 1. Item ID changed and we don't have a post for the new ID, OR
            // 2. Item ID is the same but post was removed (shouldn't happen, but handle it)
            if ( itemId && itemId > 0 ) {
                if ( itemId !== prevItemId ) {
                    // Item ID changed - load new post if we don't have it
                    if ( ! post || post.id !== itemId ) {
                        this.setState({ loading: true });
                        this.loadPost( itemId );
                    }
                } else if ( ! post && prevPost ) {
                    // Post was removed (unlikely, but handle it)
                    // Don't reload, just clear loading state
                    if ( this.state.loading ) {
                        this.setState({ loading: false });
                    }
                } else if ( ! post && ! this.state.loading ) {
                    // Post is missing and we're not already loading - start loading
                    this.setState({ loading: true });
                    this.loadPost( itemId );
                }
            }
        }
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
        
        // First check if we have a post with a name (from API)
        if ( post && post.name && post.name.trim() ) {
            name = post.name.trim();
            
            // If there's also a custom name in item.data that's different, show both
            if ( item.data.name && item.data.name.trim() && item.data.name.trim() !== name ) {
                name += ` | ${item.data.name.trim()}`;
            }
        } 
        // Fall back to item.data.name if no post name available
        else if ( item.data.name && item.data.name.trim() ) {
            name = item.data.name.trim();
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
                                        toolbar="list"
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