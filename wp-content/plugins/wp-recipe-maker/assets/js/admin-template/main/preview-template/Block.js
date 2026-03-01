import React, { Component, Fragment } from 'react';
import Parser from 'html-react-parser';
import domToReact from 'html-react-parser/lib/dom-to-react';

import Api from 'Shared/Api';
import Loader from 'Shared/Loader';
import Helpers from '../../general/Helpers';
import BlockProperties from '../../menu/BlockProperties';
import PropertyAccordion from '../../menu/PropertyAccordion';
import PreviewInteractionsContext from './PreviewInteractionsContext';
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

const preferContextValue = (contextValue, propValue) => typeof contextValue !== 'undefined' ? contextValue : propValue;

/**
 * Calculate depth for shortcodes array based on parent/child relationships
 * This uses a simple heuristic: items that can have children create a new depth level,
 * and subsequent items are assumed to be children until we encounter an item that
 * can't be a child or we've processed all items.
 * 
 * Note: This is an approximation. For accurate depth, shortcodes should already have
 * the depth property calculated from the HTML structure.
 */
const calculateShortcodeDepths = (shortcodes) => {
    if (!shortcodes || shortcodes.length === 0) return shortcodes;
    
    // If shortcodes already have depth, return as is
    if (shortcodes[0] && shortcodes[0].depth !== undefined) {
        return shortcodes;
    }
    
    // Calculate depth based on structure using a stack
    const depthMap = {};
    const stack = []; // Stack of items that can have children
    
    for (let i = 0; i < shortcodes.length; i++) {
        const shortcode = shortcodes[i];
        if (!shortcode || typeof shortcode !== 'object') continue;
        
        // Pop items from stack that can no longer have this as a child
        // This happens when we encounter an item that would be a sibling or parent
        while (stack.length > 0) {
            const lastParent = stack[stack.length - 1];
            // If the last parent can't have children, remove it
            if (!canHaveChildren(lastParent)) {
                stack.pop();
            } else {
                // Keep the parent in the stack - this item might be its child
                break;
            }
        }
        
        // Current depth is the number of parents in the stack
        const depth = stack.length;
        depthMap[shortcode.uid] = depth;
        
        // If this item can have children, add it to the stack for potential children
        if (canHaveChildren(shortcode)) {
            stack.push(shortcode);
        }
    }
    
    // Add depth to each shortcode
    return shortcodes.map(shortcode => {
        if (!shortcode || typeof shortcode !== 'object') return shortcode;
        return {
            ...shortcode,
            depth: depthMap[shortcode.uid] !== undefined ? depthMap[shortcode.uid] : 0
        };
    });
};

export default class Block extends Component {
    static contextType = PreviewInteractionsContext;

    constructor(props) {
        super(props);

        const blockMode = props.hasOwnProperty('mode') && props.mode ? props.mode : 'edit';

        this.state = {
            fullShortcode: '',
            html: '',
            loading: false,
            blockMode,
        }
    }

    componentDidMount() {
        this.checkShortcodeChange();
    }

    componentDidUpdate(prevProps) {
        this.checkShortcodeChange();

        // Check for preview recipe change.
        if ( prevProps.recipeId !== this.props.recipeId ) {
            this.updatePreview();
        }

        // Make sure we start out in edit mode unless we're in shortcode generator.
        if  ( 'shortcode-generator' !== this.state.blockMode && prevProps.editingBlock !== this.props.editingBlock ) {
            // If this block is no longer being edited, reset to edit mode.
            if ( this.props.shortcode.uid !== this.props.editingBlock ) {
                this.onChangeBlockMode('edit');
            }
        }
        
        // Reset copy/paste mode if copy/paste mode was cleared in parent.
        if ( prevProps.copyPasteMode && ! this.props.copyPasteMode ) {
            // If this block was in copy/paste mode, reset to edit mode.
            if ( 'copy' === this.state.blockMode || 'paste' === this.state.blockMode ) {
                this.onChangeBlockMode('edit');
            }
        }
        
        // Reset copy/paste mode if this block is no longer being edited.
        if ( prevProps.editingBlock === this.props.shortcode.uid && this.props.editingBlock !== this.props.shortcode.uid ) {
            this.onChangeBlockMode('edit');
        }
        
        // Reset copy/paste mode if this block is no longer being edited.
        if ( prevProps.editingBlock === this.props.shortcode.uid && this.props.editingBlock !== this.props.shortcode.uid ) {
            this.onChangeBlockMode('edit');
        }
    }

    checkShortcodeChange() {
        const fullShortcode = Helpers.getFullShortcode( this.props.shortcode, true );

        if ( fullShortcode !== this.state.fullShortcode ) {
            this.setState({
                fullShortcode
            }, this.updatePreview);
        }
    }

    updatePreview() {
        this.setState({
            loading: true,
        });

        Api.template.previewShortcode( this.props.shortcode.uid, this.state.fullShortcode, this.props.recipeId, this.props.previewContext )
            .then((data) => {
                this.setState({
                    html: data.hasOwnProperty( this.props.shortcode.uid ) ? data[ this.props.shortcode.uid ] : '',
                    loading: false,
                });
            });
    }

    getBlockProperties(shortcode = this.props.shortcode) {
        let properties = {};
        const structure = wprm_admin_template.shortcodes.hasOwnProperty(shortcode.id) ? wprm_admin_template.shortcodes[shortcode.id] : false;

        if (structure) {
            Object.entries(structure).forEach(([id, options]) => {
                if ( options.type ) {
                    let name = options.name ? options.name : id.replace(/_/g, ' ').toLowerCase().replace(/\b[a-z]/g, function(letter) {
                        return letter.toUpperCase();
                    });

                    let value = shortcode.attributes.hasOwnProperty(id) ? shortcode.attributes[id] : options.default;

                    // Revert HTML entity change.
                    value = value.replace(/&quot;/gm, '"');
                    value = value.replace(/&#93;/gm, ']');

                    properties[id] = {
                        ...options,
                        id,
                        name,
                        value,
                    };
                }
            });
        }

        return properties;
    }

    onChangeBlockMode(blockMode) {
        if ( blockMode !== this.state.blockMode ) {
            this.setState({
                blockMode
            });
            
            // Notify parent when entering/exiting copy/paste mode.
            if ( this.props.onChangeCopyPasteMode ) {
                if ( 'copy' === blockMode || 'paste' === blockMode ) {
                    this.props.onChangeCopyPasteMode( blockMode, this.props.shortcode.uid );
                } else {
                    this.props.onChangeCopyPasteMode( false, false );
                }
            }
        }
    }

    onCopyPasteStyle(from, to) {
        const fromProperties = this.getBlockProperties(this.props.shortcodes[from]);
        const toProperties = this.getBlockProperties(this.props.shortcodes[to]);

        let changedProperties = {};

        Object.entries(toProperties).forEach(([property, options]) => {    
            if (
                fromProperties.hasOwnProperty(property)
                && fromProperties[property].value !== options.value
                // Exclude some properties.
                && 'icon' !== property
                && 'text' !== property
                && 'label' !== property
                && 'header' !== property
                // Make sure type matches and dropdown actual has this option.
                && fromProperties[property].type === options.type
                && ( 'dropdown' !== options.type || options.options.hasOwnProperty( fromProperties[property].value ) ) // Make sure dropdown option exists.
            ) {
                changedProperties[property] = fromProperties[property].value;
            }
        });

        if ( Object.keys(changedProperties).length ) {
            this.props.onBlockPropertiesChange(to, changedProperties);
        }
        
        // Don't automatically exit copy/paste mode - user must explicitly stop it.
    }

    render() {
        const properties = this.getBlockProperties();
        const interactions = this.context || {};
        const templateMode = preferContextValue(interactions.mode, this.props.templateMode);
        const hoveringBlock = preferContextValue(interactions.hoveringBlock, this.props.hoveringBlock);
        const onChangeHoveringBlock = interactions.onChangeHoveringBlock || this.props.onChangeHoveringBlock;
        const editingBlock = preferContextValue(interactions.editingBlock, this.props.editingBlock);
        const copyPasteMode = preferContextValue(interactions.copyPasteMode, this.props.copyPasteMode);
        const copyPasteBlock = preferContextValue(interactions.copyPasteBlock, this.props.copyPasteBlock);
        // Use shortcodes from context (state) if available, as they have depth calculated
        const shortcodesFromContext = interactions.shortcodes;
        
        // Only enable hover/click in specific modes.
        // Use templateMode prop for template editor interactivity (separate from Block's internal mode).
        const interactiveModes = [ 'blocks', 'add' ];
        const isInteractiveMode = templateMode && interactiveModes.includes( templateMode );
        
        // Check if we're in copy/paste mode (another block is being copied/pasted from/to).
        const isInCopyPasteMode = copyPasteMode && copyPasteBlock !== this.props.shortcode.uid;
        
        // For 'blocks' mode, allow interaction even when editing (to switch blocks).
        // For 'add' mode, only allow hover (not click) - clicks are handled by the AddBlocks component.
        // For 'remove' and 'move' modes, only allow when not editing a block.
        // Also allow interaction when in copy/paste mode (but handle it separately).
        const canInteractForRegularModes = isInteractiveMode && ( 'blocks' === templateMode || false === editingBlock );
        const canInteract = canInteractForRegularModes || isInCopyPasteMode;
        // In 'add' mode, only enable hover, not click
        const canHover = canInteract || ( 'add' === templateMode && isInteractiveMode );
        
        // Check if click is inside the preview container
        const isClickInPreviewContainer = (e) => {
            const target = e.target;
            const previewContainer = target.closest('.wprm-main-container-preview-content');
            return previewContainer !== null;
        };

        // Handle click based on mode.
        const handleClick = (e) => {
            // Only handle clicks inside the preview container
            if ( ! isClickInPreviewContainer(e) ) {
                return;
            }

            // Handle copy/paste mode clicks FIRST, before anything else.
            if ( isInCopyPasteMode && copyPasteMode ) {
                // Always prevent default in copy/paste mode to stop link navigation and other default behaviors.
                e.preventDefault();
                
                // Trigger copy/paste action.
                const from = 'copy' === copyPasteMode ? copyPasteBlock : this.props.shortcode.uid;
                const to = 'copy' === copyPasteMode ? this.props.shortcode.uid : copyPasteBlock;
                this.onCopyPasteStyle(from, to);
                return;
            }
            
            if ( ! canInteractForRegularModes ) {
                return;
            }

            // Always prevent default in interactive modes to stop link navigation and other default behaviors.
            e.preventDefault();

            if ( 'blocks' === templateMode ) {
                this.props.onChangeEditingBlock( this.props.shortcode.uid );
            }
        };

        return (
            <Fragment>
                {
                    this.state.loading
                    ?
                    <Loader/>
                    :
                    <Fragment>
                        <div
                            className="wprm-template-block-wrapper"
                            data-wprm-uid={ this.props.shortcode.uid }
                            onMouseEnter={ canHover && onChangeHoveringBlock ? (e) => { e.stopPropagation(); onChangeHoveringBlock( this.props.shortcode.uid ); } : undefined }
                            onMouseLeave={ canHover && onChangeHoveringBlock ? (e) => { e.stopPropagation(); onChangeHoveringBlock( false ); } : undefined }
                            onClick={ canInteract ? (e) => { 
                                // Stop propagation immediately to prevent parent handlers
                                e.stopPropagation();
                                
                                // Call handleClick which handles preventDefault appropriately
                                handleClick(e);
                            } : undefined }
                            style={ isInCopyPasteMode ? { cursor: 'pointer' } : undefined }
                        >
                            { Parser(this.state.html.trim(), {
                                replace: function(domNode) {
                                    // Remove lowercase event handlers before processing
                                    removeLowercaseEventHandlers(domNode);
                                    
                                    if ( ! domNode.parent && this.props.shortcode.uid === hoveringBlock ) {
                                        if ( ! domNode.attribs ) {
                                            domNode.attribs = {};
                                        }
                                        domNode.attribs.class = domNode.attribs.class ? domNode.attribs.class + ' wprm-template-block-hovering' : 'wprm-template-block-hovering';
                                        return domToReact(domNode);
                                    }
                                    
                                    // Could be other shortcodes inside this block.
                                    if ( domNode.name == 'wprm-replace-shortcode-with-block' ) {
                                        return this.props.replaceDomNodeWithBlock( domNode, this.props.shortcodes, this.props.recipeId, this.props.parseOptions );
                                    }
                                    if ( domNode.name == 'div' && domNode.attribs.class && 'wprm-layout-' === domNode.attribs.class.substring( 0, 12 ) ) {
                                        return this.props.replaceDomNodeWithElement( domNode, this.props.shortcodes, this.props.recipeId, this.props.parseOptions );
                                    }
                                }.bind(this)
                            }) }
                        </div>
                    </Fragment>
                }
                {
                    this.props.shortcode.uid === editingBlock
                    ?
                    <BlockProperties>
                        {
                            'edit' === this.state.blockMode
                            &&
                            <Fragment>
                                <div className="wprm-template-menu-block-details"><a href="#" onClick={ (e) => { e.preventDefault(); return this.props.onChangeEditingBlock(false); }}>Blocks</a> &gt; { this.props.shortcode.name }</div>
                                <div className="wprm-template-menu-block-quick-edit">
                                    <a href="#" onClick={(e) => {
                                        e.preventDefault();
                                        this.onChangeBlockMode('copy');
                                    }}>Copy styles to...</a> | <a href="#" onClick={(e) => {
                                        e.preventDefault();
                                        this.onChangeBlockMode('paste');
                                    }}>Paste styles from...</a>
                                </div>
                            </Fragment>
                        }
                        {
                            ( 'edit' === this.state.blockMode 
                            || 'shortcode-generator' === this.state.blockMode )
                            &&
                            <Fragment>
                                {
                                    Object.keys(properties).length > 0
                                    ?
                                    <PropertyAccordion
                                        properties={properties}
                                        onPropertyChange={(propertyId, value, options = {}) => this.props.onBlockPropertyChange( this.props.shortcode.uid, propertyId, value, options )}
                                    />
                                    :
                                    <p>There are no adjustable properties for this block.</p>
                                }
                            </Fragment>
                        }
                        {
                            ( 'copy' === this.state.blockMode || 'paste' === this.state.blockMode )
                            &&
                            <Fragment>
                                <a href="#" onClick={(e) => {
                                    e.preventDefault();
                                    this.onChangeBlockMode('edit');
                                }}>Stop</a>
                                <p>
                                    {
                                        'copy' === this.state.blockMode
                                        ?
                                        'Copy styles to:'
                                        :
                                        'Paste styles from:'
                                    }
                                </p>
                                {
                                    (() => {
                                        // Prefer shortcodes from context (state) as they have depth calculated
                                        // Otherwise fall back to props shortcodes
                                        const shortcodesToUse = shortcodesFromContext && shortcodesFromContext.length > 0
                                            ? shortcodesFromContext
                                            : (Array.isArray(this.props.shortcodes) 
                                                ? this.props.shortcodes 
                                                : Object.values(this.props.shortcodes || {}));
                                        
                                        // Calculate depth if not already present
                                        const shortcodesWithDepth = calculateShortcodeDepths(shortcodesToUse);
                                        
                                        return shortcodesWithDepth.map((shortcode, i) => {
                                            if (!shortcode || typeof shortcode !== 'object') return null;
                                            
                                            const depth = shortcode.depth !== undefined ? shortcode.depth : 0;
                                            const indentStyle = {
                                                paddingLeft: `${depth * 15}px`
                                            };
                                        
                                        if ( shortcode.uid === this.props.shortcode.uid ) {
                                            return (
                                                <div
                                                    key={i}
                                                    className="wprm-template-menu-block wprm-template-menu-block-self"
                                                    style={indentStyle}
                                                >{ 'copy' === this.state.blockMode ? 'Copying from' : 'Pasting to' } { shortcode.name }</div>
                                            );
                                        } else {
                                            return (
                                                <div
                                                    key={i}
                                                    className={ shortcode.uid === hoveringBlock ? 'wprm-template-menu-block wprm-template-menu-block-hover' : 'wprm-template-menu-block' }
                                                    style={indentStyle}
                                                    onClick={ () => {
                                                        const from = 'copy' === this.state.blockMode ? this.props.shortcode.uid : shortcode.uid;
                                                        const to = 'copy' === this.state.blockMode ? shortcode.uid : this.props.shortcode.uid;
                                                        this.onCopyPasteStyle(from, to);
                                                    }}
                                                    onMouseEnter={ onChangeHoveringBlock ? () => onChangeHoveringBlock(shortcode.uid) : undefined }
                                                    onMouseLeave={ onChangeHoveringBlock ? () => onChangeHoveringBlock(false) : undefined }
                                                >{ shortcode.name }</div>
                                            );
                                        }
                                        });
                                    })()
                                }
                            </Fragment>
                        }
                    </BlockProperties>
                    :
                    null
                }
            </Fragment>
        );
    }
}
