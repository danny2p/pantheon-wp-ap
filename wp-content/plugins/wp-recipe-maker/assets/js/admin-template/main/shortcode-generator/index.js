import React, { Component, Fragment } from 'react';

import PreviewShortcode from './PreviewShortcode';
import SelectShortcode from './SelectShortcode';
import Shortcodes from '../../general/shortcodes';

const { shortcodeGroups, shortcodeKeysAlphebetically } = Shortcodes;

const ShortcodeGenerator = (props) => {
    
    // Get Block Properties.
    let properties = {};
    if ( false && shortcode ) {
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
    }

    return (
        <Fragment>
            <div className="wprm-main-container">
                <h2 className="wprm-main-container-name">Shortcode Generator</h2>
                <p style={{ textAlign: 'center'}}>Every part of a recipe can be displayed using a shortcode. This shortcode can be used anywhere on the page, outside of the recipe card. Use this Shortcode Generator to easily set up things the way you want them to look and get the exact shortcode to use.</p>
            </div>
            {
                false === props.shortcode
                ?
                <SelectShortcode
                    onChangeShortcode={ props.onChangeShortcode }
                />
                :
                <PreviewShortcode
                    shortcode={ props.shortcode }
                    onChangeShortcode={ props.onChangeShortcode }
                />
            }
            {/* <BlockProperties>
                {
                    'edit' === this.state.blockMode
                    &&
                    <Fragment>
                        <div className="wprm-template-menu-block-details">{ this.props.shortcode.name }</div>
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
            </BlockProperties> */}
        </Fragment>
    );
}

export default ShortcodeGenerator;