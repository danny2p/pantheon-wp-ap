import React, { Fragment } from 'react';

import Item from './Item';

const ItemDuplicateNames = ( props ) => {
    const duplicates = props.item;

    if ( ! duplicates.length ) {
        return null;
    }

    return (
        <Item
            header={ `✍️ Duplicate recipe names: ${ duplicates.length }` }>
            <p>These recipe names are used in more than 1 recipe and might be duplicates. Click to find them on the manage page:</p>
            <div className="wprm-admin-dashboard-health-check-list">
                {
                    duplicates.map( ( duplicate, index ) => {
                        return (
                            <div
                                className="wprm-admin-dashboard-health-check-list-item"
                                key={ index }
                            >
                                <div className="wprm-admin-dashboard-health-check-list-item-main">
                                    <a href={ `${ wprm_admin.manage_url}#recipe/name=${ encodeURIComponent( duplicate.name ) }`}>{ duplicate.name }</a>
                                </div>
                                <div className="wprm-admin-dashboard-health-check-list-item-side">{ duplicate.recipes.length } recipes</div>
                            </div>
                        )
                    })
                }
            </div>
        </Item>
    );
}
export default ItemDuplicateNames;