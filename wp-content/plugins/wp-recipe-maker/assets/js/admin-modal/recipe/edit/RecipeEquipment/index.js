import React, { Component } from 'react';

import '../../../../../css/admin/modal/recipe/fields/equipment.scss';

import EditMode from '../../../general/EditMode';
import { __wprm } from 'Shared/Translations';
const { hooks } = WPRecipeMaker['wp-recipe-maker/dist/shared'];

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
        let modes = {
            edit: {
                label: __wprm( 'Edit Equipment' ),
                block: EquipmentEdit,
            },
            'equipment-affiliate': {
                label: __wprm( 'Equipment Affiliate Fields' ),
                block: () => ( <p>{ __wprm( 'This feature is only available in' ) } <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Premium</a>.</p> ),
            },
            // 'products': { // TODO Products
            //     label: __wprm( 'Products' ),
            //     block: () => ( <p>{ __wprm( 'This feature is only available in' ) } <a href="https://bootstrapped.ventures/wp-recipe-maker/get-the-plugin/" target="_blank">WP Recipe Maker Elite Bundle</a>.</p> ),
            // },
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
                        onModeChange={ this.props.onModeChange }
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
