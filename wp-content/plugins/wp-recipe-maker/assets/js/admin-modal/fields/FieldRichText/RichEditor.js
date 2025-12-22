import React, { useCallback, useMemo, useState, useRef, useEffect } from 'react';
import { Editor, Path, Range, Transforms, createEditor, Node } from 'slate';
import { Slate, Editable, withReact } from 'slate-react';
import { withHistory } from 'slate-history';
import isHotkey from 'is-hotkey'

import FieldText from '../FieldText';
import FieldTextarea from '../FieldTextarea';
import { isProblemBrowser, isFirefox } from 'Shared/Browser';
import Toolbar from './Toolbar';
import InlineIngredients from '../../recipe/edit/RecipeInstructions/InlineIngredients';
import { deserialize, serialize } from './html';
import { Element, Leaf } from './nodes';

import '../../../../css/admin/modal/general/rich-text.scss';

const HOTKEYS = {
    'mod+b': 'bold',
    'mod+i': 'italic',
    'mod+u': 'underline',
};

const INLINE_BLOCKS = [ 'link', 'affiliate-link', 'code', 'temperature', 'ingredient' ];

const RichEditor = React.memo((props) => {
    if ( isProblemBrowser() ) {
        if ( props.singleLine ) {
            return ( <div className="wprm-admin-modal-field-richtext-legacy"><FieldText {...props} /></div> );
        } else {
            return ( <div className="wprm-admin-modal-field-richtext-legacy"><FieldTextarea {...props} /></div> );
        }
    }

    const editor = useMemo(() => withHtml(
        withLinks(withHistory(withReact(createEditor()))),
        props
    ), []);

    let savedValue = props.value;

    // Make sure singleLine is surrounded by exactly 1 paragraph.
    if ( props.value && props.singleLine ) {
        savedValue = '' + props.value; // Make sure it's a string.
        savedValue = savedValue.replace( '<p>', '' );
        savedValue = savedValue.replace( '</p>', '' );

        savedValue = `<p>${ savedValue }</p>`;
    }
    
    const defaultValue = [{
        type: 'paragraph',
        children: [{ text: '' }],
    }];

    let initialValue;
    try {
        initialValue = props.value ? getValueFromHtml( savedValue ) : defaultValue;
    } catch( e ) {
        alert( 'Error loading one of the rich text fields. Some information may be lost. Please check the summary, equipment, ingredients and instructions before saving. Make sure your browser is updated to the latest version if you keep getting this message.' );
        console.log( 'Text Value', savedValue );
        console.log( 'FieldRichText Error', e );
        initialValue = defaultValue;
    }
    
    const [value, setValue] = useState( initialValue );

    const debounceRef = useRef( null );
    const propsRef = useRef( props );
    propsRef.current = props;

    useEffect(() => {
        return () => {
            if ( debounceRef.current ) {
                clearTimeout( debounceRef.current );
            }
        };
    }, []);

    const flushChange = () => {
        let newValue = serialize( editor );

        if ( propsRef.current.singleLine ) {
            // Strip surrounding paragraph tags if present.
            newValue = newValue.replace(/^<p>(.*)<\/p>$/gm, '$1');
        }

        propsRef.current.onChange( newValue );
        debounceRef.current = null;
    };

    return (
        <Slate
            spellCheck
            editor={editor}
            initialValue={value}
            onChange={value => {
                    setValue(value);

                    if ( debounceRef.current ) {
                        clearTimeout( debounceRef.current );
                    }

                    debounceRef.current = setTimeout( flushChange, 500 );
                }
            }
        >
            {
                props.className
                && 'wprm-admin-modal-field-instruction-text' === props.className
                &&
                <InlineIngredients
                    ingredients={ props.hasOwnProperty( 'ingredients' ) ? props.ingredients : null }
                    instructions={ props.hasOwnProperty( 'instructions' ) ? props.instructions : null }
                    instructionsRef={ props.hasOwnProperty( 'instructionsRef' ) ? props.instructionsRef : null }
                    allIngredients={ props.hasOwnProperty( 'allIngredients' ) ? props.allIngredients : null }
                />
            }
            <Toolbar
                type={ props.toolbar ? props.toolbar : 'all' }
                isMarkActive={ isMarkActive }
                toggleMark={ toggleMark }
            />
            <Editable
                className={ `wprm-admin-modal-field-richtext${ props.className ? ` ${ props.className }` : ''}${ props.singleLine ? ` wprm-admin-modal-field-richtext-singleline` : ''}` }
                placeholder={ props.placeholder }
                renderElement={ useCallback(props => <Element {...props} />, []) }
                renderLeaf={ useCallback(props => <Leaf {...props} />, []) }
                onBlur={() => {
                    if ( debounceRef.current ) {
                        clearTimeout( debounceRef.current );
                        flushChange();
                    }
                }}
                onFocus={() => {
                    // Firefox problems:
                    // If used, cursor will always show up at the end of the content, even when clicking inside.
                    // If not used, no cursor shows up when content is empty, so do apply in Firefox then as well.
                    if ( ! isFirefox() || '' === props.value || '<p></p>' === props.value ) {
                        Transforms.deselect( editor );
                        Transforms.select(editor, {
                            anchor: Editor.start(editor, []),
                            focus: Editor.end(editor, []),
                        });
                        Transforms.collapse( editor, {
                            edge: 'end',
                        });
                    }
                }}
                onKeyDown={event => {
                    // Prevent ENTER key in singleLine mode.
                    if ( props.singleLine && isHotkey( 'enter', event ) ) {
                        event.preventDefault();
                        return;
                    }

                    // Special handling of inline ingredients (contentEditable false hides cursor).
                    if ( ! [ 'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Meta', 'Alt', 'Control', 'Shift', 'Escape' ].includes( event.key ) ) {
                        const { selection } = editor;
                        const isCollapsed = selection && Range.isCollapsed(selection);

                        // Only when collapsed (otherwise issues when using CTRL-V with ingredient inside, for example).
                        if ( isCollapsed ) {
                            const [block] = Editor.nodes(editor, { match: n => n.type === 'ingredient' });
                            if ( block ) {
                                // Backspace or delete converts to regular text.
                                if ( [ 'Backspace', 'Delete' ].includes( event.key ) ) {
                                    Transforms.unwrapNodes(editor, { match: n => n.type === 'ingredient' });

                                    event.preventDefault();
                                    return;
                                }

                                // Move selection after ingredient and insert there.
                                const afterIngredient = {
                                    path: Path.next( block[1] ),
                                    offset: 0,
                                };

                                // Check if next path exists.
                                if ( Node.has( editor, afterIngredient.path ) ) {
                                    Transforms.select(editor, afterIngredient );
                                    
                                    // Simulate text added.
                                    const keyText = event.key;
                                    if ( 1 === keyText.length ) {
                                        Transforms.insertText( editor, keyText );
                                    }
                                } else {
                                    // Insert new text node if it doesn't exist.
                                    const keyText = event.key;
                                    if ( 1 === keyText.length ) {
                                        Transforms.insertNodes(
                                            editor,
                                            { text: keyText },
                                            { at: afterIngredient.path }
                                        );
                                        
                                        // Move cursor to end of inserted text.
                                        Transforms.select(editor, {
                                            path: afterIngredient.path,
                                            offset: 1
                                        });
                                    }
                                }

                                event.preventDefault();
                                return;
                            }
                        }
                    }

                    // Check for mark shortcodes.
                    for (const hotkey in HOTKEYS) {
                        if ( isHotkey(hotkey, event) ) {
                            event.preventDefault()
                            const mark = HOTKEYS[hotkey]
                            toggleMark(editor, mark)
                        }
                    }

                    // Pass along key down.
                    if ( props.onKeyDown ) {
                        props.onKeyDown( event );
                    }
                }}
                tabIndex={ 0 }
            />
        </Slate>
    );
}, (prevProps, nextProps) => {
    // Re-render when text changes, inline ingredients portal becomes available, or ingredient data changes.
    return prevProps.value === nextProps.value
        && prevProps.inlineIngredientsPortalRendered === nextProps.inlineIngredientsPortalRendered
        && prevProps.allIngredients === nextProps.allIngredients;
});

const withLinks = editor => {
    const { isInline } = editor;

    editor.isInline = element => {
        return INLINE_BLOCKS.includes( element.type ) ? true : isInline( element );
    }
  
    return editor;
}

const withHtml = ( editor, props ) => {
    const { insertData } = editor

    editor.insertData = data => {
        const html = data.getData('text/html');

        if ( html ) {
            const parsed = new DOMParser().parseFromString(html, 'text/html');
            const fragment = deserialize( parsed.body, props.singleLine );
            Transforms.insertFragment( editor, fragment );
            return;
        }

        insertData( data );
    }

    return editor;
}

const getValueFromHtml = ( htmlString ) => {
    // Remove comments from HTML string.
    htmlString = htmlString.replace(/<!--[\s\S]*?-->/g, '');

    // Convert temperature shortcode to its own element.
    const regex = /\[wprm-temperature(\s.*?)]/gm;

    let m;
    while ((m = regex.exec(htmlString)) !== null) {
        let attrMatch;

        attrMatch = new RegExp(' value=\\"(.*?)"', 'gm').exec( m[1] );
        const value = attrMatch ? attrMatch[1] : '';

        attrMatch = new RegExp(' unit=\\"(.*?)"', 'gm').exec( m[1] );
        const unit = attrMatch ? attrMatch[1] : '';

        attrMatch = new RegExp(' icon=\\"(.*?)"', 'gm').exec( m[1] );
        const icon = attrMatch ? attrMatch[1] : '';

        attrMatch = new RegExp(' help=\\"(.*?)"', 'gm').exec( m[1] );
        const help = attrMatch ? attrMatch[1] : '';

        const node = `<wprm-temperature unit="${ unit }" icon="${ icon }" help="${ help }">${ value }</wprm-temperature>`;
        
        htmlString = htmlString.replace( m[0], node );
    }

    // Convert ingredient shortcode to its own element.
    const inlineRegex = /\[wprm-ingredient(\s.*?)]/gm;

    m = null;
    while ((m = inlineRegex.exec(htmlString)) !== null) {
        let attrMatch;

        attrMatch = new RegExp(' uid=\\"(.*?)"', 'gm').exec( m[1] );
        const uid = attrMatch ? attrMatch[1] : '';

        attrMatch = new RegExp(' removed=\\"(.*?)"', 'gm').exec( m[1] );
        const removed = attrMatch && '1' === attrMatch[1] ? true : false;

        attrMatch = new RegExp(' text=\\"(.*?)"', 'gm').exec( m[1] );
        const text = attrMatch ? attrMatch[1] : '';

        const node = `<wprm-ingredient uid="${ uid }" removed="${ removed ? '1': '0' }">${ text }</wprm-ingredient>`;
        
        htmlString = htmlString.replace( m[0], node );
    }

    // Prevent DOM parser from breaking wprm-code.
    const codeRegex = /<wprm-code>(.+?)<\/wprm-code>/gm;

    while ((m = codeRegex.exec(htmlString)) !== null) {
        if (m.index === codeRegex.lastIndex) {
            codeRegex.lastIndex++;
        }
        
        let code = m[1];
        code = code.replace( /</gm, '&lt;' );

        htmlString = htmlString.replace( m[0], `<wprm-code>${ code }</wprm-code>` );
    }

    // Deserialize HTML string.
    const document = new DOMParser().parseFromString( htmlString, 'text/html' );
    const deserialized = deserialize( document.body );

    // Make sure top level blocks are paragraphs.
    for ( let i = 0; i < deserialized.length; i++ ) {
        const block = deserialized[i];
        if ( block.hasOwnProperty( 'text' ) ) {
            deserialized[i] = {
                type: 'paragraph',
                children: [block],
            };
        }
    }

    return deserialized;
}
  
const toggleMark = (editor, format) => {
    const isActive = isMarkActive(editor, format)
  
    if (isActive) {
      Editor.removeMark(editor, format)
    } else {
      Editor.addMark(editor, format, true)
    }
}
  
const isMarkActive = (editor, format) => {
    const marks = Editor.marks(editor)
    return marks ? marks[format] === true : false
}

// Wrapper to avoid changing export type if other files expect a component.
// React.memo returns a component, so this is fine.
export default RichEditor;
