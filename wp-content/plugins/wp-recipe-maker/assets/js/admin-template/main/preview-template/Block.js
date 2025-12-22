import React, { Component, Fragment } from 'react';
import Parser from 'html-react-parser';
import domToReact from 'html-react-parser/lib/dom-to-react';

import Api from 'Shared/Api';
import Loader from 'Shared/Loader';
import Helpers from '../../general/Helpers';
import BlockProperties from '../../menu/BlockProperties';
import Property from '../../menu/Property';

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

export default class Block extends Component {
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

        Api.template.previewShortcode( this.props.shortcode.uid, this.state.fullShortcode, this.props.recipeId )
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
        
        // Only enable hover/click in specific modes.
        // Use templateMode prop for template editor interactivity (separate from Block's internal mode).
        const templateMode = this.props.templateMode;
        const interactiveModes = [ 'blocks', 'remove', 'move' ];
        const isInteractiveMode = templateMode && interactiveModes.includes( templateMode );
        
        // Check if we're in copy/paste mode (another block is being copied/pasted from/to).
        const isInCopyPasteMode = this.props.copyPasteMode && this.props.copyPasteBlock !== this.props.shortcode.uid;
        
        // For 'blocks' mode, allow interaction even when editing (to switch blocks).
        // For 'remove' and 'move' modes, only allow when not editing a block.
        // Also allow interaction when in copy/paste mode (but handle it separately).
        const canInteractForRegularModes = isInteractiveMode && ( 'blocks' === templateMode || false === this.props.editingBlock );
        const canInteract = canInteractForRegularModes || isInCopyPasteMode;
        
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
            if ( isInCopyPasteMode ) {
                // Always prevent default in copy/paste mode to stop link navigation and other default behaviors.
                e.preventDefault();
                
                // Trigger copy/paste action.
                const from = 'copy' === this.props.copyPasteMode ? this.props.copyPasteBlock : this.props.shortcode.uid;
                const to = 'copy' === this.props.copyPasteMode ? this.props.shortcode.uid : this.props.copyPasteBlock;
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
            } else if ( 'remove' === templateMode ) {
                if ( confirm( 'Are you sure you want to delete the "' + this.props.shortcode.name + '" block?' ) ) {
                    this.props.onRemoveBlock( this.props.shortcode.uid );
                }
            } else if ( 'move' === templateMode ) {
                this.props.onChangeMovingBlock( this.props.shortcode );
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
                            onMouseEnter={ canInteract ? (e) => { e.stopPropagation(); this.props.onChangeHoveringBlock( this.props.shortcode.uid ); } : undefined }
                            onMouseLeave={ canInteract ? (e) => { e.stopPropagation(); this.props.onChangeHoveringBlock( false ); } : undefined }
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
                                    
                                    if ( ! domNode.parent && this.props.shortcode.uid === this.props.hoveringBlock ) {
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
                    this.props.shortcode.uid === this.props.editingBlock
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
                                    Object.values(properties).map((property, i) => {
                                        return <Property
                                                    properties={properties}
                                                    property={property}
                                                    onPropertyChange={(propertyId, value) => this.props.onBlockPropertyChange( this.props.shortcode.uid, propertyId, value )}
                                                    key={i}
                                                />;
                                    })
                                }
                                {
                                    ! Object.keys(properties).length && <p>There are no adjustable properties for this block.</p>
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
                                    this.props.shortcodes.map((shortcode, i) => {
                                        if ( shortcode.uid === this.props.shortcode.uid ) {
                                            return (
                                                <div
                                                    key={i}
                                                    className="wprm-template-menu-block wprm-template-menu-block-self"
                                                >{ 'copy' === this.state.blockMode ? 'Copying from' : 'Pasting to' } { shortcode.name }</div>
                                            );
                                        } else {
                                            return (
                                                <div
                                                    key={i}
                                                    className={ shortcode.uid === this.props.hoveringBlock ? 'wprm-template-menu-block wprm-template-menu-block-hover' : 'wprm-template-menu-block' }
                                                    onClick={ () => {
                                                        const from = 'copy' === this.state.blockMode ? this.props.shortcode.uid : shortcode.uid;
                                                        const to = 'copy' === this.state.blockMode ? shortcode.uid : this.props.shortcode.uid;
                                                        this.onCopyPasteStyle(from, to);
                                                    }}
                                                    onMouseEnter={ () => this.props.onChangeHoveringBlock(shortcode.uid) }
                                                    onMouseLeave={ () => this.props.onChangeHoveringBlock(false) }
                                                >{ shortcode.name }</div>
                                            );
                                        }
                                    })
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