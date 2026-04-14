import React, { Component, Fragment } from 'react';

import '../../../css/admin/modal/idea.scss';

import Api from 'Shared/Api';
import { __wprm } from 'Shared/Translations';
import { ideaStatusOptions, ideaTypeOptions } from 'Shared/Ideas';

import Header from '../general/Header';
import Footer from '../general/Footer';
import FieldContainer from '../fields/FieldContainer';
import FieldDropdown from '../fields/FieldDropdown';
import FieldGroup from '../fields/FieldGroup';
import FieldRichText from '../fields/FieldRichText';
import FieldTinymce from '../fields/FieldTinymce';
import FieldText from '../fields/FieldText';

export default class Idea extends Component {
    constructor(props) {
        super(props);

        let idea = JSON.parse( JSON.stringify( wprm_admin_modal.idea ) );
        let editing = false;

        if ( props.args.hasOwnProperty( 'idea' ) ) {
            editing = true;
            idea = JSON.parse( JSON.stringify( props.args.idea ) );
        }

        this.state = {
            editing,
            idea,
            originalIdea: JSON.parse( JSON.stringify( idea ) ),
            savingChanges: false,
            saveResult: false,
        };

        this.onIdeaChange = this.onIdeaChange.bind( this );
        this.saveIdea = this.saveIdea.bind( this );
        this.changesMade = this.changesMade.bind( this );
        this.allowCloseModal = this.allowCloseModal.bind( this );
    }

    onIdeaChange(fields) {
        this.setState( ( prevState ) => ( {
            idea: {
                ...prevState.idea,
                ...fields,
            },
        } ) );
    }

    changesMade() {
        if ( typeof window.lodash !== 'undefined' ) {
            return ! window.lodash.isEqual( this.state.idea, this.state.originalIdea );
        }

        return JSON.stringify( this.state.idea ) !== JSON.stringify( this.state.originalIdea );
    }

    allowCloseModal() {
        return ! this.state.savingChanges && ( ! this.changesMade() || confirm( __wprm( 'Are you sure you want to close without saving changes?' ) ) );
    }

    saveIdea(closeAfter = false) {
        if ( ! this.state.idea.name || ! this.state.idea.name.trim() ) {
            alert( __wprm( 'The idea title is required.' ) );
            return;
        }

        if ( this.state.savingChanges ) {
            return;
        }

        this.setState( {
            savingChanges: true,
            saveResult: false,
        }, () => {
            Api.idea.save( this.state.idea ).then( ( data ) => {
                if ( data && data.idea ) {
                    const idea = JSON.parse( JSON.stringify( data.idea ) );

                    this.setState( {
                        idea,
                        originalIdea: JSON.parse( JSON.stringify( idea ) ),
                        savingChanges: false,
                        saveResult: 'ok',
                    }, () => {
                        if ( 'function' === typeof this.props.args.saveCallback ) {
                            this.props.args.saveCallback( idea );
                        }

                        if ( closeAfter ) {
                            this.props.maybeCloseModal();
                        }

                        setTimeout( () => {
                            if ( 'ok' === this.state.saveResult ) {
                                this.setState( {
                                    saveResult: false,
                                } );
                            }
                        }, 3000 );
                    } );
                } else {
                    this.setState( {
                        savingChanges: false,
                        saveResult: 'failed',
                    } );
                }
            } );
        } );
    }

    render() {
        const idea = this.state.idea;

        return (
            <Fragment>
                <Header onCloseModal={ this.props.maybeCloseModal }>
                    {
                        this.state.editing
                        ? `${ __wprm( 'Editing Idea' ) } #${ idea.id }${ idea.name ? ` - ${ idea.name }` : '' }`
                        : `${ __wprm( 'Creating new Idea' ) }${ idea.name ? ` - ${ idea.name }` : '' }`
                    }
                </Header>
                <div className="wprm-admin-modal-content wprm-admin-modal-idea-content">
                    <form className="wprm-admin-modal-idea-fields">
                        <FieldGroup
                            header={ __wprm( 'Idea' ) }
                            id={ 'idea' }
                        >
                            <FieldContainer id="idea-type" label={ __wprm( 'Type' ) }>
                                <FieldDropdown
                                    options={ ideaTypeOptions }
                                    value={ idea.type }
                                    onChange={ ( type ) => this.onIdeaChange( { type } ) }
                                />
                            </FieldContainer>
                            <FieldContainer id="idea-status" label={ __wprm( 'Status' ) }>
                                <FieldDropdown
                                    options={ ideaStatusOptions }
                                    value={ idea.status }
                                    onChange={ ( status ) => this.onIdeaChange( { status } ) }
                                />
                            </FieldContainer>
                            <FieldContainer id="idea-name" label={ __wprm( 'Name' ) }>
                                <FieldText
                                    placeholder={ __wprm( 'Recipe idea title' ) }
                                    value={ idea.name }
                                    onChange={ ( name ) => this.onIdeaChange( { name } ) }
                                />
                            </FieldContainer>
                            <FieldContainer id="idea-summary" label={ __wprm( 'Summary' ) }>
                                <FieldRichText
                                    placeholder={ __wprm( 'Short description of this idea...' ) }
                                    value={ idea.summary }
                                    onChange={ ( summary ) => this.onIdeaChange( { summary } ) }
                                />
                            </FieldContainer>
                            <FieldContainer id="idea-notes" label={ __wprm( 'Notes' ) }>
                                <FieldTinymce
                                    value={ idea.notes }
                                    onChange={ ( notes ) => this.onIdeaChange( { notes } ) }
                                    onBlur={ ( notes ) => this.onIdeaChange( { notes } ) }
                                />
                            </FieldContainer>
                        </FieldGroup>
                    </form>
                </div>
                <div id="wprm-admin-modal-toolbar-container"></div>
                <Footer savingChanges={ this.state.savingChanges }>
                    {
                        'failed' === this.state.saveResult
                        &&
                        <span>{ __wprm( 'Something went wrong during saving.' ) }</span>
                    }
                    {
                        'ok' === this.state.saveResult
                        &&
                        <span>{ __wprm( 'Saved successfully' ) }</span>
                    }
                    <button
                        className="button button-primary button-compact"
                        onClick={ () => this.saveIdea( false ) }
                        disabled={ ! this.changesMade() }
                    >
                        { __wprm( 'Save' ) }
                    </button>
                    <button
                        className="button button-primary button-compact"
                        onClick={ () => {
                            if ( this.changesMade() ) {
                                this.saveIdea( true );
                            } else {
                                this.props.maybeCloseModal();
                            }
                        } }
                    >
                        { this.changesMade() ? __wprm( 'Save and Close' ) : __wprm( 'Close' ) }
                    </button>
                </Footer>
            </Fragment>
        );
    }
}
