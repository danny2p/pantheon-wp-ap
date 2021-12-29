import React, { Fragment } from 'react';

import Item from './Item';

const ItemMissingThumbnails = ( props ) => {
    const missingThumbnails = props.item;

    if ( ! missingThumbnails || ! Object.keys( missingThumbnails ).length ) {
        return null;
    }

    return (
        <Item
            header={ `📸 Missing recipe thumbnails: ${ Object.keys( missingThumbnails ).length }` }>
            <p>Google expects to see 3 different ratios for the recipe image in the recipe metadata. WP Recipe Maker automatically generates those ratios for the recipe images, but some of them seem to be missing. This can happen if you converted those recipes from another plugin, for example.</p>
            <p>Try using a plugin like <a href="https://wordpress.org/plugins/regenerate-thumbnails/" target="_blank">Regenerate Thumbnails</a> to make sure all thumbnail sizes exist.</p>
            <p>If that doesn't fix things, the problem could also be that the original recipe image is too small. A recipe image should be at least 500 by 500 pixels to be able to generate all thumbnail sizes.</p>
            <p>Click through to find the problem recipes on the Manage page:</p>
            <div className="wprm-admin-dashboard-health-check-list">
                {
                    Object.keys( missingThumbnails ).map( ( recipe, index ) => {
                        const name = missingThumbnails[ recipe ] ? missingThumbnails[ recipe ] : 'n/a';

                        return (
                            <div
                                className="wprm-admin-dashboard-health-check-list-item"
                                key={ index }
                            >
                                <div className="wprm-admin-dashboard-health-check-list-item-main">
                                    <a href={ `${ wprm_admin.manage_url}#recipe/id=${ encodeURIComponent( recipe ) }`}>{ name }</a>
                                </div>
                            </div>
                        )
                    })
                }
            </div>
        </Item>
    );
}
export default ItemMissingThumbnails;