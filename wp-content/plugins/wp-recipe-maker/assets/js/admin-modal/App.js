import React, { Component } from 'react';
import Modal from 'react-modal';

// Only if modal is on page.
if ( document.getElementById( 'wprm-admin-modal' ) ) {
    Modal.setAppElement( '#wprm-admin-modal' );
}

import '../../css/admin/modal/app.scss';
import '../../css/admin/modal/general/fields.scss';

import ErrorBoundary from 'Shared/ErrorBoundary';
import { __wprm } from 'Shared/Translations';
const { hooks } = WPRecipeMakerAdmin['wp-recipe-maker/dist/shared'];

import BulkEdit from './bulk-edit';
import InputFields from './input-fields';
import List from './list';
import Menu from './menu';
import Recipe from './recipe';
import Roundup from './roundup';
import Select from './select';
import Taxonomy from './taxonomy';
import BulkAdd from './recipe/bulk-add';
import BulkAddCategories from './recipe/bulk-add-categories';
import TextImport from './recipe/text-import';
import SuggestTags from './recipe/suggest-tags';
import SplitIngredient from './recipe/split-ingredient';
import AddRecipeToPost from './add-recipe-to-post';

const contentBlocks = {
    'bulk-edit': BulkEdit,
    'bulk-add-ingredients': BulkAdd,
    'bulk-add-instructions': BulkAdd,
    'bulk-add-categories': BulkAddCategories,
    'input-fields': InputFields,
    list: List,
    menu: Menu,
    recipe: Recipe,
    roundup: Roundup,
    select: Select,
    taxonomy: Taxonomy,
    'text-import': TextImport,
    'suggest-tags': SuggestTags,
    'split-ingredient': SplitIngredient,
    'add-recipe-to-post': AddRecipeToPost,
};

export default class App extends Component {
    constructor() {
        super();
    
        this.state = {
            modalIsOpen: false,
            mode: '',
            args: {},
            secondaryModalIsOpen: false,
            secondaryMode: '',
            secondaryArgs: {},
        };

        this.content = React.createRef();
        this.secondaryContent = React.createRef();

        this.close = this.close.bind(this);
        this.closeIfAllowed = this.closeIfAllowed.bind(this);
        this.openSecondary = this.openSecondary.bind(this);
        this.closeSecondary = this.closeSecondary.bind(this);
        this.closeSecondaryIfAllowed = this.closeSecondaryIfAllowed.bind(this);
    }

    open( mode, args = {}, forceOpen = false ) {
        if ( forceOpen || ! this.state.modalIsOpen ) {
            this.setState({
                modalIsOpen: true,
                mode,
                args,
            }, () => {
                // Don't set onbeforeunload for simple modals that don't have unsaved changes
                const simpleModals = [ 'add-recipe-to-post', 'select' ];
                if ( ! simpleModals.includes( mode ) ) {
                    window.onbeforeunload = () => __wprm( 'Are you sure you want to leave this page?' );
                }
            });
        }
    }

    close(callback = false) { 
        // Store closeCallback before closing
        const closeCallback = this.state.args && this.state.args.closeCallback && 'function' === typeof this.state.args.closeCallback ? this.state.args.closeCallback : false;
        
        this.setState({
            modalIsOpen: false,
        }, () => {
            window.onbeforeunload = null;
            
            // Call the provided callback first
            if ( 'function' === typeof callback ) {
                callback();
            }
            
            // Then call closeCallback if provided (after modal is closed)
            if ( closeCallback ) {
                closeCallback();
            }
        });
    }

    closeIfAllowed(callback = false) {
        const checkFunction = this.content.current && this.content.current.hasOwnProperty( 'allowCloseModal' ) ? this.content.current.allowCloseModal : false;

        if ( ! checkFunction || checkFunction() ) {
            this.close(callback);
        }
    }

    openSecondary( mode, args = {} ) {
        this.setState({
            secondaryModalIsOpen: true,
            secondaryMode: mode,
            secondaryArgs: args,
        });
    }

    closeSecondary(callback = false) { 
        this.setState({
            secondaryModalIsOpen: false,
        }, () => {
            if ( 'function' === typeof callback ) {
                callback();
            }
        });
    }

    closeSecondaryIfAllowed(callback = false) {
        const checkFunction = this.secondaryContent.current && this.secondaryContent.current.hasOwnProperty( 'allowCloseModal' ) ? this.secondaryContent.current.allowCloseModal : false;

        if ( ! checkFunction || checkFunction() ) {
            this.closeSecondary(callback);
        }
    }

    addTextToEditor( text, editorId ) {
        if (typeof tinyMCE == 'undefined' || !tinyMCE.get(editorId) || tinyMCE.get(editorId).isHidden()) {
            var current = jQuery('textarea#' + editorId).val();
            jQuery('textarea#' + editorId).val(current + text);
        } else {
            tinyMCE.get(editorId).focus(true);
            tinyMCE.activeEditor.selection.collapse(false);
            tinyMCE.activeEditor.execCommand('mceInsertContent', false, text);
        }
    };

    refreshEditor( editorId ) {
        if ( typeof tinyMCE !== 'undefined' && tinyMCE.get(editorId) && !tinyMCE.get(editorId).isHidden() ) {
            tinyMCE.get(editorId).focus(true);
            tinyMCE.activeEditor.setContent(tinyMCE.activeEditor.getContent());
        }
    };

    render() {
        let allContentBlocks = hooks.applyFilters( 'modal', contentBlocks );
        let allRecipeContentBlocks = hooks.applyFilters( 'modalRecipe', {} );
        const Content = allContentBlocks.hasOwnProperty(this.state.mode) ? allContentBlocks[this.state.mode] : false;
        const SecondaryContent = allContentBlocks.hasOwnProperty(this.state.secondaryMode) ? allContentBlocks[this.state.secondaryMode] : 
                                 allRecipeContentBlocks.hasOwnProperty(this.state.secondaryMode) ? allRecipeContentBlocks[this.state.secondaryMode] : false;

        if ( ! Content ) {
            return null;
        }

        const primaryOverlayClass = this.state.secondaryModalIsOpen 
            ? "wprm-admin-modal-overlay wprm-admin-modal-overlay-dimmed"
            : "wprm-admin-modal-overlay";

        return (
            <>
                <Modal
                    isOpen={ this.state.modalIsOpen }
                    onRequestClose={ this.closeIfAllowed }
                    overlayClassName={ primaryOverlayClass }
                    className={`wprm-admin-modal wprm-admin-modal-${this.state.mode} wp-core-ui`}
                >
                    <ErrorBoundary module="Modal">
                        <Content
                            ref={ this.content }
                            mode={ this.state.mode }
                            args={ this.state.args }
                            maybeCloseModal={ this.closeIfAllowed }
                            openSecondaryModal={ this.openSecondary }
                        />
                    </ErrorBoundary>
                </Modal>
                
                { SecondaryContent && (
                    <Modal
                        isOpen={ this.state.secondaryModalIsOpen }
                        onRequestClose={ this.closeSecondaryIfAllowed }
                        overlayClassName="wprm-admin-modal-overlay-secondary"
                        className={`wprm-admin-modal wprm-admin-modal-secondary wprm-admin-modal-recipe wprm-admin-modal-${this.state.secondaryMode} wp-core-ui`}
                    >
                        <ErrorBoundary module="SecondaryModal">
                            <SecondaryContent
                                ref={ this.secondaryContent }
                                mode={ this.state.secondaryMode }
                                args={ this.state.secondaryArgs }
                                maybeCloseModal={ this.closeSecondaryIfAllowed }
                                { ...this.state.secondaryArgs }
                            />
                        </ErrorBoundary>
                    </Modal>
                )}
            </>
        );
    }
}
