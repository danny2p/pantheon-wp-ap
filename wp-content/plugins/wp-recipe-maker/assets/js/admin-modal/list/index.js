import React, { Component, Fragment } from 'react';
import { scroller } from 'react-scroll';

import '../../../css/admin/modal/list.scss';

import Header from '../general/Header';
import Footer from '../general/Footer';

import FieldGroup from '../fields/FieldGroup';
import ListGeneral from './edit/ListGeneral';
import ListMetadata from './edit/ListMetadata';
import ListItems from './edit/ListItems';

import Roundup from '../roundup';

import Loader from 'Shared/Loader';
import Api from 'Shared/Api';
import { __wprm } from 'Shared/Translations';

export default class List extends Component {
    constructor(props) {
        super(props);

        let list = JSON.parse( JSON.stringify( wprm_admin_modal.list ) );
        let loadingList = false;

        if ( props.args.hasOwnProperty( 'list' ) ) {
            list = JSON.parse( JSON.stringify( props.args.list ) );
        } else if ( props.args.hasOwnProperty( 'listId' ) ) {
            loadingList = true;
            Api.list.get(props.args.listId).then((data) => {
                if ( data ) {
                    const list = JSON.parse( JSON.stringify( data.list ) );

                    if ( props.args.cloneList ) {
                        delete list.id;
                    }

                    this.setState({
                        list,
                        originalList: props.args.cloneList ? {} : JSON.parse( JSON.stringify( list ) ),
                        loadingList: false,
                    });
                } else {
                    // Loading list failed.
                    this.setState({
                        loadingList: false,
                    });
                }
            });
        }

        this.state = {
            list,
            originalList: props.args.cloneList || props.args.restoreRevision ? {} : JSON.parse( JSON.stringify( list ) ),
            savingChanges: false,
            saveResult: false,
            loadingList,
            forceRerender: 0,
            editingItem: false,
        };

        // Bind functions.
        this.onListChange = this.onListChange.bind(this);
        this.onEditItem = this.onEditItem.bind(this);
        this.saveList = this.saveList.bind(this);
        this.allowCloseModal = this.allowCloseModal.bind(this);
        this.changesMade = this.changesMade.bind(this);
    }

    onListChange( fields, callback = false ) {
        this.setState((prevState) => ({
            list: {
                ...prevState.list,
                ...fields,
            }
        }), () => {
            if ( false !== callback ) {
                callback();
            }
        });
    }

    onEditItem( index ) {
        if ( false === index || this.state.list.items.hasOwnProperty( index ) ) {
            let newState = {
                editingItem: index,
            };
            
            this.setState(newState, () => {
                if ( false === index ) {
                    this.scrollToGroup( 'items' );
                }
            });
        }
    }

    editItem( fields ) {
        if ( false === this.state.editingItem || this.state.list.items.hasOwnProperty( this.state.editingItem ) ) {
            let newItems = JSON.parse( JSON.stringify( this.state.list.items ) );

            let newRoundup = {
                type: fields.type,
                id: 'external' !== fields.type ? fields.post.id : 0,
                link: fields.link,
                nofollow: fields.nofollow ? '1' : '',
                newtab: fields.newtab ? '1' : '',
                image: parseInt( fields.image.id ),
                image_url: fields.image.url,
                credit: fields.credit,
                name: fields.name,
                button: fields.button,
                summary: fields.summary.replace(/\r?\n|\r/gm, '%0A'),
            };

            newItems[ this.state.editingItem ] = {
                ...newItems[ this.state.editingItem ],
                data: newRoundup,
            };

            this.setState(() => ({
                list: {
                    ...this.state.list,
                    items: newItems,
                },
            }));
        }
    }

    scrollToGroup( group = 'items' ) {
        scroller.scrollTo( `wprm-admin-modal-fields-group-${ group }`, {
            containerId: 'wprm-admin-modal-list-content',
            offset: -10,
        } );
    }

    saveList( closeAfter = false ) {
        if ( ! this.state.savingChanges ) {
            const savingTimeout = setTimeout(() => {
                this.setState({
                    saveResult: 'waiting',
                });
            }, 5000 );

            this.setState({
                savingChanges: true,
                saveResult: false,
            }, () => {    
                Api.list.save(this.state.list).then((data) => {
                    clearTimeout( savingTimeout );

                    if ( data && data.list ) {
                        const list = JSON.parse( JSON.stringify( data.list ) );
                        this.setState((prevState) => ({
                            list,
                            originalList: JSON.parse( JSON.stringify( list ) ),
                            savingChanges: false,
                            saveResult: 'ok',
                            forceRerender: prevState.forceRerender + 1,
                        }), () => {
                            if ( 'function' === typeof this.props.args.saveCallback ) {
                                this.props.args.saveCallback( list );
                            }
                            if ( closeAfter ) {
                                this.props.maybeCloseModal();
                            }
                            
                            // Show save OK message for 3 seconds.
                            setTimeout(() => {
                                if ( 'ok' === this.state.saveResult ) {
                                    this.setState({
                                        saveResult: false,
                                    });
                                }
                            }, 3000);
                        });
                    } else {
                        this.setState({
                            savingChanges: false,
                            saveResult: 'failed',
                        });
                    }
                });
            });
        }
    }

    allowCloseModal() {
        if ( false !== this.state.editingItem ) {
            this.onEditItem( false );
            return false;
        }

        return ! this.state.savingChanges && ( ! this.changesMade() || confirm( __wprm( 'Are you sure you want to close without saving changes?' ) ) );
    }

    changesMade() {
        if ( typeof window.lodash !== 'undefined' ) {
            return ! window.lodash.isEqual( this.state.list, this.state.originalList );
        } else {
            return JSON.stringify( this.state.list ) !== JSON.stringify( this.state.originalList );
        }
    }

    render() {
        if ( false !== this.state.editingItem ) {
            if ( this.state.list.items.hasOwnProperty( this.state.editingItem ) ) {
                const item = this.state.list.items[ this.state.editingItem ];

                return (
                    <Roundup
                        maybeCloseModal={ this.props.maybeCloseModal }
                        args={{
                            fields: {
                                roundup: item.data,
                            },
                            insertCallback: ( fields ) => {
                                this.editItem( fields );
                            }
                        }}
                    />
                );
            }
        }

        return (
            <Fragment>
                <Header
                    onCloseModal={ this.props.maybeCloseModal }
                >
                    {
                        this.state.loadingList
                        ?
                        __wprm( 'Loading List...' )
                        :
                        <Fragment>
                            {
                                this.state.list.id
                                ?
                                `${ __wprm( 'Editing List' ) } #${this.state.list.id}${this.state.list.name ? ` - ${this.state.list.name}` : ''}`
                                :
                                `${ __wprm( 'Creating new List' ) }${this.state.list.name ? ` - ${this.state.list.name}` : ''}`
                            }
                        </Fragment>
                    }
                </Header>
                <div className="wprm-admin-modal-content" id="wprm-admin-modal-list-content">
                    {
                        this.state.loadingList
                        ?
                        <Loader/>
                        :
                        <form className="wprm-admin-modal-list-fields">
                            <FieldGroup
                                header={ __wprm( 'Internal' ) }
                                id={ 'internal' }
                            >
                                <ListGeneral
                                    name={ this.state.list.name }
                                    note={ this.state.list.note }
                                    template={ this.state.list.template }
                                    onListChange={ this.onListChange }
                                />
                            </FieldGroup>
                            <FieldGroup
                                header={ __wprm( 'Metadata' ) }
                                id={ 'metadata' }
                            >
                                <ListMetadata
                                    metadata_output={ this.state.list.metadata_output }
                                    metadata_name={ this.state.list.metadata_name }
                                    metadata_description={ this.state.list.metadata_description }
                                    onListChange={ this.onListChange }
                                />
                            </FieldGroup>
                            <FieldGroup
                                header={ __wprm( 'Items' ) }
                                id={ 'items' }
                            >
                                <ListItems
                                    items={ this.state.list.items }
                                    onEditItem={ this.onEditItem }
                                    onListChange={ this.onListChange }
                                />
                            </FieldGroup>
                        </form>
                    }
                </div>
                <div id="wprm-admin-modal-toolbar-container"></div>
                <Footer
                    savingChanges={ this.savingChanges }
                >
                    {
                        'failed' === this.state.saveResult
                        &&
                        <span>{ __wprm( 'Something went wrong during saving.' ) }</span>
                    }
                    {
                        'ok' === this.state.saveResult
                        ?
                        <span>{ __wprm( 'Saved successfully' ) }</span>
                        :
                        null
                    }
                    <button
                        className="button button-primary"
                        onClick={ () => { this.saveList( false ) } }
                        disabled={ ! this.changesMade() }
                    >
                        { __wprm( 'Save' ) }
                    </button>
                    <button
                        className="button button-primary"
                        onClick={ () => {
                            if ( this.changesMade() ) {
                                this.saveList( true );
                            } else {
                                this.props.maybeCloseModal();
                            }
                        } }
                    >
                        { this.changesMade() ? __wprm( 'Save & Close' ) : __wprm( 'Close' ) }
                    </button>
                </Footer>
            </Fragment>
        )
    }
}