import React, { Component, Fragment } from 'react';

import '../../../css/admin/modal/taxonomy.scss';

import { __wprm } from 'Shared/Translations';
import Header from 'Modal/general/Header';
import Footer from 'Modal/general/Footer';

import FieldContainer from 'Modal/fields/FieldContainer';
import FieldText from 'Modal/fields/FieldText';
import FieldCheckbox from 'Modal/fields/FieldCheckbox';
import Api from 'Shared/Api';

export default class Menu extends Component {
    constructor(props) {
        super(props);

        let taxonomy = {
            key: '',
            singular_name: '',
            name: '',
            slug: '',
            archive: false,
        }

        let editing = false;
        if ( props.args.hasOwnProperty( 'taxonomy' ) ) {
            editing = true;
            taxonomy = JSON.parse( JSON.stringify( props.args.taxonomy ) );
        }

        this.state = {
            editing,
            taxonomy,
            originalTaxonomy: JSON.parse( JSON.stringify( taxonomy ) ),
            savingChanges: false,
        };

        this.changesMade = this.changesMade.bind(this);
        this.saveChanges = this.saveChanges.bind(this);
    }

    sanitizeSlug(text, isKey = false) {
        text = text.trim();
        text = text.toLowerCase();

        let from = "àáäâèéëêìíïîòóöôùúüûñçěščřžýúůďťň·/,:;";
        let to   = "aaaaeeeeiiiioooouuuuncescrzyuudtn_____";

        if ( isKey ) {
            from += '-';
            to += '_';
        }

        for ( let i=0, l=from.length ; i<l ; i++ )
        {
            text = text.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
        }

        text = text.replace('.', '-')
            .replace(/[^a-z0-9\s_\-]/g, '')
            .replace(/\s+/g, '_')
            .replace(/_+/g, '_');

        return text;
    }

    saveChanges() {
        if ( ! this.state.taxonomy.key.trim() || ! this.state.taxonomy.singular_name.trim() || ! this.state.taxonomy.name.trim() || ! this.state.taxonomy.slug.trim() ) {
            alert( __wprm( 'All fields are required.' ) );
        } else {
            this.setState({
                savingChanges: true,
            }, () => {
                Api.customTaxonomy.save( this.state.editing, this.state.taxonomy ).then((taxonomy) => {
                    if ( taxonomy ) {
                        this.setState({
                            originalTaxonomy: JSON.parse( JSON.stringify( this.state.taxonomy ) ),
                            savingChanges: false,
                        },() => {
                            if ( 'function' === typeof this.props.args.saveCallback ) {
                                this.props.args.saveCallback( this.state.taxonomy );
                            }
                            this.props.maybeCloseModal();
                        });
                    } else {
                        if ( ! this.state.editing && false === taxonomy ) {
                            alert( __wprm( 'Something went wrong. Make sure this key does not exist yet.' ) );
                        }
                        this.setState({
                            savingChanges: false,
                        });
                    }
                });
            })
        }
    }

    allowCloseModal() {
        return ! this.state.savingChanges && ( ! this.changesMade() || confirm( __wprm( 'Are you sure you want to close without saving changes?' ) ) );
    }

    changesMade() {
        return JSON.stringify( this.state.taxonomy ) !== JSON.stringify( this.state.originalTaxonomy );
    }

    render() {
        return (
            <Fragment>
                <Header
                    onCloseModal={ this.props.maybeCloseModal }
                >
                    {
                        this.state.editing
                        ?
                        
                        `${ __wprm( 'Editing Custom Taxonomy' ) }${this.state.taxonomy.key ? ` - wprm_${this.state.taxonomy.key}` : ''}`
                        :
                        `${ __wprm( 'Creating new Custom Taxonomy' ) }${this.state.taxonomy.key ? ` - wprm_${this.state.taxonomy.key}` : ''}`
                    }
                </Header>
                <div className="wprm-admin-modal-taxonomy-container">
                    <FieldContainer id="key" label={ __wprm( 'Key' ) }>
                        <FieldText
                            placeholder={ __wprm( 'course' ) }
                            value={ `wprm_${this.state.taxonomy.key}` }
                            onChange={ (key) => {
                                let sanitizedKey = key.substr(5);
                                sanitizedKey = this.sanitizeSlug( sanitizedKey, true );

                                this.setState({
                                    taxonomy: {
                                        ...this.state.taxonomy,
                                        key: sanitizedKey,
                                    }
                                });
                            }}
                            disabled={ this.state.editing }
                        />
                    </FieldContainer>
                    <FieldContainer id="singular_name" label={ __wprm( 'Singular Name' ) }>
                        <FieldText
                            placeholder={ __wprm( 'Course' ) }
                            value={ this.state.taxonomy.singular_name }
                            onChange={ (singular_name) => {
                                this.setState({
                                    taxonomy: {
                                        ...this.state.taxonomy,
                                        singular_name,
                                    }
                                });
                            }}
                        />
                    </FieldContainer>
                    <FieldContainer id="name" label={ __wprm( 'Plural Name' ) }>
                        <FieldText
                            placeholder={ __wprm( 'Courses' ) }
                            value={ this.state.taxonomy.name }
                            onChange={ (name) => {
                                this.setState({
                                    taxonomy: {
                                        ...this.state.taxonomy,
                                        name,
                                    }
                                });
                            }}
                        />
                    </FieldContainer>
                    <FieldContainer id="archive" label={ __wprm( 'Has Archive Pages' ) }>
                        <FieldCheckbox
                            value={ this.state.taxonomy.archive }
                            onChange={ (archive) => {
                                this.setState({
                                    taxonomy: {
                                        ...this.state.taxonomy,
                                        archive,
                                    }
                                });
                            }}
                        />
                    </FieldContainer>
                    <FieldContainer id="slug" label={ __wprm( 'Slug' ) }>
                        <FieldText
                            placeholder={ __wprm( 'course' ) }
                            value={ this.state.taxonomy.slug }
                            onChange={ (slug) => {
                                const sanitizedSlug = this.sanitizeSlug( slug );

                                this.setState({
                                    taxonomy: {
                                        ...this.state.taxonomy,
                                        slug: sanitizedSlug,
                                    }
                                });
                            }}
                        />
                    </FieldContainer>
                </div>
                <Footer
                    savingChanges={ this.state.savingChanges }
                >
                    <button
                        className="button button-primary"
                        onClick={ this.saveChanges }
                        disabled={ ! this.changesMade() }
                    >
                        { __wprm( 'Save' ) }
                    </button>
                </Footer>
            </Fragment>
        );
    }
}