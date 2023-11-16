import React, { Component, Fragment } from 'react';
import { DragDropContext, Droppable } from 'react-beautiful-dnd';

import '../../../../css/admin/modal/recipe/fields/list-item.scss';

import { __wprm } from 'Shared/Translations';
import FieldListItem from '../../fields/FieldListItem';

export default class ListItems extends Component {
    constructor(props) {
        super(props);

        this.state = {
            posts: {},
        };

        this.container = React.createRef();
    }

    shouldComponentUpdate(nextProps, nextState) {
        return JSON.stringify( this.props.items ) !== JSON.stringify( nextProps.items ) || JSON.stringify( this.state.posts ) !== JSON.stringify( nextState.posts );
    }

    onDragEnd(result) {
        if ( result.destination ) {
            let newFields = JSON.parse( JSON.stringify( this.props.items ) );
            const sourceIndex = result.source.index;
            const destinationIndex = result.destination.index;

            const field = newFields.splice(sourceIndex, 1)[0];
            newFields.splice(destinationIndex, 0, field);

            this.props.onListChange({
                items: newFields,
            });
        }
    }

    addField( type = 'roundup', afterIndex = false ) {
        let newFields = JSON.parse( JSON.stringify( this.props.items ) );
        let newField = {
            type,
            data: {},
        };

        // Default data.
        if ( 'roundup' === type ) {
            newField.data = {
                type: 'internal',
                id: 0,
                link: '',
                nofollow: '',
                newtab: '',
                image: 0,
                image_url: '',
                credit: '',
                name: '',
                summary: '',
                button: '',
                template: '',
            };
        } else if ( 'text' === type ) {
            newField.data = {
                text: '',
            }
        }

        // Give unique UID.
        let maxUid = Math.max.apply( Math, newFields.map( function(field) { return field.uid; } ) );
        maxUid = maxUid < 0 ? -1 : maxUid;
        newField.uid = maxUid + 1;

        let lastAddedIndex;
        if ( false === afterIndex ) {
            newFields.push(newField);
            lastAddedIndex = newFields.length - 1;
        } else {
            newFields.splice(afterIndex + 1, 0, newField);
            lastAddedIndex = afterIndex + 1;
        }

        this.props.onListChange({
            items: newFields,
        }, () => {
            if ( 'roundup' === type ) {
                this.props.onEditItem( lastAddedIndex );
            }
        });
    }
  
    render() {
        return (
            <div
                className="wprm-admin-modal-field-items-container"
                ref={ this.container }
            >
                <DragDropContext
                    onDragEnd={this.onDragEnd.bind(this)}
                >
                    <Droppable
                        droppableId="wprm-items"
                    >
                        {(provided, snapshot) => (
                            <div
                                className={`${ snapshot.isDraggingOver ? ' wprm-admin-modal-field-items-container-draggingover' : ''}`}
                                ref={provided.innerRef}
                                {...provided.droppableProps}
                            >
                                {
                                    this.props.hasOwnProperty( 'items' ) && this.props.items && this.props.items.length > 0
                                    &&
                                    <Fragment>
                                        <div className="wprm-admin-modal-field-items-header-container">
                                            <div className="wprm-admin-modal-field-items-header">{ __wprm( '#' ) }</div>
                                            <div className="wprm-admin-modal-field-items-header">{ __wprm( 'Image' ) }</div>
                                            <div className="wprm-admin-modal-field-items-header">{ __wprm( 'Name' ) }</div>
                                        </div>
                                        {
                                            this.props.items.map((item, index) => {
                                                let validItem = false;
                                                let itemPost = false;

                                                if ( 'roundup' === item.type ) {
                                                    if ( ( 'internal' === item.data.type || 'post' === item.data.type ) && 0 < item.data.id ) {
                                                        validItem = true;
                                                        if ( this.state.posts.hasOwnProperty( item.data.id ) ) {
                                                            itemPost = this.state.posts[ item.data.id ];
                                                        }
                                                    }
                                                    if ( 'external' === item.data.type && item.data.link ) {
                                                        validItem = true;
                                                    }
                                                }

                                                if ( 'text' === item.type ) {
                                                    validItem = true;
                                                }

                                                if ( ! validItem ) {
                                                    return null;
                                                }

                                                return (
                                                    <FieldListItem
                                                        item={ item }
                                                        post={ itemPost }
                                                        onLoadPost={ (post) => {
                                                            let posts = JSON.parse( JSON.stringify( this.state.posts ) );
                                                            posts[ post.id ] = post;

                                                            this.setState({
                                                                posts,
                                                            });
                                                        } }
                                                        index={ index }
                                                        key={ `item-${item.uid}` }
                                                        onChange={ ( data ) => {
                                                            let newFields = JSON.parse( JSON.stringify( this.props.items ) );
                                                            newFields[ index ].data = {
                                                                ...newFields[ index ].data,
                                                                ...data,
                                                            }

                                                            this.props.onListChange({
                                                                items: newFields,
                                                            });
                                                        } }
                                                        onEdit={ () => { this.props.onEditItem( index ) } }
                                                        onAdd={ () => {
                                                            this.addField( 'roundup', index );
                                                        }}
                                                        onDelete={() => {
                                                            let newFields = JSON.parse( JSON.stringify( this.props.items ) );
                                                            newFields.splice(index, 1);

                                                            this.props.onListChange({
                                                                items: newFields,
                                                            });
                                                        }}
                                                    />
                                                )
                                            })
                                        }
                                    </Fragment>
                                }
                                {provided.placeholder}
                            </div>
                        )}
                    </Droppable>
                </DragDropContext>
                <div
                    className="wprm-admin-modal-field-items-actions"
                >
                    <button
                        className="button"
                        onClick={(e) => {
                            e.preventDefault();
                            this.addField( 'roundup' );
                        } }
                    >{ __wprm( 'Add Roundup Item' ) }</button>
                    <button
                        className="button"
                        onClick={(e) => {
                            e.preventDefault();
                            this.addField( 'text' );
                        } }
                    >{ __wprm( 'Add Text Field' ) }</button>
                </div>
            </div>
        );
    }
}
