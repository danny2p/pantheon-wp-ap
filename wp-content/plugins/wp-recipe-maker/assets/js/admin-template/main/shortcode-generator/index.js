import React, { Component, Fragment } from 'react';

import PreviewShortcode from './PreviewShortcode';
import SelectShortcode from './SelectShortcode';
import Shortcodes from '../../general/shortcodes';

const { shortcodeGroups, shortcodeKeysAlphebetically } = Shortcodes;

const ShortcodeGenerator = (props) => {
    return (
        <Fragment>
            <div className="wprm-main-container">
                <h2 className="wprm-main-container-name">Shortcode Generator</h2>
                <p style={{ textAlign: 'center'}}>Every part of a recipe can be displayed using a shortcode. This shortcode can be used anywhere on the page, outside of the recipe card. Use this Shortcode Generator to easily set up things the way you want them to look and get the exact shortcode to use.</p>
                <p style={{ textAlign: 'center'}}>A shortcode will display its value for the current recipe on the page. If none is found or set, nothing will show. Alternatively the <pre style={ { display: 'inline' } }>id="123"</pre> attribute can be added to the shortcode to have it display the value of a specific recipe ID.</p>
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
        </Fragment>
    );
}

export default ShortcodeGenerator;