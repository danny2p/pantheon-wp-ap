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
const { hooks } = WPRecipeMaker['wp-recipe-maker/dist/shared'];

import BulkEdit from './bulk-edit';
import InputFields from './input-fields';
import Menu from './menu';
import Recipe from './recipe';
import Roundup from './roundup';
import Select from './select';
import Taxonomy from './taxonomy';

const contentBlocks = {
    'bulk-edit': BulkEdit,
    'input-fields': InputFields,
    menu: Menu,
    recipe: Recipe,
    roundup: Roundup,
    select: Select,
    taxonomy: Taxonomy,
};

export default class App extends Component {
    constructor() {
        super();
    
        this.state = {
            modalIsOpen: false,
            mode: '',
            args: {},
        };

        this.content = React.createRef();

        this.close = this.close.bind(this);
        this.closeIfAllowed = this.closeIfAllowed.bind(this);
    }

    open( mode, args = {}, forceOpen = false ) {
        if ( forceOpen || ! this.state.modalIsOpen ) {
            this.setState({
                modalIsOpen: true,
                mode,
                args,
            }, () => {
                window.onbeforeunload = () => __wprm( 'Are you sure you want to leave this page?' );
            });
        }
    }

    close(callback = false) { 
        this.setState({
            modalIsOpen: false,
        }, () => {
            window.onbeforeunload = null;
            if ( 'function' === typeof callback ) {
                callback();
            }
        });
    }

    closeIfAllowed(callback = false) {
        const checkFunction = this.content.current && this.content.current.hasOwnProperty( 'allowCloseModal' ) ? this.content.current.allowCloseModal : false;

        if ( ! checkFunction || checkFunction() ) {
            this.close(callback);
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
        const Content = allContentBlocks.hasOwnProperty(this.state.mode) ? allContentBlocks[this.state.mode] : false;

        if ( ! Content ) {
            return null;
        }

        return (
            <Modal
                isOpen={ this.state.modalIsOpen }
                onRequestClose={ this.closeIfAllowed }
                overlayClassName="wprm-admin-modal-overlay"
                className={`wprm-admin-modal wprm-admin-modal-${this.state.mode}`}
            >
                <ErrorBoundary module="Modal">
                    <Content
                        ref={ this.content }
                        mode={ this.state.mode }
                        args={ this.state.args }
                        maybeCloseModal={ this.closeIfAllowed }
                    />
                </ErrorBoundary>
            </Modal>
        );
    }
}
