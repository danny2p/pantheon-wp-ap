import React, { useRef } from 'react';

import '../../../../css/admin/modal/recipe/fields/notes.scss';

import { __wprm } from 'Shared/Translations';
import FieldContainer from '../../fields/FieldContainer';
import FieldTinymce from '../../fields/FieldTinymce';

const defaultStyle = 'left-border-straight';
const defaultIcon = 'lightbulb';
const defaultAccent = '#2b6cb0';
const defaultTextColor = '#000000';
const noIcon = '__none__';

const cleanUpShortcodeAttribute = (value) => {
    value = value.replace(/"/gm, '%22');
    value = value.replace(/\[/gm, '%5B');
    value = value.replace(/\]/gm, '%5D');
    value = value.replace(/\r?\n|\r/gm, '%0A');
    return value;
}

const getTipShortcode = (tipStyle) => {
    const style = tipStyle && tipStyle.tip_style ? tipStyle.tip_style : '';
    const icon = tipStyle && tipStyle.tip_icon ? tipStyle.tip_icon : '';
    const accent = tipStyle && tipStyle.tip_accent ? tipStyle.tip_accent : '';
    const textColor = tipStyle && tipStyle.tip_text_color ? tipStyle.tip_text_color : '';
    const text = tipStyle && tipStyle.tip_text && tipStyle.tip_text.trim() ? tipStyle.tip_text : __wprm( 'Tip text' );

    const atts = [];
    if ( style && defaultStyle !== style ) {
        atts.push( `style="${ cleanUpShortcodeAttribute( style ) }"` );
    }
    if ( icon ) {
        if ( noIcon === icon || defaultIcon !== icon ) {
            atts.push( `icon="${ cleanUpShortcodeAttribute( icon ) }"` );
        }
    }
    if ( accent && defaultAccent !== accent.toLowerCase() ) {
        atts.push( `accent="${ cleanUpShortcodeAttribute( accent ) }"` );
    }
    if ( textColor && defaultTextColor !== textColor.toLowerCase() ) {
        atts.push( `text_color="${ cleanUpShortcodeAttribute( textColor ) }"` );
    }

    let shortcode = '[wprm-tip';
    if ( atts.length ) {
        shortcode += ` ${ atts.join( ' ' ) }`;
    }
    shortcode += `]${ text }[/wprm-tip]`;

    return shortcode;
}
 
const RecipeNotes = (props) => {
    const editorRef = useRef( false );

    const onInsertTip = () => {
        if ( ! props.openSecondaryModal ) {
            return;
        }

        props.openSecondaryModal( 'instruction-tip-style', {
            title: __wprm( 'Insert Tip' ),
            save_button: __wprm( 'Insert' ),
            show_tip_text_input: true,
            tip_text: __wprm( 'Tip text' ),
            onSave: ( tipStyle ) => {
                if ( editorRef.current && editorRef.current.insertContent ) {
                    editorRef.current.insertContent( getTipShortcode( tipStyle ) );
                }
            },
        } );
    };

    return (
        <FieldContainer label={ __wprm( 'Recipe Notes' ) }>
            <FieldTinymce
                id="recipe-notes"
                value={ props.notes }
                onReady={ (editor) => {
                    editorRef.current = editor;
                }}
                onChange={ ( notes ) => {
                    props.onRecipeChange( { notes }, {
                        historyMode: 'debounced',
                        historyKey: 'notes:content',
                    } );
                }}
                onBlur={ ( notes ) => {
                    props.onRecipeChange( { notes }, {
                        historyMode: 'debounced',
                        historyBoundary: true,
                        historyKey: 'notes:content',
                    } );
                }}
            />
            {
                props.openSecondaryModal
                &&
                <div className="wprm-admin-modal-field-notes-actions">
                    <button
                        className="button"
                        onClick={ (e) => {
                            e.preventDefault();
                            onInsertTip();
                        } }
                    >
                        { __wprm( 'Insert Tip' ) }
                    </button>
                </div>
            }
        </FieldContainer>
    );
}
export default RecipeNotes;
