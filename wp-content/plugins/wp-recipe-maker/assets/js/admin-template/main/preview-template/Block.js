import React, { Component, Fragment } from 'react';
import Parser from 'html-react-parser';
import domToReact from 'html-react-parser/lib/dom-to-react';

import Api from 'Shared/Api';
import Loader from 'Shared/Loader';
import Helpers from '../../general/Helpers';
import BlockProperties from '../../menu/BlockProperties';
import Property from '../../menu/Property';

export default class Block extends Component {
    constructor(props) {
        super(props);

        this.state = {
            fullShortcode: '',
            html: '',
            loading: false,
            blockMode: 'edit',
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

        // Make sure we start out in edit mode.
        if  ( prevProps.editingBlock !== this.props.editingBlock ) {
            this.onChangeBlockMode('edit');
        }
    }

    checkShortcodeChange() {
        const fullShortcode = Helpers.getFullShortcode(this.props.shortcode);

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
    }

    render() {
        const properties = this.getBlockProperties();

        return (
            <Fragment>
                {
                    this.state.loading
                    ?
                    <Loader/>
                    :
                    <Fragment>
                        { Parser(this.state.html.trim(), {
                            replace: function(domNode) {
                                if ( ! domNode.parent && this.props.shortcode.uid === this.props.hoveringBlock ) {
                                    if ( ! domNode.attribs ) {
                                        domNode.attribs = {};
                                    }
                                    domNode.attribs.class = domNode.attribs.class ? domNode.attribs.class + ' wprm-template-block-hovering' : 'wprm-template-block-hovering';
                                    return domToReact(domNode);
                                }
                            }.bind(this)
                        }) }
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