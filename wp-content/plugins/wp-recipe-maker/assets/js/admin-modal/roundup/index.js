import React, { Component, Fragment } from 'react';

import '../../../css/admin/modal/roundup.scss';

import Api from 'Shared/Api';
import Loader from 'Shared/Loader';
import { __wprm } from 'Shared/Translations';
import Header from '../general/Header';
import Footer from '../general/Footer';

import FieldImage from '../fields/FieldImage';
import FieldRadio from '../fields/FieldRadio';
import FieldText from '../fields/FieldText';
import FieldRichText from '../fields/FieldRichText';
import FieldTextarea from '../fields/FieldTextarea';
import SelectRecipe from '../select/SelectRecipe';

export default class Roundup extends Component {
    constructor(props) {
        super(props);

        let type = 'internal';
        let recipe = false;
        let link = '';
        let nofollow = false;
        let newtab = true;
        let name = '';
        let summary = '';
        let button = '';
        let image = {
            id: 0,
            url: '',
        }

        // Fallback to classic textarea if summary existed before and wasn't created in rich editor.
        let fallbackToTextarea = false;

        if ( props.args.fields && props.args.fields.roundup ) {
            const roundup = props.args.fields.roundup;

            if ( roundup.id ) {
                type = 'internal';
                recipe = {
                    id: roundup.id,
                    text: `${ __wprm( 'Recipe' ) } #${ roundup.id }`,
                };
                name = roundup.name;
                summary = roundup.summary.replaceAll( '%0A', '\n');
                button = roundup.button;
            } else if ( roundup.link ) {
                type = 'external';
                link = roundup.link;
                nofollow = roundup.nofollow ? true : false;
                newtab = roundup.newtab ? true : false;
                name = roundup.name;
                summary = roundup.summary.replaceAll( '%0A', '\n');
                button = roundup.button;
                image.id = roundup.image;
                image.url = roundup.image_url;

                // Existing roundup summary that doesn't start with "<p>" => created as regular textarea.
                if ( summary && '<p>' !== summary.substr( 0, 3 ) ) {
                    fallbackToTextarea = true;
                }
            }
        }
    
        this.state = {
            type,
            recipe,
            link,
            nofollow,
            newtab,
            name,
            summary,
            image,
            button,
            loading: false,
            saving: false,
            fallbackToTextarea,
        };

        this.loadDetailsFromURL = this.loadDetailsFromURL.bind(this);
        this.saveImage = this.saveImage.bind(this);
    }

    selectionsMade() {
        if ( 'external' === this.state.type ) {
            return '' !== this.state.link;
        } else {
            return false !== this.state.recipe;
        }
    }

    loadDetailsFromURL() {
        const url = this.state.link;
    
        // Check if valid URL (https://stackoverflow.com/questions/5717093/check-if-a-javascript-string-is-a-url).
        const pattern = new RegExp('^(https?:\\/\\/)?'+
            '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.?)+[a-z]{2,}|'+
            '((\\d{1,3}\\.){3}\\d{1,3}))'+
            '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+
            '(\\?[;&a-z\\d%_.~+=-]*)?'+
            '(\\#[-a-z\\d_]*)?$','i');
        
        if ( pattern.test(url) ) {
            // Valid URL, use OpenGraph API to load content.
            this.setState({
                loading: true,
            }, () => {
                fetch('https://api.microlink.io?url=' + encodeURIComponent( url ))
                    .then((response) => response.json())
                    .then((json) => {
                        let newState = {
                            loading: false,
                        }

                        if ( 'success' === json.status ) {
                            if ( json.data.title ) {
                                newState.name = json.data.title;
                            }
                            if ( json.data.description ) {
                                newState.summary = json.data.description;
                            }
                            if ( json.data.image && json.data.image.url ) {
                                newState.image = {
                                    id: -1,
                                    url: json.data.image.url
                                }
                            }
                        }

                        this.setState(newState);
                    }).catch( (error) => {
                        console.log( 'Fetch Error', error );

                        let newState = {
                            loading: false,
                        }
                        this.setState(newState);
                    });
            });
        }
    }

    saveImage() {
        const url = this.state.image.url;

        if ( url ) {
            this.setState({
                saving: true,
            }, () => {
                Api.utilities.saveImage(url).then((image) => {
                    let newState = {
                        saving: false,
                    }

                    if ( image && image.id ) {
                        newState.image = image;
                    }

                    this.setState(newState);
                });
            });
        }
    }

    render() {
        return (
            <Fragment>
                <Header
                    onCloseModal={ this.props.maybeCloseModal }
                >
                    { __wprm( 'Select Roundup Recipe' ) }
                </Header>
                <div className={ `wprm-admin-modal-roundup-container wprm-admin-modal-roundup-container-${ this.state.type }` }>
                    <div className="wprm-admin-modal-roundup-field-label">{ __wprm( 'Type' ) }</div>
                    <FieldRadio
                        id="type"
                        options={ [
                            { value: 'internal', label: __wprm( 'Use one of your own recipes' ) },
                            { value: 'external', label: __wprm( 'Use external recipe from a different website' ) },
                        ] }
                        value={ this.state.type }
                        onChange={(type) => {
                            this.setState({ type });
                        }}
                    />
                    {
                        'internal' === this.state.type
                        ?
                        <Fragment>
                            <div className="wprm-admin-modal-roundup-field-label">{ __wprm( 'Recipe' ) }</div>
                            <SelectRecipe
                                options={ [] }
                                value={ this.state.recipe }
                                onValueChange={(recipe) => {
                                    this.setState({ recipe });
                                }}
                            />
                        </Fragment>
                        :
                        <Fragment>
                            <div className="wprm-admin-modal-roundup-field-label">{ __wprm( 'Link' ) }</div>
                            <FieldText
                                name="roundup-link"
                                placeholder="https://demo.wprecipemaker.com/amazing-vegetable-pizza/"
                                type="url"
                                value={ this.state.link }
                                onChange={ (link) => {
                                    this.setState({ link });
                                }}
                                disabled={ this.state.loading }
                            />
                            {
                                this.state.loading
                                ?
                                <Loader/>
                                :
                                <Fragment>
                                    <div
                                        className="wprm-admin-modal-roundup-field-load-details-container"
                                        style={ ! this.state.link ? { visibility: 'hidden' } : {} }
                                    >
                                        <a
                                            href="#"
                                            onClick={(e) => {
                                                e.preventDefault();
                                                this.loadDetailsFromURL();
                                            }}
                                        >{ __wprm( 'Try to load details from URL' ) }</a>
                                    </div>
                                    <div className="wprm-admin-modal-roundup-field-nofollow-container">
                                        <input
                                            id="wprm-admin-modal-roundup-field-nofollow"
                                            type="checkbox"
                                            checked={ this.state.nofollow }
                                            onChange={(e) => {
                                                this.setState({ nofollow: e.target.checked });
                                            }}
                                        /> <label htmlFor="wprm-admin-modal-roundup-field-nofollow">{ __wprm( 'Add rel="nofollow" to link' ) }</label>
                                    </div>
                                    <div className="wprm-admin-modal-roundup-field-new-tab-container">
                                        <input
                                            id="wprm-admin-modal-roundup-field-new-tab"
                                            type="checkbox"
                                            checked={ this.state.newtab }
                                            onChange={(e) => {
                                                this.setState({ newtab: e.target.checked });
                                            }}
                                        /> <label htmlFor="wprm-admin-modal-roundup-field-new-tab">{ __wprm( 'Open link in new tab' ) }</label>
                                    </div>
                                    <div className="wprm-admin-modal-roundup-field-label">{ __wprm( 'Image' ) }</div>
                                    {
                                        this.state.saving
                                        ?
                                        <Loader/>
                                        :
                                        <Fragment>
                                            {
                                                -1 === this.state.image.id
                                                && '' !== this.state.image.url
                                                ?
                                                <div className="wprm-admin-modal-field-image">
                                                    <p>
                                                        { __wprm( 'External image. Recommended:' ) } <a
                                                            href="#"
                                                            onClick={ (e) => {
                                                                e.preventDefault();
                                                                this.saveImage();
                                                            } }
                                                        >{ __wprm( 'Save image locally' ) }</a>
                                                    </p>
                                                    <div className="wprm-admin-modal-field-image-preview">
                                                        <img src={ this.state.image.url } />
                                                        <a
                                                            href="#"
                                                            onClick={ (e) => {
                                                                e.preventDefault();
                                                                this.setState({
                                                                    image: {
                                                                        id: 0,
                                                                        url: '',
                                                                    }
                                                                });
                                                            } }
                                                        >{ __wprm( 'Remove Image' ) }</a>
                                                    </div>
                                                </div>
                                                :
                                                <FieldImage
                                                    id={ this.state.image.id }
                                                    url={ this.state.image.url }
                                                    onChange={ ( id, url ) => {
                                                        this.setState( {
                                                            image: {
                                                                id,
                                                                url,
                                                            }
                                                        });
                                                    }}
                                                />
                                            }
                                        </Fragment>
                                    }
                                </Fragment>
                            }
                        </Fragment>
                    }
                    {
                        'internal' === this.state.type
                        &&
                        <p className="wprm-admin-modal-roundup-override">{ __wprm( 'Optionally fill in these fields to use instead of the recipe values:' ) }</p>
                    }
                    {
                        /* Shared by internal and external */
                        ! this.state.loading
                        &&
                        <Fragment>
                            <div className="wprm-admin-modal-roundup-field-label">{ __wprm( 'Name' ) }</div>
                            <FieldText
                                name="recipe-name"
                                placeholder={ __wprm( 'Recipe Name' ) }
                                value={ this.state.name }
                                onChange={ (name) => {
                                    this.setState({ name });
                                }}
                            />
                            <div className="wprm-admin-modal-roundup-field-label">{ __wprm( 'Summary' ) }</div>
                            {
                                this.state.fallbackToTextarea
                                ?
                                <FieldTextarea
                                    placeholder={ __wprm( 'Short description of this recipe...' ) }
                                    value={ this.state.summary }
                                    onChange={ (summary) => {
                                        this.setState({ summary });
                                    }}
                                />
                                :
                                <FieldRichText
                                    toolbar="roundup"
                                    placeholder={ __wprm( 'Short description of this recipe...' ) }
                                    value={ this.state.summary }
                                    onChange={ (summary) => {
                                        // Remove empty lines before saving.
                                        summary = summary.replaceAll( '<p></p>', '' );
                                        summary = summary.replaceAll( '<p><br></p>', '' );
                                        summary = summary.replaceAll( '<p><br/></p>', '' );

                                        this.setState({ summary });
                                    }}
                                />
                            }
                            <div className="wprm-admin-modal-roundup-field-label">{ __wprm( 'Custom Button Text' ) }</div>
                            <FieldText
                                placeholder={ __wprm( 'Leave blank to use default from template' ) }
                                value={ this.state.button }
                                onChange={ (button) => {
                                    this.setState({ button });
                                }}
                            />
                        </Fragment>
                    }
                </div>
                <div id="wprm-admin-modal-toolbar-container"></div>
                <Footer
                    savingChanges={ this.state.loading || this.state.saving }
                >
                    <button
                        className="button button-primary"
                        onClick={ () => {
                            if ( 'function' === typeof this.props.args.insertCallback ) {
                                this.props.args.insertCallback( this.state );
                            }
                            this.props.maybeCloseModal();
                        } }
                        disabled={ ! this.selectionsMade() }
                    >
                        { __wprm( 'Use' ) }
                    </button>
                </Footer>
            </Fragment>
        );
    }
}