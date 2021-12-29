import React, { Component, Fragment } from 'react';

import '../../../css/admin/modal/menu.scss';

import Header from '../general/Header';
import Button from 'Shared/Button';
import { __wprm } from 'Shared/Translations';

const cleanUpShortcodeAttribute = (value) => {
    value = value.replace(/"/gm, '%22');
    value = value.replace(/\[/gm, '%5B');
    value = value.replace(/\]/gm, '%5D');
    value = value.replace(/\r?\n|\r/gm, '%0A');
    return value;
}

export default class Menu extends Component {
    render() {
        return (
            <Fragment>
                <Header
                    onCloseModal={ this.props.maybeCloseModal }
                >
                    WP Recipe Maker
                </Header>
                <div className="wprm-admin-modal-menu-container">
                    <h2>{ __wprm( 'Recipes' ) }</h2>
                    <div className="wprm-admin-modal-menu-buttons">
                        <Button
                            isPrimary
                            onClick={ () => {
                                let args = this.props.args;

                                // Default recipe name to post title.
                                if ( wprm_admin.settings.hasOwnProperty( 'recipe_name_from_post_title' ) && wprm_admin.settings.recipe_name_from_post_title ) {
                                    const titleInput = document.querySelector( '#title[name=post_title]' );

                                    if ( titleInput ) {
                                        let recipe = JSON.parse( JSON.stringify( wprm_admin_modal.recipe ) );
                                        recipe.name = titleInput.value;

                                        args.recipe = recipe;
                                    }
                                }

                                WPRM_Modal.open( 'recipe', args, true );
                            } }
                        >{ __wprm( 'Create new Recipe' ) }</Button>
                        <Button
                            onClick={ () => {
                                WPRM_Modal.open( 'select', {
                                    title: __wprm( 'Insert existing Recipe' ),
                                    button: __wprm( 'Insert' ),
                                    fields: {
                                        recipe: {},
                                    },
                                    insertCallback: ( fields ) => {
                                        if ( 'function' === typeof this.props.args.insertCallback ) {
                                            this.props.args.insertCallback( `[wprm-recipe id="${ fields.recipe.id }"]` );
                                        }
                                    },
                                }, true );
                            } }
                        >{ __wprm( 'Insert existing Recipe' ) }</Button>
                        <Button
                            required="premium"
                            onClick={ () => {
                                WPRM_Modal.open( 'select', {
                                    title: __wprm( 'Create new from existing Recipe' ),
                                    button: __wprm( 'Clone Recipe' ),
                                    fields: {
                                        recipe: {},
                                    },
                                    nextStepCallback: ( fields ) => {
                                        WPRM_Modal.open( 'recipe', {
                                            ...this.props.args,
                                            recipeId: fields.recipe.id,
                                            cloneRecipe: true,
                                        }, true );
                                    },
                                }, true );
                            } }
                        >{ __wprm( 'Create new from existing Recipe' ) }</Button>
                    </div>
                    <h2>{ __wprm( 'Recipe Snippets' ) }</h2>
                    <div className="wprm-admin-modal-menu-buttons">
                        <Button
                            help={ __wprm( 'Displays default snippet template as set in the WPRM Settings' ) }
                            onClick={ () => {
                                WPRM_Modal.open( 'select', {
                                    title: __wprm( 'Full Snippet Template' ),
                                    button: __wprm( 'Insert' ),
                                    fields: {
                                        recipe: { showFirst: true },
                                    },
                                    insertCallback: ( fields ) => {
                                        if ( 'function' === typeof this.props.args.insertCallback ) {
                                            let shortcode = '[wprm-recipe-snippet';
                                            shortcode += fields.recipe && fields.recipe.id ? ` id="${ fields.recipe.id }"]` : ']';

                                            this.props.args.insertCallback( shortcode );
                                        }
                                    },
                                }, true );
                            } }
                        >{ __wprm( 'Full Snippet Template' ) }</Button>
                        <Button
                            onClick={ () => {
                                WPRM_Modal.open( 'select', {
                                    title: __wprm( 'Jump to Recipe' ),
                                    button: __wprm( 'Insert' ),
                                    fields: {
                                        recipe: { showFirst: true },
                                    },
                                    insertCallback: ( fields ) => {
                                        if ( 'function' === typeof this.props.args.insertCallback ) {
                                            let shortcode = '[wprm-recipe-jump';
                                            shortcode += fields.recipe && fields.recipe.id ? ` id="${ fields.recipe.id }"]` : ']';

                                            this.props.args.insertCallback( shortcode );
                                        }
                                    },
                                }, true );
                            } }
                        >{ __wprm( 'Jump to Recipe' ) }</Button>
                        <Button
                            onClick={ () => {
                                WPRM_Modal.open( 'select', {
                                    title: __wprm( 'Jump to Video' ),
                                    button: __wprm( 'Insert' ),
                                    fields: {
                                        recipe: { showFirst: true },
                                    },
                                    insertCallback: ( fields ) => {
                                        if ( 'function' === typeof this.props.args.insertCallback ) {
                                            let shortcode = '[wprm-recipe-jump-video';
                                            shortcode += fields.recipe && fields.recipe.id ? ` id="${ fields.recipe.id }"]` : ']';

                                            this.props.args.insertCallback( shortcode );
                                        }
                                    },
                                }, true );
                            } }
                        >{ __wprm( 'Jump to Video' ) }</Button>
                        <Button
                            onClick={ () => {
                                WPRM_Modal.open( 'select', {
                                    title: __wprm( 'Print Recipe' ),
                                    button: __wprm( 'Insert' ),
                                    fields: {
                                        recipe: { showFirst: true },
                                    },
                                    insertCallback: ( fields ) => {
                                        if ( 'function' === typeof this.props.args.insertCallback ) {
                                            let shortcode = '[wprm-recipe-print';
                                            shortcode += fields.recipe && fields.recipe.id ? ` id="${ fields.recipe.id }"]` : ']';

                                            this.props.args.insertCallback( shortcode );
                                        }
                                    },
                                }, true );
                            } }
                        >{ __wprm( 'Print Recipe' ) }</Button>
                    </div>
                    <h2>{ __wprm( 'Recipe Parts' ) }</h2>
                    <div className="wprm-admin-modal-menu-buttons">
                        <Button
                            required="premium"
                            onClick={ () => {
                                WPRM_Modal.open( 'select', {
                                    title: __wprm( 'Nutrition Label' ),
                                    button: __wprm( 'Insert' ),
                                    fields: {
                                        recipe: { showFirst: true },
                                    },
                                    insertCallback: ( fields ) => {
                                        if ( 'function' === typeof this.props.args.insertCallback ) {
                                            let shortcode = '[wprm-nutrition-label';
                                            shortcode += fields.recipe && fields.recipe.id ? ` id="${ fields.recipe.id }"]` : ']';

                                            this.props.args.insertCallback( shortcode );
                                        }
                                    },
                                }, true );
                            } }
                        >{ __wprm( 'Nutrition Label' ) }</Button>
                        <Button
                            onClick={ () => {
                                WPRM_Modal.open( 'select', {
                                    title: __wprm( 'Recipe Video' ),
                                    button: __wprm( 'Insert' ),
                                    fields: {
                                        recipe: { showFirst: true },
                                    },
                                    insertCallback: ( fields ) => {
                                        if ( 'function' === typeof this.props.args.insertCallback ) {
                                            let shortcode = '[wprm-recipe-video';
                                            shortcode += fields.recipe && fields.recipe.id ? ` id="${ fields.recipe.id }"]` : ']';

                                            this.props.args.insertCallback( shortcode );
                                        }
                                    },
                                }, true );
                            } }
                        >{ __wprm( 'Recipe Video' ) }</Button>
                    </div>
                    <h2>{ __wprm( 'Other Features' ) }</h2>
                    <div className="wprm-admin-modal-menu-buttons">
                        <Button
                            help={ __wprm( 'Use for a list of existing recipes on your website' ) }
                            onClick={ () => {
                                WPRM_Modal.open( 'roundup', {
                                    insertCallback: ( fields ) => {
                                        if ( 'function' === typeof this.props.args.insertCallback ) {
                                            let shortcode = '[wprm-recipe-roundup-item';

                                            if ( ! fields.hasOwnProperty( 'type' ) || 'external' !== fields.type ) {
                                                // Default to internal link.
                                                shortcode += ` id="${ fields.recipe.id }"`;

                                                // Optional override fields.
                                                if ( fields.name ) { shortcode += ` name="${ cleanUpShortcodeAttribute( fields.name ) }"`; }
                                                if ( fields.summary ) { shortcode += ` summary="${ cleanUpShortcodeAttribute( fields.summary ) }"`; }
                                            } else {
                                                shortcode += ` link="${ cleanUpShortcodeAttribute( fields.link ) }"`;
                                                shortcode += fields.nofollow ? ' nofollow="1"' : '';
                                                shortcode += fields.newtab ? '' : ' newtab="0"';
                                                shortcode += ` name="${ cleanUpShortcodeAttribute( fields.name ) }"`;
                                                shortcode += ` summary="${ cleanUpShortcodeAttribute( fields.summary ) }"`;

                                                // Image.
                                                shortcode += fields.image.id ? ` image="${ fields.image.id }"` : '';

                                                if ( -1 === fields.image.id && fields.image.url ) {
                                                    shortcode += fields.image.url ? ` image_url="${ fields.image.url }"` : '';
                                                }
                                            }

                                            // Both internal and external.
                                            if ( fields.button ) { shortcode += ` button="${ cleanUpShortcodeAttribute( fields.button ) }"`; }

                                            // Close and insert shortcode.
                                            shortcode += ']';
                                            this.props.args.insertCallback( shortcode );
                                        }
                                    },
                                }, true );
                            } }
                        >{ __wprm( 'Recipe Roundup Item' ) }</Button>
                        <Button
                            required="elite"
                            onClick={ () => {
                                if ( 'function' === typeof this.props.args.insertCallback ) {
                                    this.props.args.insertCallback( '[wprm-recipe-submission]' );
                                }
                                this.props.maybeCloseModal();
                            } }
                        >{ __wprm( 'Recipe Submission Form' ) }</Button>
                        <Button
                            required="elite"
                            onClick={ () => {
                                if ( 'function' === typeof this.props.args.insertCallback ) {
                                    this.props.args.insertCallback( '[wprm-recipe-collections]' );
                                }
                                this.props.maybeCloseModal();
                            } }
                        >{ __wprm( 'Recipe Collections' ) }</Button>
                        <Button
                            required="elite"
                            help={ __wprm( 'Find the saved collection ID on the WP Recipe Maker > Manage > Saved Collections page' ) }
                            onClick={ () => {
                                if ( 'function' === typeof this.props.args.insertCallback ) {
                                    this.props.args.insertCallback( '[wprm-saved-collection id=""]' );
                                }
                                this.props.maybeCloseModal();
                            } }
                        >{ __wprm( 'Saved Recipe Collection' ) }</Button>
                    </div>
                </div>
            </Fragment>
        );
    }
}