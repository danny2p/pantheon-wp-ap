import React, { Component } from 'react';

import '../../../../../css/admin/modal/recipe/fields/equipment.scss';

import EditMode from '../../../general/EditMode';
import { __wprm } from 'Shared/Translations';
import Icon from 'Shared/Icon';
const { hooks } = WPRecipeMakerAdmin['wp-recipe-maker/dist/shared'];

import EquipmentEdit from './EquipmentEdit';

export default class RecipeEquipment extends Component {
    constructor(props) {
        super(props);

        this.state = {
            mode: 'edit',
        }
    }

    shouldComponentUpdate(nextProps, nextState) {
        return this.state.mode !== nextState.mode
                || this.props.type !== nextProps.type
                || JSON.stringify( this.props.equipment ) !== JSON.stringify( nextProps.equipment );
    }
  
    render() {
        // Calculate product count for dynamic tab title
        const totalItems = this.props.equipment.length;
        const itemsWithProducts = this.props.equipment.filter(item => 
            item.product && item.product.id && item.product_amount && parseFloat(item.product_amount) > 0
        ).length;
        
        // Check for items with warnings (changed since product was set)
        const itemsWithWarnings = this.props.equipment.filter(item => {
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
                notes: item.notes || ''
            };
            
            return (
                snapshot.amount !== current.amount ||
                snapshot.name !== current.name ||
                snapshot.notes !== current.notes
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

        let modes = {
            edit: {
                label: __wprm( 'Edit Equipment' ),
                block: EquipmentEdit,
            },
            'equipment-affiliate': {
                label: __wprm( 'Equipment Affiliate Fields' ),
                block: () => ( <p>{ __wprm( 'This feature is only available in' ) } <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Premium</a>.</p> ),
            },
            'products': {
                label: productsLabel,
                labelClass: productsLabelClass,
                block: () => ( <p>{ __wprm( 'This feature is only available in' ) } <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Elite Bundle</a>.</p> ),
            },
        };

        const allModes = hooks.applyFilters( 'modalRecipeEquipment', modes );
        const Content = allModes.hasOwnProperty(this.state.mode) ? allModes[this.state.mode].block : false;

        if ( ! Content ) {
            return null;
        }

        let mode = null;
        switch ( this.state.mode ) {
            case 'products':
                mode = (
                    <Content
                        taxonomy="wprm_equipment"
                        items={ this.props.equipment.filter((field) => field.name ) }
                        onItemsChange={ ( equipment ) => {                            
                            this.props.onRecipeChange({
                                equipment,
                            });
                        }}
                        openSecondaryModal={ this.props.openSecondaryModal }
                    />
                );
                break;
            default:
                mode = (
                    <Content
                        type={ this.props.type }
                        equipment={ this.props.equipment }
                        instructions={ this.props.instructions }
                        onRecipeChange={ this.props.onRecipeChange }
                        openSecondaryModal={ this.props.openSecondaryModal }
                    />
                );
        }

        return (
            <div className="wprm-admin-modal-field-equipment-container">
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
