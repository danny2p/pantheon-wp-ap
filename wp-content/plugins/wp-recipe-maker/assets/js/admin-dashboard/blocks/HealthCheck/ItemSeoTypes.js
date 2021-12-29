import React, { Fragment } from 'react';

import Item from './Item';

const ItemSeoTypes = ( props ) => {
    const seo = props.item;
    const totalNbrRecipes = Object.values( seo ).reduce( (a, b) => a + b );

    if ( 0 === totalNbrRecipes ) {
        return (
            <Item
                header={ `🤷‍♂️ No recipes yet` }>
                <p>We can't seem to find any recipes yet. Start adding them!</p>
            </Item>
        )
    }

    const seoTypes = {
        good: 'Good SEO',
        rating: 'Only missing recipe ratings',
        warning: 'Missing some recommended fields',
        bad: 'Missing some required fields',
        other: 'Not using metadata (excluded from count)',
    }

    return (
        <Item
            header={ `🔍 Recipes with a good SEO rating: ${ seo.good } of ${ totalNbrRecipes - seo.other }` }>
            <p>Google has a list of required and recommended fields they want to see in the metadata. You definitely need to fill in all required fields. Not filling in recommended fields will get you a warning, but is not a huge problem. Click to see those recipes on the manage page:</p>
            <div className="wprm-admin-dashboard-health-check-list">
                {
                    Object.keys( seoTypes ).map( ( type, index ) => {
                        const label = seoTypes[ type ];

                        return (
                            <div
                                className="wprm-admin-dashboard-health-check-list-item"
                                key={ index }
                            >
                                <div className="wprm-admin-dashboard-health-check-list-item-main">
                                    <a href={ `${ wprm_admin.manage_url}#recipe/seo=${ type }` }>{ label }</a>
                                </div>
                                <div className="wprm-admin-dashboard-health-check-list-item-side">{ seo[ type ] } { 1 === seo[ type ] ? 'recipe' : 'recipes' }</div>
                            </div>
                        )
                    })
                }
            </div>
        </Item>
    );
}
export default ItemSeoTypes;