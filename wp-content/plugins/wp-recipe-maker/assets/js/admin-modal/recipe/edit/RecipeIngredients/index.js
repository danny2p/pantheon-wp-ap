import React, { Component } from 'react';

import '../../../../../css/admin/modal/recipe/fields/ingredients.scss';

import EditMode from '../../../general/EditMode';
import { __wprm } from 'Shared/Translations';
import Icon from 'Shared/Icon';
const { hooks } = WPRecipeMakerAdmin['wp-recipe-maker/dist/shared'];

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
        // Calculate product count for dynamic tab title
        const ingredients = this.props.ingredients.filter((field) => 'ingredient' === field.type && field.name);
        const totalItems = ingredients.length;
        const itemsWithProducts = ingredients.filter(item => 
            item.product && item.product.id && item.product_amount && parseFloat(item.product_amount) > 0
        ).length;
        
        // Check for items with warnings (changed since product was set)
        const itemsWithWarnings = ingredients.filter(item => {
            if (!item.product || !item.product_item_snapshot || !item.product_amount) {
                return false;
            }
            
            const amount = parseFloat(item.product_amount);
            if (isNaN(amount) || amount <= 0) {
                return false;
            }
            
            const snapshot = item.product_item_snapshot;
            const current = {
                amount: item.amount || '',
                name: item.name || '',
                notes: item.notes || '',
                unit: item.unit || ''
            };
            
            return (
                snapshot.amount !== current.amount ||
                snapshot.name !== current.name ||
                snapshot.notes !== current.notes ||
                snapshot.unit !== current.unit
            );
        }).length;
        
        let productsLabel = `${__wprm( 'Products' )} (${itemsWithProducts}/${totalItems})`;
        let productsLabelClass = '';
        
        // Add warning styling if there are items with warnings
        if (itemsWithWarnings > 0) {
            productsLabelClass = 'wprm-admin-modal-field-edit-mode-warning';
            productsLabel = (
                <span style={{ display: 'inline-flex', gap: '5px', alignItems: 'center' }}>
                    <Icon type="warning" color="#8B0000" />
                    {productsLabel}
                </span>
            );
        }

        // Check if conversion feature is being used (at least one ingredient has a conversion)
        const isConversionFeatureUsed = ingredients.some(item => 
            item && 
            item.converted && 
            item.converted[2] && 
            item.converted[2].amount && 
            item.converted[2].amount !== ''
        );

        // Check for items with unit conversion warnings
        // 1. Ingredients that changed since conversion was calculated
        // 2. Ingredients added after conversion (missing snapshot when feature is used, only if they have an amount and no conversion)
        const itemsWithConversionWarnings = ingredients.filter(item => {
            // Check if ingredient was added after conversion (missing snapshot and no conversion)
            // Only count if ingredient has an amount (some ingredients don't need conversions)
            // Don't count if ingredient already has a conversion (legacy data - snapshot will be auto-generated)
            const hasConversion = item.converted && item.converted[2] && item.converted[2].amount && item.converted[2].amount !== '';
            if (isConversionFeatureUsed && !item.conversion_item_snapshot && !hasConversion && item.amount && item.amount.trim() !== '') {
                return true;
            }
            
            // Check if ingredient changed since conversion was calculated
            if (!item.conversion_item_snapshot || !item.converted || !item.converted[2] || !item.converted[2].amount) {
                return false;
            }
            
            const snapshot = item.conversion_item_snapshot;
            const current = {
                amount: item.amount || '',
                unit: item.unit || ''
            };
            
            return (
                snapshot.amount !== current.amount ||
                snapshot.unit !== current.unit
            );
        }).length;
        
        let unitConversionLabel = __wprm( 'Unit Conversion' );
        let unitConversionLabelClass = '';
        
        // Add warning styling if there are items with conversion warnings
        if (itemsWithConversionWarnings > 0) {
            unitConversionLabelClass = 'wprm-admin-modal-field-edit-mode-warning';
            unitConversionLabel = (
                <span style={{ display: 'inline-flex', gap: '5px', alignItems: 'center' }}>
                    <Icon type="warning" color="#8B0000" />
                    {unitConversionLabel}
                </span>
            );
        }

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
                label: unitConversionLabel,
                labelClass: unitConversionLabelClass,
                block: () => ( <p>{ __wprm( 'This feature is only available in' ) } <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Pro Bundle</a>.</p> ),
            },
            'products': {
                label: productsLabel,
                labelClass: productsLabelClass,
                block: () => ( <p>{ __wprm( 'This feature is only available in' ) } <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Elite Bundle</a>.</p> ),
            },
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
                        openSecondaryModal={ this.props.openSecondaryModal }
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
                        openSecondaryModal={ this.props.openSecondaryModal }
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
                        openSecondaryModal={ this.props.openSecondaryModal }
                        setUids={ this.props.setUids }
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
