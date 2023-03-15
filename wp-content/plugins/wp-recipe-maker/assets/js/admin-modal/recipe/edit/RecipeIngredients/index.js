import React, { Component } from 'react';

import '../../../../../css/admin/modal/recipe/fields/ingredients.scss';

import EditMode from '../../../general/EditMode';
import { __wprm } from 'Shared/Translations';
const { hooks } = WPRecipeMaker['wp-recipe-maker/dist/shared'];

import IngredientsEdit from './IngredientsEdit';
import IngredientsPreview from './IngredientsPreview';

import UnitConversion from '../../../../../../../wp-recipe-maker-premium/assets/js/admin-modal/recipe/ingredients/UnitConversion';

export default class RecipeIngredients extends Component {
    constructor(props) {
        super(props);

        this.state = {
            mode: 'edit',
        }
    }

    shouldComponentUpdate(nextProps, nextState) {
        return this.state.mode !== nextState.mode
                || this.props.type !== nextProps.type
                || this.props.linkType !== nextProps.linkType
                || this.props.system !== nextProps.system
                || JSON.stringify( this.props.ingredients ) !== JSON.stringify( nextProps.ingredients )
                || JSON.stringify( this.props.instructions ) !== JSON.stringify( nextProps.instructions );
    }
  
    render() {
        let modes = {
            edit: {
                label: 'howto' === this.props.type ? __wprm( 'Edit Materials' ) : __wprm( 'Edit Ingredients' ),
                block: IngredientsEdit,
            },
            'ingredient-links': {
                label: 'howto' === this.props.type ? __wprm( 'Material Links' ) : __wprm( 'Ingredient Links' ),
                block: () => ( <p>{ __wprm( 'This feature is only available in' ) } <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Premium</a>.</p> ),
            },
            'unit-conversion': {
                label: __wprm( 'Unit Conversion' ),
                block: () => ( <p>{ __wprm( 'This feature is only available in' ) } <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Pro Bundle</a>.</p> ),
            },
            // 'products': { // TODO Products
            //     label: __wprm( 'Products' ),
            //     block: () => ( <p>{ __wprm( 'This feature is only available in' ) } <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Elite Bundle</a>.</p> ),
            // },
        };

        // TODO: Doing it here because of invariant hook error others.
        if ( wprm_admin.addons.pro ) {
            modes['unit-conversion'].block = UnitConversion;
        }

        const allModes = hooks.applyFilters( 'modalRecipeIngredients', modes );
        const Content = allModes.hasOwnProperty(this.state.mode) ? allModes[this.state.mode].block : false;

        if ( ! Content ) {
            return null;
        }

        let mode = null;
        switch ( this.state.mode ) {
            case 'products':
                mode = (
                    <Content
                        taxonomy="wprm_ingredient"
                        items={ this.props.ingredients.filter((field) => 'ingredient' === field.type && field.name ) }
                        onItemsChange={ ( ingredients_flat ) => {                            
                            this.props.onRecipeChange({
                                ingredients_flat,
                            });
                        }}
                    />
                );
                break;
            case 'unit-conversion':
                mode = (
                    <Content
                        ingredients={ this.props.ingredients }
                        onIngredientsChange={ ( ingredients_flat ) => {                            
                            this.props.onRecipeChange({
                                ingredients_flat,
                            });
                        }}
                        system={ this.props.system }
                        onSystemChange={ ( unit_system ) => {
                            this.props.onRecipeChange({
                                unit_system,
                            });
                        } }
                    />
                );
                break;
            case 'ingredient-links':
                mode = (
                    <Content
                        ingredients={ this.props.ingredients }
                        onIngredientsChange={ ( ingredients_flat ) => {                            
                            this.props.onRecipeChange({
                                ingredients_flat,
                            });
                        }}
                        type={ this.props.linkType }
                        onTypeChange={ ( ingredient_links_type ) => {
                            this.props.onRecipeChange({
                                ingredient_links_type,
                            });
                        } }
                        onModeChange={ this.props.onModeChange }
                    />
                );
                break;
            case 'preview':
                mode = (
                    <Content
                        ingredients={ this.props.ingredients }
                    />
                );
                break;
            default:
                mode = (
                    <Content
                        type={ this.props.type }
                        ingredients={ this.props.ingredients }
                        instructions={ this.props.instructions }
                        onRecipeChange={ this.props.onRecipeChange }
                    />
                );
        }

        return (
            <div className="wprm-admin-modal-field-ingredient-container">
                <EditMode
                    modes={ modes }
                    mode={ this.state.mode }
                    onModeChange={(mode) => {
                        this.setState({
                            mode,
                        })
                    }}
                />
                { mode }
            </div>
        );
    }
}
