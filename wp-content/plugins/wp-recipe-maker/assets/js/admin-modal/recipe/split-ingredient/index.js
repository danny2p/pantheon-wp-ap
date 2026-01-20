import React, { Component, Fragment } from 'react';

import '../../../../css/admin/modal/split-ingredient.scss';

import Header from '../../general/Header';
import Footer from '../../general/Footer';
import { __wprm } from 'Shared/Translations';
import Icon from 'Shared/Icon';

import FieldContainer from '../../fields/FieldContainer';
import FieldText from '../../fields/FieldText';
import { parseQuantity, formatQuantity } from 'Shared/quantities';

export default class SplitIngredient extends Component {
    constructor(props) {
        super(props);

        const ingredient = props.ingredient || props.args?.ingredient || {};
        const splits = ingredient.splits || [];

        this.state = {
            splits: JSON.parse(JSON.stringify(splits)),
        };

        this.addSplit = this.addSplit.bind(this);
        this.removeSplit = this.removeSplit.bind(this);
        this.updateSplit = this.updateSplit.bind(this);
        this.save = this.save.bind(this);
        this.hasValidSplits = this.hasValidSplits.bind(this);
    }

    hasValidSplits() {
        if (this.state.splits.length < 2) {
            return false;
        }

        // Calculate total percentage
        let totalPercentage = 0;
        for (let split of this.state.splits) {
            const percentage = parseFloat(split.percentage) || 0;
            if (isNaN(percentage) || percentage < 0 || percentage > 100) {
                return false;
            }
            totalPercentage += percentage;
        }

        // Total should be close to 100% (allow small rounding differences)
        return Math.abs(totalPercentage - 100) < 0.01;
    }

    getTotalPercentage() {
        let total = 0;
        for (let split of this.state.splits) {
            const percentage = parseFloat(split.percentage) || 0;
            if (!isNaN(percentage)) {
                total += percentage;
            }
        }
        return total;
    }

    calculateSplitAmount(percentage, useConverted = false) {
        const ingredient = this.props.ingredient || this.props.args?.ingredient || {};
        
        // Determine which amount to use (original or converted)
        let parentAmount = 0;
        let parentUnit = '';
        
        if (useConverted && ingredient.converted && ingredient.converted[2] && ingredient.converted[2].amount && ingredient.converted[2].amount !== '') {
            // Use converted amount
            parentAmount = parseQuantity(ingredient.converted[2].amount || '0');
            parentUnit = ingredient.converted[2].unit || '';
        } else {
            // Use original amount
            parentAmount = parseQuantity(ingredient.amount || '0');
            parentUnit = ingredient.unit || '';
        }
        
        if (isNaN(parentAmount) || parentAmount === 0) {
            return { amount: '', unit: parentUnit };
        }
        
        // Calculate split amount
        const splitAmount = (parentAmount * parseFloat(percentage || 0)) / 100;
        if (isNaN(splitAmount)) {
            return { amount: '', unit: parentUnit };
        }
        
        // Format using same function as frontend
        const decimals = typeof wprm_admin !== 'undefined' && wprm_admin.settings 
            ? parseInt(wprm_admin.settings.adjustable_servings_round_to_decimals) || 2 
            : 2;
        
        // Check system-specific fraction setting for converted amounts, otherwise use general setting
        let allowFractions = false;
        if (useConverted) {
            // For converted amounts (system 2), check system-specific setting from premium
            if (typeof wprmp_admin !== 'undefined' && wprmp_admin.settings && wprmp_admin.settings.hasOwnProperty('unit_conversion_system_2_fractions')) {
                allowFractions = wprmp_admin.settings.unit_conversion_system_2_fractions;
            } else if (typeof wprm_admin !== 'undefined' && wprm_admin.settings) {
                // Fall back to general setting if system-specific not available
                allowFractions = wprm_admin.settings.fractions_enabled || false;
            }
        } else {
            // For original amounts, use general setting
            if (typeof wprm_admin !== 'undefined' && wprm_admin.settings) {
                allowFractions = wprm_admin.settings.fractions_enabled || false;
            }
        }
        
        return {
            amount: formatQuantity(splitAmount, decimals, allowFractions),
            unit: parentUnit
        };
    }

    hasConversion() {
        const ingredient = this.props.ingredient || this.props.args?.ingredient || {};
        return ingredient.converted && 
               ingredient.converted[2] && 
               ingredient.converted[2].amount && 
               ingredient.converted[2].amount !== '';
    }

    addSplit() {
        const newSplits = [...this.state.splits];
        const maxId = newSplits.length > 0 
            ? Math.max(...newSplits.map(s => s.id || 0))
            : 0;
        
        // If we have 0 parts, add 2 parts to get started (50% each)
        if (newSplits.length === 0) {
            newSplits.push(
                {
                    id: maxId + 1,
                    percentage: '50',
                },
                {
                    id: maxId + 2,
                    percentage: '50',
                }
            );
        } else {
            // Calculate remaining percentage and distribute
            const currentTotal = this.getTotalPercentage();
            const remaining = 100 - currentTotal;
            const newPercentage = remaining > 0 ? remaining : 0;
            
            // Otherwise, just add 1 part with remaining percentage
            newSplits.push({
                id: maxId + 1,
                percentage: newPercentage > 0 ? String(newPercentage) : '',
            });
        }

        this.setState({
            splits: newSplits,
        });
    }

    removeSplit(index) {
        const newSplits = [...this.state.splits];
        newSplits.splice(index, 1);
        
        // If removing would leave only 1 part, treat it as having no parts
        if (newSplits.length === 1) {
            this.setState({
                splits: [],
            });
        } else {
            this.setState({
                splits: newSplits,
            });
        }
    }

    updateSplit(index, field, value) {
        const newSplits = [...this.state.splits];
        
        // If updating percentage, ensure it's a valid number
        if (field === 'percentage') {
            // Allow empty string for intermediate typing
            if (value === '' || value === null || value === undefined) {
                newSplits[index] = {
                    ...newSplits[index],
                    [field]: '',
                };
            } else {
                // Parse and validate the percentage
                const numValue = parseFloat(value);
                if (!isNaN(numValue)) {
                    // Clamp between 0 and 100
                    const clampedValue = Math.max(0, Math.min(100, numValue));
                    newSplits[index] = {
                        ...newSplits[index],
                        [field]: String(clampedValue),
                    };
                } else {
                    // Invalid input, keep current value
                    return;
                }
            }
        } else {
            newSplits[index] = {
                ...newSplits[index],
                [field]: value,
            };
        }

        // Always recalculate the last split's percentage to make total 100%
        if (newSplits.length > 1) {
            const lastIndex = newSplits.length - 1;
            if (index !== lastIndex || field === 'percentage') {
                const otherTotal = newSplits.slice(0, -1).reduce((sum, split) => {
                    const pct = parseFloat(split.percentage) || 0;
                    return sum + (isNaN(pct) ? 0 : pct);
                }, 0);
                const remaining = 100 - otherTotal;
                // Clamp remaining between 0 and 100
                const finalRemaining = Math.max(0, Math.min(100, remaining));
                newSplits[lastIndex].percentage = String(finalRemaining);
            }
        }

        this.setState({
            splits: newSplits,
        });
    }

    save() {
        // Validate that percentages sum to 100%
        if (!this.hasValidSplits()) {
            const total = this.getTotalPercentage();
            alert(__wprm('Percentages must sum to 100%. Current total: ' + total.toFixed(1) + '%'));
            return;
        }

        const onSave = this.props.onSave || this.props.args?.onSave;
        if (onSave) {
            // If we have less than 2 parts, save as empty array (no splits)
            const splitsToSave = this.state.splits.length >= 2 ? this.state.splits : [];
            onSave(splitsToSave);
        }
        if (this.props.maybeCloseModal) {
            this.props.maybeCloseModal();
        }
    }

    render() {
        const ingredient = this.props.ingredient || this.props.args?.ingredient || {};
        // Build ingredient string conditionally to avoid double spaces when unit is empty
        let ingredientString = '';
        if ( ingredient.amount ) {
            const parts = [ ingredient.amount ];
            if ( ingredient.unit ) { parts.push( ingredient.unit ); }
            if ( ingredient.name ) { parts.push( ingredient.name ); }
            ingredientString = parts.join( ' ' );
        } else {
            ingredientString = ingredient.name || '';
        }
        
        const totalPercentage = this.getTotalPercentage();
        const percentageValid = Math.abs(totalPercentage - 100) < 0.01;
        const hasConversion = this.hasConversion();

        return (
            <Fragment>
                <Header
                    onCloseModal={ this.props.maybeCloseModal }
                >
                    { __wprm( 'Split Ingredient' ) }
                </Header>
                <div className="wprm-admin-modal-split-ingredient-container">
                    <div className="wprm-admin-modal-split-ingredient-info">
                        <p>
                            { __wprm( 'Split an ingredient into multiple parts. Each part can be used separately in instruction steps as inline or associated ingredients.' ) }
                        </p>
                        <p className="wprm-admin-modal-split-ingredient-help">
                            { __wprm( 'For example, if you have "3 tbsp olive oil" in the ingredient list, you can split it into "2 tbsp" and "1 tbsp" parts, allowing you to use these as a part of different instruction steps.' ) }
                        </p>
                    </div>

                    <div className="wprm-admin-modal-split-ingredient-parts">
                        <p><strong>{ __wprm( 'Ingredient' ) }:</strong> { ingredientString }</p>
                        { this.state.splits.length === 0 ? (
                            <p className="wprm-admin-modal-split-ingredient-no-parts">
                                { __wprm( 'You need at least 2 parts to split an ingredient. Click "Add Part" to get started.' ) }
                            </p>
                        ) : (
                            <Fragment>
                                <div className="wprm-admin-modal-split-ingredient-header-container">
                                    <div className="wprm-admin-modal-split-ingredient-header">{ __wprm( 'Percentage' ) }</div>
                                    <div className="wprm-admin-modal-split-ingredient-header">{ __wprm( 'Amount' ) }</div>
                                    { hasConversion && (
                                        <div className="wprm-admin-modal-split-ingredient-header wprm-admin-modal-split-ingredient-header-converted">{ __wprm( 'Converted Amount' ) }</div>
                                    )}
                                    <div className="wprm-admin-modal-split-ingredient-header">{ __wprm( 'Name' ) }</div>
                                    <div className="wprm-admin-modal-split-ingredient-header">&nbsp;</div>
                                </div>
                                <div className="wprm-admin-modal-split-ingredient-parts-list">
                                    { this.state.splits.map((split, index) => {
                                        const isLast = index === this.state.splits.length - 1;
                                        
                                        // For the last split, calculate the remaining percentage
                                        let percentage = split.percentage || '';
                                        if (isLast && this.state.splits.length > 1) {
                                            const otherTotal = this.state.splits.slice(0, -1).reduce((sum, s) => {
                                                const pct = parseFloat(s.percentage) || 0;
                                                return sum + (isNaN(pct) ? 0 : pct);
                                            }, 0);
                                            const remaining = 100 - otherTotal;
                                            percentage = String(Math.max(0, Math.min(100, remaining)));
                                        }
                                        
                                        const calculatedAmount = this.calculateSplitAmount(percentage, false);
                                        const calculatedConvertedAmount = hasConversion ? this.calculateSplitAmount(percentage, true) : null;
                                        const displayName = ingredient.name || '';
                                        const displayUnit = ingredient.unit || '';
                                        
                                        return (
                                            <div key={split.id || index} className="wprm-admin-modal-split-ingredient-part">
                                                <FieldText
                                                    type="number"
                                                    min="0"
                                                    max="100"
                                                    step="0.1"
                                                    disabled={isLast && this.state.splits.length > 1}
                                                    className="wprm-admin-modal-split-ingredient-percentage-input"
                                                    value={isLast && this.state.splits.length > 1 ? percentage : (split.percentage || '')}
                                                    placeholder={isLast ? __wprm('Auto') : '50'}
                                                    onChange={(value) => {
                                                        this.updateSplit(index, 'percentage', value);
                                                    }}
                                                />
                                                <div className="wprm-admin-modal-split-ingredient-part-amount">
                                                    { calculatedAmount.amount ? `${calculatedAmount.amount} ${calculatedAmount.unit}`.trim() : '-' }
                                                </div>
                                                { hasConversion && (
                                                    <div className="wprm-admin-modal-split-ingredient-part-converted-amount">
                                                        { calculatedConvertedAmount && calculatedConvertedAmount.amount ? `${calculatedConvertedAmount.amount} ${calculatedConvertedAmount.unit}`.trim() : '-' }
                                                    </div>
                                                )}
                                                <div className="wprm-admin-modal-split-ingredient-part-name">
                                                    { displayName }
                                                </div>
                                                <div className="wprm-admin-modal-split-ingredient-part-after-container">
                                                    <div className="wprm-admin-modal-split-ingredient-part-after-container-icons">
                                                        <Icon
                                                            type="trash"
                                                            title={ __wprm( 'Remove Split' ) }
                                                            onClick={() => {
                                                                this.removeSplit(index);
                                                            }}
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </Fragment>
                        )}
                        <button
                            className="button"
                            onClick={this.addSplit}
                        >
                            { __wprm( 'Add Split' ) }
                        </button>
                        <p style={{ color: '#8B0000', marginTop: '10px', marginBottom: '0', visibility: !percentageValid && this.state.splits.length > 0 ? 'visible' : 'hidden' }}>
                            <strong>{ __wprm( 'Total' ) }:</strong> { totalPercentage.toFixed(1) }% ({ __wprm( 'should be 100%' ) })
                        </p>
                    </div>
                </div>
                <Footer>
                    <button
                        className="button button-primary"
                        onClick={this.save}
                        disabled={!this.hasValidSplits()}
                    >
                        { __wprm( 'Save' ) }
                    </button>
                    <button
                        className="button"
                        onClick={this.props.maybeCloseModal}
                    >
                        { __wprm( 'Cancel' ) }
                    </button>
                </Footer>
            </Fragment>
        );
    }
}
