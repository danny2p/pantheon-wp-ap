import React, { Fragment } from 'react';

import { __wprm } from 'Shared/Translations';
import Icon from 'Shared/Icon';
 
const Recipe = (props) => {
    const { recipe } = props;
    const hasPermalink = recipe.hasOwnProperty( 'permalink' ) && recipe.permalink;

    return (
        <div className="wprm-admin-dashboard-recipes-recipe">
            <div className="wprm-admin-dashboard-recipes-recipe-name-container">
            <div className="wprm-admin-dashboard-recipes-recipe-date">{ recipe.date_formatted ? recipe.date_formatted : '' }</div>
            <div className="wprm-admin-dashboard-recipes-recipe-name">{ recipe.name ? recipe.name : 'n/a' }</div>
            </div>
            <div className="wprm-admin-dashboard-recipes-recipe-actions">
                {
                    hasPermalink
                    &&
                    <div className="wprm-admin-dashboard-recipes-recipe-action">
                        <a href={ recipe.permalink } target="_blank">
                            <Icon
                                type="eye"
                                title={ __wprm( 'View Recipe' ) }
                            />
                        </a>
                    </div>
                }
                <div className="wprm-admin-dashboard-recipes-recipe-action">
                    <Icon
                        type="pencil"
                        title={ __wprm( 'Edit Recipe' ) }
                        onClick={() => {
                            WPRM_Modal.open( 'recipe', {
                                recipeId: recipe.id, // Make sure it loads the latest version to prevent issues after already making changes.
                                saveCallback: ( savedRecipe ) => {
                                    let newRecipe = JSON.parse( JSON.stringify( recipe ) );
                                    newRecipe.name = savedRecipe.name;

                                    props.onUpdate( newRecipe );
                                },
                            } );
                        }}
                    />
                </div>
            </div>
        </div>
    );
}
export default Recipe;