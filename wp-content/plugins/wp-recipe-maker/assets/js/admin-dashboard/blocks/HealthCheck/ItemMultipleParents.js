import React, { Fragment } from 'react';
import he from 'he';

import Item from './Item';

const ItemMultipleParents = ( props ) => {
    const recipes = props.item;

    if ( ! Object.keys( recipes ).length ) {
        return null;
    }

    return (
        <Item
            header={ `📑 Recipes used in multiple places: ${ Object.keys( recipes ).length }` }>
            <p>These recipes have been added to more than 1 post or page. A recipe should generally only exist in 1 parent post. References should be made using the <a href="https://help.bootstrapped.ventures/article/182-itemlist-metadata-for-recipe-roundup-posts" target="_blank">Recipe Roundup feature</a> instead. Click on a post to edit it:</p>
            <div className="wprm-admin-dashboard-health-check-list">
                {
                    Object.keys( recipes ).map( ( id, index ) => {
                        const recipe = recipes[ id ];

                        return (
                            <div
                                className="wprm-admin-dashboard-health-check-multiple-parents"
                                key={ index }
                            >
                                <div className="wprm-admin-dashboard-health-check-multiple-parents-recipe">{ id } - { recipe.name }</div>
                                <div className="wprm-admin-dashboard-health-check-multiple-parents-posts">
                                    {
                                        recipe.posts.map( ( post, postIndex ) => {
                                            const label = `${ post.id } - ${ post.name ? post.name : 'n/a' }`;
                                            return (
                                                <div className="wprm-admin-dashboard-health-check-multiple-parents-post" key={ postIndex }>
                                                    {
                                                        post.edit_url
                                                        ?
                                                        <Fragment>
                                                            <a href={ he.decode( post.edit_url ) }>{ label }</a>{ parseInt( recipe.parent_post_id ) === parseInt( post.id ) && ` (parent post)` }
                                                        </Fragment>
                                                        :
                                                        label
                                                    }
                                                </div>
                                            )
                                        } )
                                    }
                                </div>
                            </div>
                        )
                    })
                }
            </div>
        </Item>
    );
}
export default ItemMultipleParents;