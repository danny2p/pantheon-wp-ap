import React, { Component, Fragment } from 'react';

import '../../../../css/admin/dashboard/recipes.scss';

import { __wprm } from 'Shared/Translations';

import Block from '../../layout/Block';
import Recipe from './Recipe';

export default class Recipes extends Component {
    constructor(props) {
        super(props);

        this.state = {
            recipes: wprm_admin_dashboard.recipes,
        }
    }

    render() {
        return (
            <Block
                title={ __wprm( 'Latest Recipes' ) }
                button={ __wprm( 'Create Recipe' ) }
                buttonAction={ () => {
                    WPRM_Modal.open( 'recipe', {
                        saveCallback: ( savedRecipe ) => {
                            let newRecipes = JSON.parse( JSON.stringify( this.state.recipes ) );
                            let newRecipe = JSON.parse( JSON.stringify( savedRecipe ) );
                            newRecipe.date_formatted = wprm_admin_dashboard.today_formatted;

                            // Remove existing with same ID (multiple saves).
                            newRecipes = newRecipes.filter( (recipe) => recipe.id !== newRecipe.id );
 
                            // Add on top.
                            newRecipes.unshift( newRecipe );

                            this.setState({
                                recipes: newRecipes,
                            });
                        },
                    } );
                }}
            >
                <div className="wprm-admin-dashboard-recipes-container">
                    {
                        0 === this.state.recipes.length
                        ?
                        <div className="wprm-admin-dashboard-recipes-all">
                            { __wprm( 'No recipes found. Welcome to WP Recipe Maker!' ) }
                        </div>
                        :
                        <Fragment>
                            {
                                this.state.recipes.map( ( recipe, index ) => {
                                    return (
                                        <Recipe
                                            recipe={ recipe }
                                            onUpdate={ ( recipe ) => {
                                                let newRecipes = JSON.parse( JSON.stringify( this.state.recipes ) );
                                                newRecipes[ index ] = recipe;

                                                this.setState({
                                                    recipes: newRecipes,
                                                });
                                            } }
                                            key={ index }
                                        />
                                    )
                                } )
                            }
                            
                        </Fragment>
                    }
                    <div className="wprm-admin-dashboard-recipes-all">
                        <a href={ wprm_admin.manage_url }>{ __wprm( 'Manage all recipes...' ) }</a>
                    </div>
                </div>
            </Block>
        );
    }
}