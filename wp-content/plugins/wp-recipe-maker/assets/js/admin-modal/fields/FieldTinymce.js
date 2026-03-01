import React, { Component } from 'react';

import Loader from 'Shared/Loader';

export default class FieldTinymce extends Component {
    constructor(props) {
        super(props);

        // Make sure each render gets a unique UID.
        const uid = parseInt( wprm_admin_modal.editor_uid );
        wprm_admin_modal.editor_uid = uid + 1;

        this.state = {
            editorHtml: false,
            editorId: `wprm-admin-modal-notes-editor-${uid}`,
        }

        this.initEditor = this.initEditor.bind(this);
        this.initTextarea = this.initTextarea.bind(this);
        this.initTinyMCE = this.initTinyMCE.bind(this);
        this.notifyReady = this.notifyReady.bind(this);
        this.insertContent = this.insertContent.bind(this);

        this.textareaEventsAttached = false;
    }

    componentDidMount() {
        const editor = document.getElementById( 'wprm-admin-modal-notes-placeholder' );
        if ( ! editor ) {
            // Placeholder doesn't exist, can't initialize editor.
            return;
        }
        let editorHtml = editor.innerHTML;
        editorHtml = editorHtml.replace( /wprm-admin-modal-notes-editor/g, this.state.editorId );

        this.setState({
            editorHtml,
        });
    }

    componentDidUpdate( prevProps, prevState ) {
        if ( this.state.editorHtml && ! prevState.editorHtml ) {
            this.initEditor();
        }
    }

    initEditor() {
        if ( typeof window.tinymce !== 'undefined' ) {
            this.initTinyMCE();
        } else {
            this.initTextarea();
        }
    }

    initTextarea() {
        const textarea = this.getTextarea();

        if ( typeof window.quicktags !== 'undefined' ) {
            try {
                window.quicktags( { id: this.state.editorId } );
            } catch(e) {
                // Needed for Divi compatibility.
            }
        }

        if ( textarea ) {
            textarea.value = this.props.value;

            if ( ! this.textareaEventsAttached ) {
                [ 'input', 'blur' ].forEach( ( event ) => {
                    textarea.addEventListener( event, () => {
                        this.props.onChange( textarea.value );

                        if ( 'blur' === event && 'function' === typeof this.props.onBlur ) {
                            this.props.onBlur( textarea.value );
                        }
                    } );
                });
                this.textareaEventsAttached = true;
            }
        }

        this.notifyReady();
    }

    initTinyMCE() {
        // Clean up first.
        const container = document.getElementById( `wp-${this.state.editorId}-editor-container` );
        if ( ! container ) {
            // Container doesn't exist, fall back to textarea mode.
            this.initTextarea();
            return;
        }
        container.outerHTML = `<textarea id="${this.state.editorId}"></textarea>`;

        const $wrap = tinymce.$( `#wp-${this.state.editorId}-wrap` );
        if ( ! $wrap || $wrap.length === 0 ) {
            // Wrap doesn't exist, fall back to textarea mode.
            this.initTextarea();
            return;
        }

        // Force to text mode and init.
        $wrap.removeClass( 'tmce-active' ).addClass( 'html-active' );
        this.initTextarea();

        // Force to visual mode and init.
        $wrap.removeClass( 'html-active' ).addClass( 'tmce-active' );

        let args = {};
        if ( typeof window.tinyMCEPreInit !== 'undefined' && tinyMCEPreInit.hasOwnProperty('mceInit') && tinyMCEPreInit.mceInit.hasOwnProperty('wprm-admin-modal-notes-editor') ) {
            args = tinyMCEPreInit.mceInit['wprm-admin-modal-notes-editor'];
        }
        if ( args.hasOwnProperty('body_class') ) {
            args.body_class = args.body_class.replace( /wprm-admin-modal-notes-editor/g, this.state.editorId );
        }
        args.selector = `#${this.state.editorId}`;

        window.tinymce.init( args );

        // Attach listener.
        const editor = window.tinymce.get(this.state.editorId);

        if ( editor ) {
            editor.on('change', () => {
                this.props.onChange( editor.getContent() );
            });

            editor.on('blur', () => {
                if ( 'function' === typeof this.props.onBlur ) {
                    this.props.onBlur( editor.getContent() );
                }
            });
        }

        this.notifyReady();
    }

    getTextarea() {
        return document.getElementById( this.state.editorId );
    }

    getEditor() {
        if ( typeof window.tinyMCE !== 'undefined' ) {
            return window.tinyMCE.get( this.state.editorId );
        }
        if ( typeof window.tinymce !== 'undefined' ) {
            return window.tinymce.get( this.state.editorId );
        }

        return false;
    }

    notifyReady() {
        if ( 'function' === typeof this.props.onReady ) {
            this.props.onReady({
                editorId: this.state.editorId,
                insertContent: this.insertContent,
            });
        }
    }

    insertContent( text ) {
        const editor = this.getEditor();

        if ( editor && ! editor.isHidden() ) {
            editor.focus( true );
            editor.selection.collapse( false );
            editor.execCommand( 'mceInsertContent', false, text );
            this.props.onChange( editor.getContent() );
            return;
        }

        const textarea = this.getTextarea();
        if ( ! textarea ) {
            return;
        }

        const value = textarea.value || '';

        if ( 'number' === typeof textarea.selectionStart && 'number' === typeof textarea.selectionEnd ) {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;

            textarea.value = `${ value.slice( 0, start ) }${ text }${ value.slice( end ) }`;

            const cursor = start + text.length;
            textarea.focus();
            textarea.setSelectionRange( cursor, cursor );
        } else {
            textarea.value = `${ value }${ text }`;
        }

        this.props.onChange( textarea.value );
    }
  
    componentWillUnmount() {
        if ( typeof window.tinyMCE !== 'undefined' ) {
            window.tinyMCE.remove(`#${this.state.editorId}`);
        }

        if ( 'function' === typeof this.props.onReady ) {
            this.props.onReady( false );
        }
    }
  
    render() {
        if ( ! this.state.editorHtml ) {
            return <Loader/>;
        }

        return (
            <div
                id="wprm-admin-modal-field-tinymce-container"
                dangerouslySetInnerHTML={ { __html: this.state.editorHtml } }
            />
        );
    }
}
