import React, { Component, Fragment } from 'react';
import Parser, { domToReact } from 'html-react-parser';

// Functionality for the preview.
import '../../../public/expandable';
import '../../../other/size-conditions';
import '../../../../../../wp-recipe-maker-premium/assets/js/public/share-options-popup';

// Styles for the preview.
import '../../../../css/public/template_reset.scss';
import '../../../../css/public/tooltip.scss';
import '../../../../css/shortcodes/shortcodes.scss';
import '../../../../../../wp-recipe-maker-premium/assets/css/shortcodes/shortcodes.scss';
import '../../../../css/admin/shared/dropdown-menu.scss';

import Helpers from '../../general/Helpers';
import Loader from 'Shared/Loader';
import Icon from 'Shared/Icon';
import TemplateIcon from '../../general/Icon';
import DropdownMenu from 'Shared/DropdownMenu';
import Block from './Block';
import Element from './Element';
import SortableBlockList from './SortableBlockList';
import PreviewInteractionsContext from './PreviewInteractionsContext';
import AddPatterns from '../../menu/AddPatterns';
import AddBlocks from '../../menu/AddBlocks';
import BlockProperties from '../../menu/BlockProperties';
import PreviewRecipe from './PreviewRecipe';
import AddBlocksView from './AddBlocksView';
import Shortcodes from '../../general/shortcodes';
import Elements from '../../general/elements';
import Patterns from '../../general/patterns';
import { canHaveChildren } from './treeUtils';

// Helper function to remove lowercase event handler attributes that React doesn't accept
const removeLowercaseEventHandlers = (domNode) => {
    if (domNode.attribs) {
        // List of common event handlers to check for lowercase versions
        const eventHandlers = [
            'onmouseenter', 'onmouseleave', 'onmouseover', 'onmouseout',
            'onclick', 'ondblclick', 'onmousedown', 'onmouseup',
            'onfocus', 'onblur', 'onchange', 'oninput', 'onsubmit',
            'onkeydown', 'onkeyup', 'onkeypress',
            'ontouchstart', 'ontouchend', 'ontouchmove',
            'onload', 'onerror', 'onscroll'
        ];
        
        eventHandlers.forEach(handler => {
            if (domNode.attribs.hasOwnProperty(handler)) {
                delete domNode.attribs[handler];
            }
        });
    }
    return domNode;
};

const { shortcodeGroups, shortcodeKeysAlphebetically, getShortcodeId } = Shortcodes;

export default class PreviewTemplate extends Component {
    constructor(props) {
        super(props);

        let recipe = wprm_admin_template.preview_recipe;
        if ( 'demo' === recipe || 0 === recipe.id ) {
            recipe = {
                id: 'demo',
                text: 'Use WPRM Demo Recipe',
            };
        }

        this.state = {
            recipe,
            width: 600,
            html: '',
            htmlMap: '',
            parsedHtml: '',
            shortcodes: [],
            editingBlock: props.editingBlock !== undefined ? props.editingBlock : false,
            addingPattern: false,
            addingBlock: false,
            movingBlock: false,
            hoveringBlock: false,
            copyPasteMode: false, // 'copy', 'paste', or false
            copyPasteBlock: false, // uid of block in copy/paste mode
            hasError: false,
            addBlocksSearchQuery: '',
            scrollToCategory: null,
            showNewBlocksOnly: false,
        }
    }

    componentDidCatch() {
        this.setState({
            hasError: true,
        });
    }

    componentDidMount() {
        this.checkHtmlChange();
    }

    componentDidUpdate(prevProps) {
        // Sync editingBlock from props (e.g., when URL changes)
        if ( this.props.editingBlock !== undefined && this.props.editingBlock !== prevProps.editingBlock && this.props.editingBlock !== this.state.editingBlock ) {
            this.setState({
                editingBlock: this.props.editingBlock,
            });
        }
        
        // Clear hover state when mode changes and force HTML update to re-render components with new mode.
        if ( this.props.mode !== prevProps.mode ) {
            this.setState({
                hoveringBlock: false,
                addBlocksSearchQuery: '', // Clear search when switching modes
                scrollToCategory: null, // Clear scroll target when switching modes
            }, () => {
                // Force HTML update to ensure components re-render with new mode prop.
                this.changeHtml();
            });
        } else if ( 'shortcode-generator' === this.props.mode ) {
            // Make sure editing block stays on the shortcode.
            if ( this.state.editingBlock !== 0 ) {
                this.onChangeEditingBlock(0);
            } else {
                this.checkHtmlChange();
            }
        } else {
            // If changing to edit blocks mode, only reset if not coming from URL navigation
            if ( 'blocks' === this.props.mode && this.props.mode !== prevProps.mode ) {
                // Only reset if editingBlock prop is not set (not coming from URL)
                if ( this.props.editingBlock === undefined || this.props.editingBlock === false ) {
                    this.onChangeEditingBlock(false);
                } else {
                    // Sync with prop from URL
                    this.setState({
                        editingBlock: this.props.editingBlock,
                    });
                }
            } else {
                this.checkHtmlChange(); // onChangeEditingBlock forces HTML update, so no need to check.
            }
        }
    }

    checkHtmlChange() {
        if ( this.props.template.html !== this.state.html ) {
            this.changeHtml();
        }
    }

    changeHtml() {
        const parsed = this.parseHtml(this.props.template.html);

        this.setState({
            html: this.props.template.html,
            htmlMap: parsed.htmlMap,
            parsedHtml: parsed.html,
            shortcodes: parsed.shortcodes,
            hasError: false,
        });
    }

    parseHtml(html) {
        // Find shortcodes in HTML.
        let shortcodes = [];
        const regex = /\[([^\s\]]*)\s*([^\]]*?)\]|<div class="(wprm-layout-[^\s"]*)\s?(.*?)"(?: style="(.*?)")?>/gmi;

        // Loop over all the matches we found and replace in HTML to parse.
        let htmlToParse = html;
        let waitingForClosing = {};

        let match;
        while ((match = regex.exec(html)) !== null) {
            // Handle shortcodes first.
            if ( '[' === match[0].substring(0, 1) ) {
                // Check for attributes in shortcode.
                let shortcode_atts = Helpers.getShortcodeAttributes( match[2] );

                // Get shortcode name.
                let uid = shortcodes.length;
                let id = match[1];
                const name = Helpers.getShortcodeName(id);

                // Check if this is a shortcode that needs a closing tag.
                const isClosingShortcode = '/' === id.substring(0, 1);
                const needsClosingShortcode = Shortcodes.contentShortcodes.includes( id );

                // We have a shortcode that still needs a closing shortcode, only add placeholder comment.
                if ( needsClosingShortcode ) {
                    htmlToParse = htmlToParse.replace(match[0], '<!--wprm-opening-' + uid + '-->');
                    waitingForClosing[ id ] = {
                        match,
                        uid,
                        id,
                        name,
                        attributes: shortcode_atts,
                    }
                    shortcodes[uid] = false; // Placeholder.
                    continue;
                }

                // We have a closing shortcode, check if we have a matching shortcode that's still open.
                if ( isClosingShortcode ) {
                    const openingShortcode = waitingForClosing.hasOwnProperty( id.substring(1) ) ? waitingForClosing[ id.substring(1) ] : false;
                    let content = false;

                    if ( openingShortcode ) {
                        htmlToParse = htmlToParse.replace( match[0], '<!--wprm-closing-' + openingShortcode.uid + '-->' );
                        
                        // Find content in between opening and closing shortcode.
                        const contentRegex = new RegExp( '<!--wprm-opening-' + openingShortcode.uid + '-->(.*)<!--wprm-closing-' + openingShortcode.uid + '-->', 'mis' );
                        let contentMatch;

                        if ((contentMatch = contentRegex.exec(htmlToParse)) !== null) {
                            content = contentMatch[1];

                            // Add placeholder elements in htmlToParse (keep comment for closing element).
                            htmlToParse = htmlToParse.replace( '<!--wprm-opening-' + openingShortcode.uid + '-->', '<wprm-replace-shortcode-with-block uid="' + openingShortcode.uid + '">' );
                            htmlToParse = htmlToParse.replace( '<!--wprm-closing-' + openingShortcode.uid + '-->', '<!--wprm-closing-' + openingShortcode.uid + '--></wprm-replace-shortcode-with-block>' );
                            shortcodes[ openingShortcode.uid ] = {
                                uid: openingShortcode.uid,
                                id: openingShortcode.id,
                                name: openingShortcode.name,
                                attributes: openingShortcode.attributes,
                                content,
                            };
                        }
                    }
                    
                    if ( ! openingShortcode || false === content ) {
                        // No matching opening shortcode or no opening comment found, remove closing shortcode.
                        htmlToParse = htmlToParse.replace(match[0], '');
                    }
                    continue;
                }

                // We have a regular shortcode, add placeholder element and register shortcode.
                htmlToParse = htmlToParse.replace(match[0], '<wprm-replace-shortcode-with-block uid="' + uid + '"></wprm-replace-shortcode-with-block>');
                shortcodes[uid] = {
                    uid,
                    id,
                    name,
                    attributes: shortcode_atts,
                    content: false,
                };
            } else {
                // Get layout element.
                let uid = shortcodes.length;
                let id = match[3];
                let content;
                let classes = match[4] ? match[4].split( ' ' ) : [];
                let style = match[5] ? match[5].split( ';' ) : [];
                let name = Helpers.getShortcodeName(id);

                const customClass = classes.find( (c) => ! c.startsWith( 'wprm-' ) );
                if ( customClass ) {
                    name += ' (' + customClass + ')';
                }

                // Remove prefix from style.
                style = style.map( (s) => {
                    return s.replace( '--' + id + '-', '' );
                } );

                // Remove empty from style.
                style = style.filter( (s) => {
                    return s.trim() !== '';
                } );

                const elementWithUid = match[0].replace( '">', '" uid="' + uid + '">' );

                // Find closing div.
                const remainingHtmlIndex = htmlToParse.indexOf( match[0] ) + match[0].length;
                const remainingHtml = htmlToParse.substring( remainingHtmlIndex );
                const closingDivIndex = this.getIndexOfClosingDiv( remainingHtml );

                // Add comment for closing div.
                if ( -1 === closingDivIndex ) {
                    content = '';
                    htmlToParse = htmlToParse.substring( 0, remainingHtmlIndex + closingDivIndex + 1 ) + '<!--wprm-closing-' + uid + '--></div>' + htmlToParse.substring( remainingHtmlIndex + closingDivIndex + 1 );
                } else {
                    content = remainingHtml.substring( 0, closingDivIndex );
                    htmlToParse = htmlToParse.substring( 0, remainingHtmlIndex + closingDivIndex ) + '<!--wprm-closing-' + uid + '-->' + htmlToParse.substring( remainingHtmlIndex + closingDivIndex );
                }

                htmlToParse = htmlToParse.replace(match[0], elementWithUid);
                shortcodes[uid] = {
                    uid,
                    id,
                    name,
                    classes,
                    style,
                    content,
                };
            }
        }

        // Get HTML with shortcodes replaced by blocks.
        let parsedHtml = <Loader/>;
        try {
            const recipeId = this.state.recipe ? this.state.recipe.id : false;

            const parseOptions = {
                replace: function(domNode) {
                    // Remove lowercase event handlers before processing
                    removeLowercaseEventHandlers(domNode);
                    
                    if ( domNode.name == 'wprm-replace-shortcode-with-block' ) {
                        return this.replaceDomNodeWithBlock( domNode, shortcodes, recipeId, parseOptions );
                    }

                    if ( domNode.name == 'div' && domNode.attribs.class && 'wprm-layout-' === domNode.attribs.class.substring( 0, 12 ) ) {
                        return this.replaceDomNodeWithElement( domNode, shortcodes, recipeId, parseOptions );
                    }
                }.bind(this)
            }
            parsedHtml = Parser(htmlToParse, parseOptions);
        } catch ( error ) {
            console.log( 'Error parsing HTML', error );
        }

        // Calculate depth for each shortcode based on nesting in HTML structure
        shortcodes = this.calculateShortcodeDepths(shortcodes, htmlToParse);

        return {
            htmlMap: htmlToParse,
            html: parsedHtml,
            shortcodes,
        }
    }

    calculateShortcodeDepths(shortcodes, htmlToParse) {
        // Create a map of uid to depth
        const depthMap = {};
        
        // Track the current depth as we parse through the HTML
        let currentDepth = 0;
        const uidStack = [];
        
        // Regular expression to match opening and closing tags for blocks and elements
        // Match opening tags: <wprm-replace-shortcode-with-block uid="X"> or <div ... uid="X">
        // Match closing tags: </wprm-replace-shortcode-with-block> or <!--wprm-closing-X-->
        const blockRegex = /<wprm-replace-shortcode-with-block\s+uid="(\d+)"|<div[^>]*uid="(\d+)"[^>]*>|<\/wprm-replace-shortcode-with-block>|<!--wprm-closing-(\d+)-->/g;
        
        let match;
        while ((match = blockRegex.exec(htmlToParse)) !== null) {
            if (match[1]) {
                // Opening block shortcode: <wprm-replace-shortcode-with-block uid="X">
                const uid = parseInt(match[1]);
                depthMap[uid] = currentDepth;
                uidStack.push({ type: 'block', uid });
                currentDepth++;
            } else if (match[2]) {
                // Opening layout element: <div ... uid="X">
                const uid = parseInt(match[2]);
                // Only process if it's a layout element (has wprm-layout- class)
                if (match[0].includes('wprm-layout-')) {
                    depthMap[uid] = currentDepth;
                    uidStack.push({ type: 'element', uid });
                    currentDepth++;
                }
            } else if (match[0].includes('</wprm-replace-shortcode-with-block>')) {
                // Closing block shortcode
                if (uidStack.length > 0 && uidStack[uidStack.length - 1].type === 'block') {
                    uidStack.pop();
                    currentDepth = Math.max(0, currentDepth - 1);
                }
            } else if (match[3]) {
                // Closing layout element (comment): <!--wprm-closing-X-->
                const uid = parseInt(match[3]);
                if (uidStack.length > 0 && uidStack[uidStack.length - 1].uid === uid && uidStack[uidStack.length - 1].type === 'element') {
                    uidStack.pop();
                    currentDepth = Math.max(0, currentDepth - 1);
                }
            }
        }
        
        // Add depth property to each shortcode
        return shortcodes.map(shortcode => {
            if (shortcode && typeof shortcode === 'object') {
                return {
                    ...shortcode,
                    depth: depthMap[shortcode.uid] !== undefined ? depthMap[shortcode.uid] : 0
                };
            }
            return shortcode;
        });
    }

    getIndexOfClosingDiv( html ) {
        let index = -1;
        let depth = 1; // We're already inside the opening div.
        let i = 0;

        while ( i < html.length ) {
            if ( '<div' === html.substring( i, i + 4 ) ) {
                depth++;
            } else if ( '</div' === html.substring( i, i + 5 ) ) {
                depth--;
            }

            if ( 0 === depth ) {
                index = i;
                break;
            }

            i++;
        }

        return index;
    }

    replaceDomNodeWithElement( domNode, shortcodes, recipeId, parseOptions ) {
        const shortcode = shortcodes[ domNode.attribs.uid ];

        if ( ! shortcode ) {
            return null
        }

        return <Element
                    recipeId={ recipeId }
                    shortcode={ shortcode }
                    shortcodes={ shortcodes }
                    mode={ this.props.mode }
                    onClassesChange={ this.onClassesChange.bind(this) }
                    onStyleChange={ this.onStyleChange.bind(this) }
                    editingBlock={this.state.editingBlock}
                    onChangeEditingBlock={this.onChangeEditingBlock.bind(this)}
                    hoveringBlock={this.state.hoveringBlock}
                    onChangeHoveringBlock={this.onChangeHoveringBlock.bind(this)}
                    onRemoveBlock={this.onRemoveBlock.bind(this)}
                    onChangeMovingBlock={this.onChangeMovingBlock.bind(this)}
                    copyPasteMode={this.state.copyPasteMode}
                    copyPasteBlock={this.state.copyPasteBlock}
                    replaceDomNodeWithElement={this.replaceDomNodeWithElement.bind(this)}
                    replaceDomNodeWithBlock={this.replaceDomNodeWithBlock.bind(this)}
                    parseOptions={parseOptions}
                >
                    { false !== shortcode.content ? domToReact( domNode.children, parseOptions ) : null }
                </Element>;
    }

    replaceDomNodeWithBlock( domNode, shortcodes, recipeId, parseOptions ) {
        const shortcode = shortcodes[ domNode.attribs.uid ];

        if ( ! shortcode ) {
            return null
        }

        return <Block
                    mode={ 'shortcode-generator' === this.props.mode ? this.props.mode : null }
                    templateMode={ this.props.mode }
                    recipeId={ recipeId }
                    shortcode={ shortcode }
                    shortcodes={ shortcodes }
                    onBlockPropertyChange={ this.onBlockPropertyChange.bind(this) }
                    onBlockPropertiesChange={ this.onBlockPropertiesChange.bind(this) }
                    editingBlock={this.state.editingBlock}
                    onChangeEditingBlock={this.onChangeEditingBlock.bind(this)}
                    hoveringBlock={this.state.hoveringBlock}
                    onChangeHoveringBlock={this.onChangeHoveringBlock.bind(this)}
                    onRemoveBlock={this.onRemoveBlock.bind(this)}
                    onChangeMovingBlock={this.onChangeMovingBlock.bind(this)}
                    copyPasteMode={this.state.copyPasteMode}
                    copyPasteBlock={this.state.copyPasteBlock}
                    onChangeCopyPasteMode={this.onChangeCopyPasteMode.bind(this)}
                    replaceDomNodeWithElement={this.replaceDomNodeWithElement.bind(this)}
                    replaceDomNodeWithBlock={this.replaceDomNodeWithBlock.bind(this)}
                    parseOptions={parseOptions}
                >
                    { false !== shortcode.content ? domToReact( domNode.children, parseOptions ) : null }
                </Block>;
    }

    unparseHtml() {
        let html = this.state.htmlMap;

        for ( let shortcode of this.state.shortcodes ) {
            if ( Elements.layoutElements.includes( shortcode.id ) ) {
                const elementRegex = new RegExp( '<div class="wprm-layout-[^>]*? uid="' + shortcode.uid + '">', 'mis' );
                let elementMatch;
        
                if ((elementMatch = elementRegex.exec(html)) !== null) {
                    // Classes to add.
                    let classes = [
                        shortcode.id,
                    ];
                    if ( shortcode.hasOwnProperty( 'classes' ) && shortcode.classes.length ) {
                        classes = classes.concat( shortcode.classes );
                    }

                    // Inline style to add.
                    let style = '';
                    if ( shortcode.hasOwnProperty( 'style' ) && shortcode.style.length ) {

                        let prefixedStyle = shortcode.style.map( (style) => {
                            return '--' + shortcode.id + '-' + style;
                        } );

                        style = ' style="' + prefixedStyle.join( ';' ) + ';"';
                    }

                    const elementToOutput = '<div class="' + classes.join( ' ' ) + '"' + style + '>';
                    
                    html = html.replace( elementMatch[0], elementToOutput );
                    html = html.replace('<!--wprm-closing-' + shortcode.uid + '-->', '');
                }
                
            } else {
                // Shortcodes, regular or content.
                let fullShortcode = Helpers.getFullShortcode(shortcode);

                if ( false !== shortcode.content ) {
                    const closingShortcode = '[/' + shortcode.id + ']';

                    html = html.replace('<wprm-replace-shortcode-with-block uid="' + shortcode.uid + '">', fullShortcode);
                    html = html.replace('<!--wprm-closing-' + shortcode.uid + '--></wprm-replace-shortcode-with-block>', closingShortcode);
                } else {
                    html = html.replace('<wprm-replace-shortcode-with-block uid="' + shortcode.uid + '"></wprm-replace-shortcode-with-block>', fullShortcode);
                }
            }
        }

        return html;
    }

    onClassesChange(uid, classes) {
        let newState = this.state;
        newState.shortcodes[uid].classes = classes;

        this.setState(newState,
            () => {
                let newHtml = this.unparseHtml();
                this.props.onChangeHTML(newHtml);
            }
        );
    }

    onStyleChange(uid, style) {
        let newState = this.state;
        newState.shortcodes[uid].style = style;

        this.setState(newState,
            () => {
                let newHtml = this.unparseHtml();
                this.props.onChangeHTML(newHtml);
            }
        );
    }

    onBlockPropertyChange(uid, property, value) {
        let properties = {};
        properties[property] = value;
        this.onBlockPropertiesChange(uid, properties);
    }

    onBlockPropertiesChange(uid, properties) {
        let newState = this.state;
        newState.shortcodes[uid].attributes = {
            ...newState.shortcodes[uid].attributes,
            ...properties,
        }

        this.setState(newState,
            () => {
                let newHtml = this.unparseHtml();
                this.props.onChangeHTML(newHtml);
            });
    }

    onChangeEditingBlock(uid) {
        if (uid !== this.state.editingBlock) {
            this.setState({
                editingBlock: uid,
                hoveringBlock: false,
                copyPasteMode: false, // Reset copy/paste mode when switching blocks
                copyPasteBlock: false,
            }, () => {
                // Notify parent component (App) to update URL
                if (this.props.onChangeEditingBlock) {
                    this.props.onChangeEditingBlock(uid);
                }
                this.changeHtml();
            });
            // Force HTML update to trickle down editingBlock prop.
        }
    }

    onChangeCopyPasteMode(mode, blockUid) {
        // Only update copy/paste state, preserve editing block.
        this.setState((prevState) => ({
            copyPasteMode: mode,
            copyPasteBlock: blockUid,
            // Explicitly preserve editingBlock to prevent accidental changes.
            editingBlock: prevState.editingBlock,
        }));
    }

    onChangeHoveringBlock(uid) {
        if (uid !== this.state.hoveringBlock) {
            this.setState({
                hoveringBlock: uid,
            });
        }
    }

    onChangeAddingPattern(id) {
        if (id !== this.state.addingPattern) {
            this.setState({
                addingPattern: id,
            });
        }
    }

    onAddPattern( uid, position = 'after' ) {
        const pattern = Patterns.patterns[ this.state.addingPattern ];

        if ( pattern ) {
            if ( pattern.hasOwnProperty( 'html' ) && pattern.html ) {
                this.onAddHTML( pattern.html, uid, position );
            }

            if ( pattern.hasOwnProperty( 'css' ) && pattern.css ) {
                const patternCss = pattern.css.replace( /%template%/g, '.wprm-recipe-template-' + this.props.template.slug );
                const newCSS = this.props.template.style.css + '\n' + patternCss;
                this.props.onChangeCSS( newCSS );
            }
        }
    }

    onChangeAddingBlock(id) {
        if (id !== this.state.addingBlock) {
            this.setState({
                addingBlock: id,
            });
        }
    }

    onAddBlock( uid, position = 'after' ) {
        // Get shortcode to add.
        let shortcode = '[' + this.state.addingBlock + ']';

        if ( Shortcodes.contentShortcodes.includes( this.state.addingBlock ) ) {
            shortcode = '[' + this.state.addingBlock + ']\n[/' + this.state.addingBlock + ']';
        }
        if ( Elements.layoutElements.includes( this.state.addingBlock ) ) {
            shortcode = '<div class="' + this.state.addingBlock + '">\n</div>';
        }

        this.onAddHTML( shortcode, uid, position, ( addedShortcodeUid ) => {
            this.onChangeEditingBlock( addedShortcodeUid );
        });
    }

    onAddHTML( code, uid, position = 'after', callback = false) {
        let htmlMap = this.state.htmlMap;
        let addedShortcodeUid;

        if ( 'start' === uid ) {
            htmlMap = code + '\n' + htmlMap;
            addedShortcodeUid = 0;
        } else {
            const targetIsLayoutElement = this.state.shortcodes[uid] && Elements.layoutElements.includes( this.state.shortcodes[uid].id );
            const targetIsContentShortcode = ! targetIsLayoutElement && this.state.shortcodes[uid] && false !== this.state.shortcodes[uid].content;

            // Handle 'before' position by finding the previous sibling
            if ( 'before' === position ) {
                // Find the opening tag for this shortcode
                const openingTagRegex = targetIsLayoutElement 
                    ? new RegExp(`<div[^>]*uid="${uid}"[^>]*>`, 'i')
                    : new RegExp(`<wprm-replace-shortcode-with-block uid="${uid}"`, 'i');
                
                const match = htmlMap.match(openingTagRegex);
                
                if (match && match.index !== undefined) {
                    const insertIndex = match.index;
                    // Find the previous block/element before this one
                    const beforeHtml = htmlMap.substring(0, insertIndex);
                    const beforeMatches = beforeHtml.match(/uid="(\d+)"/gmi);
                    
                    if (beforeMatches && beforeMatches.length > 0) {
                        const prevUid = parseInt(beforeMatches[beforeMatches.length - 1].match(/\d+/gmi)[0]);
                        // Add after the previous sibling
                        return this.onAddHTML(code, prevUid, 'after', callback);
                    } else {
                        // No previous sibling, add at start
                        return this.onAddHTML(code, 'start', 'after', callback);
                    }
                }
                // Fallback to 'after' if we can't find the opening tag
                position = 'after';
            }

            let afterShortcode = targetIsLayoutElement ? '<!--wprm-closing-' + uid + '--></div>' : '<wprm-replace-shortcode-with-block uid="' + uid + '"></wprm-replace-shortcode-with-block>';
            addedShortcodeUid = uid + 1;

            if ( targetIsContentShortcode || targetIsLayoutElement ) {
                if ( 'inside-start' === position ) {
                    afterShortcode = targetIsLayoutElement ? ' uid="' + uid + '">' : '<wprm-replace-shortcode-with-block uid="' + uid + '">';
                } else {
                    if ( targetIsContentShortcode ) {
                        afterShortcode = '<!--wprm-closing-' + uid + '--></wprm-replace-shortcode-with-block>';
                    }

                    // Get htmlMap substr before closing shortcode.
                    const beforeShortcode = htmlMap.substring( 0, htmlMap.indexOf( afterShortcode ) );

                    // Get last uid before closing shortcode.
                    const lastUid = beforeShortcode.match(/uid="(\d+)"/gmi).pop().match(/\d+/gmi).pop();
                    addedShortcodeUid = parseInt( lastUid ) + 1;
                }
            }

            if ( 'inside-end' === position ) {
                htmlMap = htmlMap.replace( afterShortcode, code + '\n' + afterShortcode );
            } else {
                // Default to add after. Works for inside-start as well.
                htmlMap = htmlMap.replace( afterShortcode, afterShortcode + '\n' + code );
            }
        }

        if ( htmlMap !== this.state.htmlMap) {
            this.setState({
                addingPattern: false,
                addingBlock: false,
                hoveringBlock: false,
                htmlMap,
            },
                () => {
                    let newHtml = this.unparseHtml();
                    this.props.onChangeHTML(newHtml);
                    this.props.onChangeMode( 'blocks' );

                    this.setState({
                        addingPattern: false,
                        addingBlock: false,
                        hoveringBlock: false,
                    }, () => {
                        if ( callback ) {
                            callback( addedShortcodeUid );
                        }
                    });
                });
        }
    }

    onDuplicateBlock(uid) {
        const shortcode = this.state.shortcodes[uid];
        if ( !shortcode ) {
            return;
        }

        let htmlMap = this.state.htmlMap;
        const sourceIsLayoutElement = Elements.layoutElements.includes( shortcode.id );
        const sourceIsContentShortcode = ! sourceIsLayoutElement && false !== shortcode.content;

        let codeToAdd = '';

        if ( sourceIsLayoutElement ) {
            // Extract full layout element with all nested content (use greedy matching like onMoveBlock)
            const elementRegex = new RegExp( '<div class="wprm-layout-[^>]*? uid="' + uid + '">.*<!--wprm-closing-' + uid + '--></div>', 'mis' );
            let elementMatch = elementRegex.exec(htmlMap);
            
            if ( elementMatch ) {
                let extractedHtml = elementMatch[0];
                
                // Convert nested shortcode placeholders back to actual shortcodes
                extractedHtml = this.convertPlaceholdersToShortcodes(extractedHtml);
                
                // Remove uid attribute and closing comment to get clean HTML for duplication
                codeToAdd = extractedHtml
                    .replace(/\s+uid="\d+"/g, '') // Remove uid attribute
                    .replace(/<!--wprm-closing-\d+-->/g, ''); // Remove closing comment
            }
        } else if ( sourceIsContentShortcode ) {
            // For content shortcodes, extract the full block including all nested content (use greedy matching)
            const shortcodeRegex = new RegExp( '<wprm-replace-shortcode-with-block uid="' + uid + '">.*<!--wprm-closing-' + uid + '--></wprm-replace-shortcode-with-block>', 'mis' );
            let shortcodeMatch = shortcodeRegex.exec(htmlMap);
            
            if ( shortcodeMatch ) {
                // Extract inner content (everything between opening and closing tags, including nested blocks)
                const fullMatch = shortcodeMatch[0];
                let innerContent = fullMatch
                    .replace('<wprm-replace-shortcode-with-block uid="' + uid + '">', '')
                    .replace('<!--wprm-closing-' + uid + '--></wprm-replace-shortcode-with-block>', '');
                
                // Convert nested shortcode placeholders back to actual shortcodes
                innerContent = this.convertPlaceholdersToShortcodes(innerContent);
                
                // Generate the shortcode with content - use the extracted content which includes nested blocks
                codeToAdd = Helpers.getFullShortcode(shortcode) + '\n' + innerContent + '\n[/' + shortcode.id + ']';
            }
        } else {
            // Regular shortcode - generate it using Helpers
            codeToAdd = Helpers.getFullShortcode(shortcode);
        }

        if ( codeToAdd ) {
            this.onAddHTML( codeToAdd, uid, 'after' );
        }
    }

    convertPlaceholdersToShortcodes(html) {
        // Convert all shortcode placeholders in the given HTML back to actual shortcodes
        let convertedHtml = html;

        // Process shortcodes in reverse order of uid to avoid replacing nested placeholders incorrectly
        const sortedShortcodes = [...this.state.shortcodes].sort((a, b) => b.uid - a.uid);

        for ( let shortcode of sortedShortcodes ) {
            if ( !shortcode ) continue;

            if ( Elements.layoutElements.includes( shortcode.id ) ) {
                // Convert layout element placeholder
                const elementRegex = new RegExp( '<div class="wprm-layout-[^>]*? uid="' + shortcode.uid + '">', 'mis' );
                let elementMatch;
        
                if ((elementMatch = elementRegex.exec(convertedHtml)) !== null) {
                    // Classes to add.
                    let classes = [
                        shortcode.id,
                    ];
                    if ( shortcode.hasOwnProperty( 'classes' ) && shortcode.classes.length ) {
                        classes = classes.concat( shortcode.classes );
                    }

                    // Inline style to add.
                    let style = '';
                    if ( shortcode.hasOwnProperty( 'style' ) && shortcode.style.length ) {
                        let prefixedStyle = shortcode.style.map( (style) => {
                            return '--' + shortcode.id + '-' + style;
                        } );
                        style = ' style="' + prefixedStyle.join( ';' ) + ';"';
                    }

                    const elementToOutput = '<div class="' + classes.join( ' ' ) + '"' + style + '>';
                    
                    convertedHtml = convertedHtml.replace( elementMatch[0], elementToOutput );
                    convertedHtml = convertedHtml.replace('<!--wprm-closing-' + shortcode.uid + '-->', '');
                }
            } else {
                // Convert shortcode placeholder
                let fullShortcode = Helpers.getFullShortcode(shortcode);

                if ( false !== shortcode.content ) {
                    const closingShortcode = '[/' + shortcode.id + ']';

                    convertedHtml = convertedHtml.replace('<wprm-replace-shortcode-with-block uid="' + shortcode.uid + '">', fullShortcode);
                    convertedHtml = convertedHtml.replace('<!--wprm-closing-' + shortcode.uid + '--></wprm-replace-shortcode-with-block>', closingShortcode);
                } else {
                    convertedHtml = convertedHtml.replace('<wprm-replace-shortcode-with-block uid="' + shortcode.uid + '"></wprm-replace-shortcode-with-block>', fullShortcode);
                }
            }
        }

        return convertedHtml;
    }

    onRemoveBlock(uid) {
        let htmlMap = this.state.htmlMap;
        htmlMap = htmlMap.replace('<wprm-replace-shortcode-with-block uid="' + uid + '"></wprm-replace-shortcode-with-block>', '');

        // Remove closing shortcode if it exists.
        htmlMap = htmlMap.replace('<wprm-replace-shortcode-with-block uid="' + uid + '">', '');
        htmlMap = htmlMap.replace('<!--wprm-closing-' + uid + '--></wprm-replace-shortcode-with-block>', '');

        // Replace div element if exists.
        const elementRegex = new RegExp( '<div class="wprm-layout-[^>]*? uid="' + uid + '">', 'mis' );
        let elementMatch;

        if ((elementMatch = elementRegex.exec(htmlMap)) !== null) {
            htmlMap = htmlMap.replace( elementMatch[0], '' );
            htmlMap = htmlMap.replace('<!--wprm-closing-' + uid + '--></div>', '');
        }

        if ( htmlMap !== this.state.htmlMap) {
            this.setState({
                htmlMap,
            },
                () => {
                    let newHtml = this.unparseHtml();
                    this.props.onChangeHTML(newHtml);
                });
        }
    }

    onChangeMovingBlock(shortcode) {
        this.setState({
            movingBlock: shortcode,
        });
    }

    onMoveBlock( target, position = 'after' ) {
        let htmlMap = this.state.htmlMap;
        const sourceIsLayoutElement = this.state.shortcodes[this.state.movingBlock.uid] && Elements.layoutElements.includes( this.state.shortcodes[this.state.movingBlock.uid].id );
        const sourceIsContentShortcode = ! sourceIsLayoutElement && this.state.shortcodes[this.state.movingBlock.uid] && false !== this.state.shortcodes[this.state.movingBlock.uid].content;

        const targetIsLayoutElement = this.state.shortcodes[target] && Elements.layoutElements.includes( this.state.shortcodes[target].id );
        const targetIsContentShortcode = ! targetIsLayoutElement && this.state.shortcodes[target] && false !== this.state.shortcodes[target].content;

        let shortcode = '<wprm-replace-shortcode-with-block uid="' + this.state.movingBlock.uid + '"></wprm-replace-shortcode-with-block>';

        if ( sourceIsContentShortcode || sourceIsLayoutElement ) {
            // Get full element or shortcode, with everything inside.
            let shortcodeRegex = new RegExp( '<wprm-replace-shortcode-with-block uid="' + this.state.movingBlock.uid + '">.*<!--wprm-closing-' + this.state.movingBlock.uid + '--><\/wprm-replace-shortcode-with-block>', 'mis' );
            if ( sourceIsLayoutElement ) {
                shortcodeRegex = new RegExp( '<div class="wprm-layout-[^>]*? uid="' + this.state.movingBlock.uid + '">.*<!--wprm-closing-' + this.state.movingBlock.uid + '--><\/div>', 'mis' );
            }

            let shortcodeMatch;
            if ((shortcodeMatch = shortcodeRegex.exec(htmlMap)) !== null) {
                shortcode = shortcodeMatch[0];
            }
        }
        
        let targetShortcode = '<wprm-replace-shortcode-with-block uid="' + target + '"></wprm-replace-shortcode-with-block>';

        // Remove from current position.
        htmlMap = htmlMap.replace(shortcode, '');

        // Move to new position.
        if ( 'before' === position || 'inside' === position ) {
            if ( targetIsContentShortcode ) {
                targetShortcode = '<wprm-replace-shortcode-with-block uid="' + target + '">';
            } else if ( targetIsLayoutElement ) {
                const elementRegex = new RegExp( '<div class="wprm-layout-[^>]*? uid="' + target + '">', 'mis' );

                let elementMatch;
                if ((elementMatch = elementRegex.exec(htmlMap)) !== null) {
                    targetShortcode = elementMatch[0];
                } else {
                    if ( 'inside' === position ) {
                        targetShortcode = ' uid="' + target + '">';
                    } else {
                        return; // Did not find the div we want to but the shortcode before, so can't continue.
                    }
                }
            }

            if ( 'inside' === position ) {
                htmlMap = htmlMap.replace(targetShortcode, targetShortcode + '\n' + shortcode);
            } else {
                htmlMap = htmlMap.replace(targetShortcode, shortcode + '\n' + targetShortcode);
            }
        } else {
            if ( targetIsContentShortcode ) {
                targetShortcode = '<!--wprm-closing-' + target + '--></wprm-replace-shortcode-with-block>';
            } else if ( targetIsLayoutElement ) {
                targetShortcode = '<!--wprm-closing-' + target + '--></div>';
            }

            htmlMap = htmlMap.replace(targetShortcode, targetShortcode + '\n' + shortcode);
        }

        if ( htmlMap !== this.state.htmlMap) {
            this.setState({
                movingBlock: false,
                hoveringBlock: false,
                htmlMap,
            },
                () => {
                    let newHtml = this.unparseHtml();
                    this.props.onChangeHTML(newHtml);
                });
        }
    }

    render() {
        const parsedHtml = this.state.hasError ? <Loader /> : this.state.parsedHtml;
        const interactionContextValue = {
            hoveringBlock: this.state.hoveringBlock,
            onChangeHoveringBlock: this.onChangeHoveringBlock.bind(this),
            editingBlock: this.state.editingBlock,
            mode: this.props.mode,
            copyPasteMode: this.state.copyPasteMode,
            copyPasteBlock: this.state.copyPasteBlock,
            shortcodes: this.state.shortcodes, // Pass shortcodes with depth from state
        };

        if ( 'onboarding' === this.props.mode ) {
            
            return (
                <PreviewInteractionsContext.Provider value={ interactionContextValue }>
                <Fragment>
                    <style>{ Helpers.parseCSS( this.props.template ) }</style>
                    {
                        'recipe' === this.props.template.type
                        &&
                        <div className={`wprm-recipe wprm-recipe-template-${this.props.template.slug}`}>{ parsedHtml }</div>
                    }
                    {
                        'snippet' === this.props.template.type
                        &&
                        <div className={`wprm-recipe wprm-recipe-snippet wprm-recipe-template-${this.props.template.slug}`}>{ parsedHtml }</div>
                    }
                </Fragment>
                </PreviewInteractionsContext.Provider>
            );
        }

        // Show AddBlocksView when in 'add' mode and no block/pattern is selected yet
        const showAddBlocksView = 'add' === this.props.mode && !this.state.addingBlock && !this.state.addingPattern;

        return (
            <PreviewInteractionsContext.Provider value={ interactionContextValue }>
            <Fragment>
                <div className="wprm-main-container">
                    {!showAddBlocksView && (
                        <h2 className="wprm-main-container-name">Preview at <input type="number" min="1" value={ this.state.width } onChange={ (e) => { this.setState({ width: e.target.value } ); } } />px</h2>
                    )}
                    <div className="wprm-main-container-preview">
                        {showAddBlocksView ? (
                            <AddBlocksView
                                onSelectBlock={(blockId) => this.onChangeAddingBlock(blockId)}
                                onSelectPattern={(patternId) => this.onChangeAddingPattern(patternId)}
                                searchQuery={this.state.addBlocksSearchQuery}
                                scrollToCategory={this.state.scrollToCategory}
                                showNewOnly={this.state.showNewBlocksOnly}
                                onToggleShowNewOnly={(value) => this.setState({ showNewBlocksOnly: value })}
                            />
                        ) : (
                            <Fragment>
                                <PreviewRecipe
                                    recipe={ this.state.recipe }
                                    onRecipeChange={ (recipe) => {
                                        if ( recipe !== this.state.recipe ) {
                                            this.setState( {
                                                recipe,
                                                html: '', // Force HTML to update.
                                            });
                                        }
                                    }}
                                />
                                {
                                    this.state.recipe && this.state.recipe.id
                                    ?
                                    <div
                                        className="wprm-main-container-preview-content"
                                        style={{
                                            width: `${this.state.width}px`,
                                        }}
                                    >
                                        <style>{ Helpers.parseCSS( this.props.template ) }</style>
                                        {
                                            'recipe' === this.props.template.type
                                            &&
                                            <Fragment>
                                                <p>This is an example paragraph that could be appearing before the recipe box, just to give some context to this preview. After this paragraph the recipe box will appear.</p>
                                                <div className={`wprm-recipe wprm-recipe-template-${this.props.template.slug}`}>{ parsedHtml }</div>
                                                <p>This is a paragraph appearing after the recipe box.</p>
                                            </Fragment>
                                        }
                                        {
                                            'snippet' === this.props.template.type
                                            &&
                                            <Fragment>
                                                <p>&nbsp;</p>
                                                <div className={`wprm-recipe wprm-recipe-snippet wprm-recipe-template-${this.props.template.slug}`}>{ parsedHtml }</div>
                                                <p>This would be the start of your post content, as the recipe snippets should automatically appear above. We'll be adding some example content below to give you a realistic preview.</p>
                                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. In eleifend vitae nisl et pharetra. Sed euismod nisi convallis arcu lobortis commodo. Mauris nec arcu blandit, ultrices nisi sit amet, scelerisque tortor. Mauris vitae odio sed nisl posuere feugiat eu sit amet nunc. Vivamus varius rutrum tortor, ut viverra mi. Pellentesque sed justo eget lectus eleifend consectetur. Curabitur hendrerit purus velit, ut auctor orci fringilla sed. Phasellus commodo luctus nulla, et rutrum risus lobortis in. Aenean ullamcorper, magna congue viverra consequat, libero elit blandit magna, in ultricies quam risus et magna. Aenean viverra lorem leo, eget laoreet quam suscipit viverra. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Quisque sodales dolor mauris. Ut sed tempus erat. Nulla metus diam, luctus ac erat bibendum, placerat maximus nisi. Nullam hendrerit eleifend lobortis.</p>
                                                <p>Proin tempus hendrerit orci, tincidunt bibendum justo tincidunt vel. Morbi porttitor finibus magna non imperdiet. Fusce sollicitudin ex auctor interdum ultricies. Proin efficitur eleifend lacus, dapibus eleifend nibh tempus at. Pellentesque feugiat imperdiet turpis, sed consequat diam tincidunt a. Mauris mollis justo nec tellus aliquam, efficitur scelerisque nunc semper. Morbi rhoncus ultricies congue. Sed semper aliquet interdum.</p>
                                                <p>Nam ultricies, tellus nec vulputate varius, ligula ipsum viverra libero, lacinia ultrices sapien erat id mi. Duis vel dignissim lectus. Aliquam vehicula finibus tortor, cursus fringilla leo sodales ut. Vestibulum nec erat pretium, finibus odio et, porta lorem. Nunc in mi lobortis, aliquet sem sollicitudin, accumsan mi. Nam pretium nibh nunc, vel varius ex sagittis at. Vestibulum ac turpis vitae dui congue iaculis et non massa. Duis sed gravida nunc. Vivamus blandit dapibus orci, eu maximus velit faucibus eu.</p>
                                                <div id={ `wprm-recipe-container-${this.state.recipe.id}` } className="wprm-preview-snippet-recipe-box">
                                                    <p>This is an example recipe box.</p>
                                                    <p id={ `wprm-recipe-video-container-${this.state.recipe.id}` }>It includes an example video.</p>
                                                </div>
                                                <p>Some more random content could be appearing after the recipe box. Morbi dignissim euismod vestibulum. Interdum et malesuada fames ac ante ipsum primis in faucibus. Vestibulum eu faucibus lectus. Donec sit amet mattis erat, at vulputate elit. Morbi ullamcorper, justo nec porttitor porta, dui lectus euismod est, convallis tempor lorem elit nec leo. Praesent hendrerit auctor risus sed mollis. Integer suscipit arcu at risus efficitur, et interdum arcu fringilla. Aliquam mollis accumsan blandit. Nam vestibulum urna id velit scelerisque, eu commodo urna imperdiet. Mauris sed risus libero. Integer lacinia nec lectus in posuere. Sed feugiat dolor eros, ac scelerisque tellus hendrerit sit amet. Sed nisl lacus, condimentum id orci eu, malesuada mattis sem. Quisque ipsum velit, viverra et magna a, laoreet porta lorem. Praesent porttitor lorem quis quam lobortis, lacinia tincidunt odio sodales.</p>
                                            </Fragment>
                                        }
                                        {
                                            'roundup' === this.props.template.type
                                            &&
                                            <Fragment>
                                                <h2>Our first recipe</h2>
                                                <p>This is the first example recipe in this recipe roundup. We can have as much information and images as we want here and then end with the roundup template for this particular recipe.</p>
                                                <div className={`wprm-recipe wprm-recipe-roundup-item wprm-recipe-template-${this.props.template.slug}`}>{ parsedHtml }</div>
                                                <h2>Our second recipe</h2>
                                                <p>A roundup would have multiple recipes, so here is another one with some more demo text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. In eleifend vitae nisl et pharetra. Sed euismod nisi convallis arcu lobortis commodo.</p>
                                                <p>...</p>
                                            </Fragment>
                                        }
                                        {
                                            'shortcode' === this.props.template.type
                                            &&
                                            <Fragment>
                                                <p>&nbsp;</p>
                                                <div className={`wprm-recipe wprm-recipe-template-${this.props.template.slug}`}>{ parsedHtml }</div>
                                            </Fragment>
                                        }
                                    </div>
                                    :
                                    <p style={{color: 'darkred', textAlign: 'center'}}>You have to select a recipe to preview the template. Use the dropdown above or set a default recipe to use for the preview on the settings page.</p>
                                }
                            </Fragment>
                        )}
                    </div>
                </div>
                {
                    false === this.state.editingBlock || this.state.shortcodes.length <= this.state.editingBlock
                    ?
                    <BlockProperties>
                        {
                            'blocks' === this.props.mode
                            &&
                            <p>Select block to edit by clicking on it in the template or the list below:</p>
                        }
                        <SortableBlockList
                            shortcodes={this.state.shortcodes}
                            hoveringBlock={this.state.hoveringBlock}
                            onChangeHoveringBlock={this.onChangeHoveringBlock.bind(this)}
                            onChangeEditingBlock={this.onChangeEditingBlock.bind(this)}
                            onDuplicateBlock={this.onDuplicateBlock.bind(this)}
                            onRemoveBlock={this.onRemoveBlock.bind(this)}
                            onChangeMovingBlock={this.onChangeMovingBlock.bind(this)}
                            onMoveBlock={this.onMoveBlock.bind(this)}
                        />
                        {
                             ! this.state.shortcodes.length && <p>There are no adjustable blocks.</p>
                        }
                    </BlockProperties>
                    :
                    null
                }
                <AddPatterns>
                {
                    ! this.state.addingPattern
                    ?
                    <Fragment>
                        <p>Select pattern to add:</p>
                        {
                            Object.keys( Patterns.patterns ).map( ( id, i ) => (
                                <div
                                    key={i}
                                    className="wprm-template-menu-block"
                                    onClick={ () => this.onChangeAddingPattern(id) }
                                >{ Patterns.patterns[ id ].name }</div>
                            ) )
                        }
                    </Fragment>
                    :
                    <Fragment>
                        <a href="#" onClick={(e) => {
                            e.preventDefault();
                            this.onChangeAddingPattern(false);
                        }}>Cancel</a>
                        <p>Where would you like to add "{ Patterns.patterns[ this.state.addingPattern ].name }"?</p>
                        {
                            // Only show "Start of template" if there are no blocks
                            this.state.shortcodes.length === 0 && (
                                <div
                                    className="wprm-template-menu-block wprm-template-menu-block-insert"
                                    onClick={ () => this.onAddPattern( 'start' ) }
                                    onMouseEnter={ () => this.onChangeHoveringBlock('start') }
                                    onMouseLeave={ () => this.onChangeHoveringBlock(false) }
                                >
                                    <span className="wprm-template-menu-block-name">Start of template</span>
                                    <div className="wprm-template-menu-block-actions">
                                        <TemplateIcon
                                            type="arrow-down"
                                            title="Add at start"
                                            onClick={ (e) => {
                                                e.stopPropagation();
                                                this.onAddPattern( 'start' );
                                            }}
                                        />
                                    </div>
                                </div>
                            )
                        }
                        {
                            (() => {
                                // Build tree structure from flat shortcodes array
                                const buildTree = (shortcodes) => {
                                    const tree = [];
                                    const stack = [{ node: { children: tree }, depth: -1 }];
                                    
                                    shortcodes.forEach((shortcode, i) => {
                                        if (!shortcode || typeof shortcode !== 'object') return;
                                        
                                        const depth = shortcode.depth !== undefined ? shortcode.depth : 0;
                                        const node = { ...shortcode, children: [] };
                                        
                                        // Pop stack until we find the right parent
                                        while (stack.length > 1 && stack[stack.length - 1].depth >= depth) {
                                            stack.pop();
                                        }
                                        
                                        // Add to parent's children
                                        stack[stack.length - 1].node.children.push(node);
                                        
                                        // If this can have children, push it to stack
                                        if (canHaveChildren(shortcode)) {
                                            stack.push({ node, depth });
                                        }
                                    });
                                    
                                    return tree;
                                };
                                
                                // Render a node recursively
                                const renderNode = (node, depth = 0, index = 0, parentUid = null) => {
                                    const indentStyle = {
                                        paddingLeft: `${depth * 15}px`
                                    };
                                    
                                    const isContainer = canHaveChildren(node);
                                    const hasChildren = node.children && node.children.length > 0;
                                    
                                    const result = [];
                                    
                                    // Render the node itself
                                    if (isContainer) {
                                        result.push(
                                            <div
                                                key={`container-${node.uid}`}
                                                className={ node.uid === this.state.hoveringBlock ? 'wprm-template-menu-block wprm-template-menu-block-container wprm-template-menu-block-hover' : 'wprm-template-menu-block wprm-template-menu-block-container' }
                                                style={indentStyle}
                                                onMouseEnter={ () => this.onChangeHoveringBlock(node.uid) }
                                                onMouseLeave={ () => this.onChangeHoveringBlock(false) }
                                            >
                                                <DropdownMenu
                                                    items={[
                                                        {
                                                            label: 'Add before this block',
                                                            onClick: (e) => {
                                                                e.stopPropagation();
                                                                this.onAddPattern( node.uid, 'before' );
                                                            }
                                                        },
                                                        {
                                                            label: 'Add inside',
                                                            onClick: (e) => {
                                                                e.stopPropagation();
                                                                this.onAddPattern( node.uid, hasChildren ? 'inside-end' : 'inside-start' );
                                                            }
                                                        },
                                                        {
                                                            label: 'Add after this block',
                                                            onClick: (e) => {
                                                                e.stopPropagation();
                                                                this.onAddPattern( node.uid, 'after' );
                                                            }
                                                        }
                                                    ]}
                                                    placement="top"
                                                >
                                                    <span className="wprm-template-menu-block-name wprm-template-menu-block-name-clickable">
                                                        { node.name }
                                                    </span>
                                                </DropdownMenu>
                                                <div className="wprm-template-menu-block-actions">
                                                    <TemplateIcon
                                                        type="arrow-up"
                                                        title="Add before this block"
                                                        onClick={ (e) => {
                                                            e.stopPropagation();
                                                            this.onAddPattern( node.uid, 'before' );
                                                        }}
                                                    />
                                                    {!hasChildren && (
                                                        <span
                                                            className="wprm-template-menu-block-placeholder"
                                                            onClick={ (e) => {
                                                                e.stopPropagation();
                                                                this.onAddPattern( node.uid, 'inside-start' );
                                                            }}
                                                            title="Add inside this container"
                                                        >
                                                            Add inside
                                                        </span>
                                                    )}
                                                    <TemplateIcon
                                                        type="arrow-down"
                                                        title="Add after this block"
                                                        onClick={ (e) => {
                                                            e.stopPropagation();
                                                            this.onAddPattern( node.uid, 'after' );
                                                        }}
                                                    />
                                                </div>
                                            </div>
                                        );
                                        
                                        // Render children if they exist
                                        if (hasChildren) {
                                            node.children.forEach((child, childIndex) => {
                                                result.push(...renderNode(child, depth + 1, childIndex, node.uid));
                                            });
                                        }
                                    } else {
                                        // Regular shortcode (not a container)
                                        result.push(
                                            <div
                                                key={`block-${node.uid}`}
                                                className={ node.uid === this.state.hoveringBlock ? 'wprm-template-menu-block wprm-template-menu-block-insert wprm-template-menu-block-hover' : 'wprm-template-menu-block wprm-template-menu-block-insert' }
                                                style={indentStyle}
                                                onMouseEnter={ () => this.onChangeHoveringBlock(node.uid) }
                                                onMouseLeave={ () => this.onChangeHoveringBlock(false) }
                                            >
                                                <DropdownMenu
                                                    items={[
                                                        {
                                                            label: 'Add before this block',
                                                            onClick: (e) => {
                                                                e.stopPropagation();
                                                                this.onAddPattern( node.uid, 'before' );
                                                            }
                                                        },
                                                        {
                                                            label: 'Add after this block',
                                                            onClick: (e) => {
                                                                e.stopPropagation();
                                                                this.onAddPattern( node.uid, 'after' );
                                                            }
                                                        }
                                                    ]}
                                                    placement="top"
                                                >
                                                    <span className="wprm-template-menu-block-name wprm-template-menu-block-name-clickable">
                                                        { node.name }
                                                    </span>
                                                </DropdownMenu>
                                                <div className="wprm-template-menu-block-actions">
                                                    <TemplateIcon
                                                        type="arrow-up"
                                                        title="Add before this block"
                                                        onClick={ (e) => {
                                                            e.stopPropagation();
                                                            this.onAddPattern( node.uid, 'before' );
                                                        }}
                                                    />
                                                    <TemplateIcon
                                                        type="arrow-down"
                                                        title="Add after this block"
                                                        onClick={ (e) => {
                                                            e.stopPropagation();
                                                            this.onAddPattern( node.uid, 'after' );
                                                        }}
                                                    />
                                                </div>
                                            </div>
                                        );
                                    }
                                    
                                    return result;
                                };
                                
                                // Get shortcodes with depth already calculated (from state)
                                const shortcodesWithDepth = this.state.shortcodes.map(shortcode => ({
                                    ...shortcode,
                                    depth: shortcode.depth !== undefined ? shortcode.depth : 0
                                }));
                                
                                // Build tree and render
                                const tree = buildTree(shortcodesWithDepth);
                                const renderedNodes = [];
                                tree.forEach((node, index) => {
                                    renderedNodes.push(...renderNode(node, 0, index));
                                });
                                
                                return renderedNodes;
                            })()
                        }
                    </Fragment>
                }
                </AddPatterns>
                <AddBlocks>
                {
                    // Show search UI when in 'add' mode but no block/pattern selected yet
                    'add' === this.props.mode && !this.state.addingBlock && !this.state.addingPattern
                    ?
                    <Fragment>
                        <div className="wprm-add-blocks-search">
                            <input
                                type="text"
                                placeholder="Search blocks..."
                                value={this.state.addBlocksSearchQuery}
                                onChange={(e) => this.setState({ addBlocksSearchQuery: e.target.value })}
                                className="wprm-add-blocks-search-input"
                            />
                            {this.state.addBlocksSearchQuery && (
                                <button
                                    className="wprm-add-blocks-search-clear"
                                    onClick={() => this.setState({ addBlocksSearchQuery: '' })}
                                    title="Clear search"
                                >
                                    <Icon type="close" />
                                </button>
                            )}
                        </div>
                        <div className="wprm-add-blocks-filter">
                            <label className="wprm-add-blocks-filter-checkbox">
                                <input
                                    type="checkbox"
                                    checked={this.state.showNewBlocksOnly}
                                    onChange={(e) => this.setState({ showNewBlocksOnly: e.target.checked })}
                                />
                                <span>Show new blocks only</span>
                            </label>
                        </div>
                        {!this.state.addBlocksSearchQuery && (
                            <div className="wprm-add-blocks-categories">
                                <div className="wprm-add-blocks-categories-title">Jump to Category</div>
                                {['patterns', ...Object.keys(shortcodeGroups)].map((groupKey) => {
                                    let group;
                                    if ( 'patterns' === groupKey ) {
                                        group = {
                                            group: 'Patterns',
                                        };
                                    } else {
                                        group = shortcodeGroups[groupKey];
                                    }
                                    return (
                                        <a
                                            key={groupKey}
                                            href="#"
                                            className="wprm-add-blocks-category-link"
                                            onClick={(e) => {
                                                e.preventDefault();
                                                this.setState({ scrollToCategory: groupKey }, () => {
                                                    // Reset after scroll animation completes
                                                    setTimeout(() => {
                                                        this.setState({ scrollToCategory: null });
                                                    }, 1000);
                                                });
                                            }}
                                        >
                                            {group.group}
                                        </a>
                                    );
                                })}
                            </div>
                        )}
                    </Fragment>
                    :
                    // Show placement selection when a block or pattern has been selected
                    (this.state.addingBlock || this.state.addingPattern)
                    &&
                    <Fragment>
                        <a href="#" onClick={(e) => {
                            e.preventDefault();
                            if (this.state.addingBlock) {
                                this.onChangeAddingBlock(false);
                            }
                            if (this.state.addingPattern) {
                                this.onChangeAddingPattern(false);
                            }
                        }}>Cancel</a>
                        <p>Where would you like to add "{ this.state.addingPattern ? Patterns.patterns[this.state.addingPattern].name : Helpers.getShortcodeName(this.state.addingBlock) }"?</p>
                        {
                            // Only show "Start of template" if there are no blocks
                            this.state.shortcodes.length === 0 && (
                                <div
                                    className="wprm-template-menu-block wprm-template-menu-block-insert"
                                    onClick={ () => {
                                        if (this.state.addingBlock) {
                                            this.onAddBlock( 'start' );
                                        } else if (this.state.addingPattern) {
                                            this.onAddPattern( 'start' );
                                        }
                                    }}
                                    onMouseEnter={ () => this.onChangeHoveringBlock('start') }
                                    onMouseLeave={ () => this.onChangeHoveringBlock(false) }
                                >
                                    <span className="wprm-template-menu-block-name">Start of template</span>
                                    <div className="wprm-template-menu-block-actions">
                                        <TemplateIcon
                                            type="arrow-down"
                                            title="Add at start"
                                            onClick={ (e) => {
                                                e.stopPropagation();
                                                if (this.state.addingBlock) {
                                                    this.onAddBlock( 'start' );
                                                } else if (this.state.addingPattern) {
                                                    this.onAddPattern( 'start' );
                                                }
                                            }}
                                        />
                                    </div>
                                </div>
                            )
                        }
                        {
                            (() => {
                                // Build tree structure from flat shortcodes array
                                const buildTree = (shortcodes) => {
                                    const tree = [];
                                    const stack = [{ node: { children: tree }, depth: -1 }];
                                    
                                    shortcodes.forEach((shortcode, i) => {
                                        if (!shortcode || typeof shortcode !== 'object') return;
                                        
                                        const depth = shortcode.depth !== undefined ? shortcode.depth : 0;
                                        const node = { ...shortcode, children: [] };
                                        
                                        // Pop stack until we find the right parent
                                        while (stack.length > 1 && stack[stack.length - 1].depth >= depth) {
                                            stack.pop();
                                        }
                                        
                                        // Add to parent's children
                                        stack[stack.length - 1].node.children.push(node);
                                        
                                        // If this can have children, push it to stack
                                        if (canHaveChildren(shortcode)) {
                                            stack.push({ node, depth });
                                        }
                                    });
                                    
                                    return tree;
                                };
                                
                                // Render a node recursively
                                const renderNode = (node, depth = 0, index = 0, parentUid = null) => {
                                    const indentStyle = {
                                        paddingLeft: `${depth * 15}px`
                                    };
                                    
                                    const isContainer = canHaveChildren(node);
                                    const hasChildren = node.children && node.children.length > 0;
                                    
                                    const result = [];
                                    
                                    // Render the node itself
                                    if (isContainer) {
                                        result.push(
                                            <div
                                                key={`container-${node.uid}`}
                                                className={ node.uid === this.state.hoveringBlock ? 'wprm-template-menu-block wprm-template-menu-block-container wprm-template-menu-block-hover' : 'wprm-template-menu-block wprm-template-menu-block-container' }
                                                style={indentStyle}
                                                onMouseEnter={ () => this.onChangeHoveringBlock(node.uid) }
                                                onMouseLeave={ () => this.onChangeHoveringBlock(false) }
                                            >
                                                <DropdownMenu
                                                    items={[
                                                        {
                                                            label: 'Add before this block',
                                                            onClick: (e) => {
                                                                e.stopPropagation();
                                                                if (this.state.addingBlock) {
                                                                    this.onAddBlock( node.uid, 'before' );
                                                                } else if (this.state.addingPattern) {
                                                                    this.onAddPattern( node.uid, 'before' );
                                                                }
                                                            }
                                                        },
                                                        {
                                                            label: 'Add inside',
                                                            onClick: (e) => {
                                                                e.stopPropagation();
                                                                if (this.state.addingBlock) {
                                                                    this.onAddBlock( node.uid, hasChildren ? 'inside-end' : 'inside-start' );
                                                                } else if (this.state.addingPattern) {
                                                                    this.onAddPattern( node.uid, hasChildren ? 'inside-end' : 'inside-start' );
                                                                }
                                                            }
                                                        },
                                                        {
                                                            label: 'Add after this block',
                                                            onClick: (e) => {
                                                                e.stopPropagation();
                                                                if (this.state.addingBlock) {
                                                                    this.onAddBlock( node.uid, 'after' );
                                                                } else if (this.state.addingPattern) {
                                                                    this.onAddPattern( node.uid, 'after' );
                                                                }
                                                            }
                                                        }
                                                    ]}
                                                    placement="top"
                                                >
                                                    <span className="wprm-template-menu-block-name wprm-template-menu-block-name-clickable">
                                                        { node.name }
                                                    </span>
                                                </DropdownMenu>
                                                <div className="wprm-template-menu-block-actions">
                                                    <TemplateIcon
                                                        type="arrow-up"
                                                        title="Add before this block"
                                                        onClick={ (e) => {
                                                            e.stopPropagation();
                                                            if (this.state.addingBlock) {
                                                                this.onAddBlock( node.uid, 'before' );
                                                            } else if (this.state.addingPattern) {
                                                                this.onAddPattern( node.uid, 'before' );
                                                            }
                                                        }}
                                                    />
                                                    {!hasChildren && (
                                                        <span
                                                            className="wprm-template-menu-block-placeholder"
                                                            onClick={ (e) => {
                                                                e.stopPropagation();
                                                                if (this.state.addingBlock) {
                                                                    this.onAddBlock( node.uid, 'inside-start' );
                                                                } else if (this.state.addingPattern) {
                                                                    this.onAddPattern( node.uid, 'inside-start' );
                                                                }
                                                            }}
                                                            title="Add inside this container"
                                                        >
                                                            Add inside
                                                        </span>
                                                    )}
                                                    <TemplateIcon
                                                        type="arrow-down"
                                                        title="Add after this block"
                                                        onClick={ (e) => {
                                                            e.stopPropagation();
                                                            if (this.state.addingBlock) {
                                                                this.onAddBlock( node.uid, 'after' );
                                                            } else if (this.state.addingPattern) {
                                                                this.onAddPattern( node.uid, 'after' );
                                                            }
                                                        }}
                                                    />
                                                </div>
                                            </div>
                                        );
                                        
                                        // Render children if they exist
                                        if (hasChildren) {
                                            node.children.forEach((child, childIndex) => {
                                                result.push(...renderNode(child, depth + 1, childIndex, node.uid));
                                            });
                                        }
                                    } else {
                                        // Regular shortcode (not a container)
                                        result.push(
                                            <div
                                                key={`block-${node.uid}`}
                                                className={ node.uid === this.state.hoveringBlock ? 'wprm-template-menu-block wprm-template-menu-block-insert wprm-template-menu-block-hover' : 'wprm-template-menu-block wprm-template-menu-block-insert' }
                                                style={indentStyle}
                                                onMouseEnter={ () => this.onChangeHoveringBlock(node.uid) }
                                                onMouseLeave={ () => this.onChangeHoveringBlock(false) }
                                            >
                                                <DropdownMenu
                                                    items={[
                                                        {
                                                            label: 'Add before this block',
                                                            onClick: (e) => {
                                                                e.stopPropagation();
                                                                if (this.state.addingBlock) {
                                                                    this.onAddBlock( node.uid, 'before' );
                                                                } else if (this.state.addingPattern) {
                                                                    this.onAddPattern( node.uid, 'before' );
                                                                }
                                                            }
                                                        },
                                                        {
                                                            label: 'Add after this block',
                                                            onClick: (e) => {
                                                                e.stopPropagation();
                                                                if (this.state.addingBlock) {
                                                                    this.onAddBlock( node.uid, 'after' );
                                                                } else if (this.state.addingPattern) {
                                                                    this.onAddPattern( node.uid, 'after' );
                                                                }
                                                            }
                                                        }
                                                    ]}
                                                    placement="top"
                                                >
                                                    <span className="wprm-template-menu-block-name wprm-template-menu-block-name-clickable">
                                                        { node.name }
                                                    </span>
                                                </DropdownMenu>
                                                <div className="wprm-template-menu-block-actions">
                                                    <TemplateIcon
                                                        type="arrow-up"
                                                        title="Add before this block"
                                                        onClick={ (e) => {
                                                            e.stopPropagation();
                                                            if (this.state.addingBlock) {
                                                                this.onAddBlock( node.uid, 'before' );
                                                            } else if (this.state.addingPattern) {
                                                                this.onAddPattern( node.uid, 'before' );
                                                            }
                                                        }}
                                                    />
                                                    <TemplateIcon
                                                        type="arrow-down"
                                                        title="Add after this block"
                                                        onClick={ (e) => {
                                                            e.stopPropagation();
                                                            if (this.state.addingBlock) {
                                                                this.onAddBlock( node.uid, 'after' );
                                                            } else if (this.state.addingPattern) {
                                                                this.onAddPattern( node.uid, 'after' );
                                                            }
                                                        }}
                                                    />
                                                </div>
                                            </div>
                                        );
                                    }
                                    
                                    return result;
                                };
                                
                                // Get shortcodes with depth already calculated (from state)
                                const shortcodesWithDepth = this.state.shortcodes.map(shortcode => ({
                                    ...shortcode,
                                    depth: shortcode.depth !== undefined ? shortcode.depth : 0
                                }));
                                
                                // Build tree and render
                                const tree = buildTree(shortcodesWithDepth);
                                const renderedNodes = [];
                                tree.forEach((node, index) => {
                                    renderedNodes.push(...renderNode(node, 0, index));
                                });
                                
                                return renderedNodes;
                            })()
                        }
                    </Fragment>
                }
                </AddBlocks>
            </Fragment>
            </PreviewInteractionsContext.Provider>
        );
    }
}
