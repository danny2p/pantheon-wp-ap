import React, { Component, Fragment } from 'react';

import { __wprm } from 'Shared/Translations';
import Button from 'Shared/Button';
import FieldContainer from '../../fields/FieldContainer';
import FieldCategory from '../../fields/FieldCategory';

export default class RecipeCategories extends Component {
    shouldComponentUpdate(nextProps) {
        return JSON.stringify(this.props.tags) !== JSON.stringify(nextProps.tags);
    }

    render() {
        const categories = Object.keys( wprm_admin_modal.categories );

        return (
            <Fragment>
                {
                    categories.map((category, index) => {
                        const options = wprm_admin_modal.categories[ category ];
                        const value = this.props.tags.hasOwnProperty( category ) ? this.props.tags[ category ] : [];

                        return (
                            <FieldContainer
                                id={ category }
                                label={ options.label }
                                help={ options.hasOwnProperty( 'help' ) ? options.help : null }
                                key={ index }
                            >
                                <FieldCategory
                                    id={ category }
                                    value={ value }
                                    onChange={ (value) => {
                                        const tags = {
                                            ...this.props.tags,
                                        };

                                        tags[ category ] = value;

                                        this.props.onRecipeChange( { tags }, {
                                            historyMode: 'immediate',
                                            historyBoundary: true,
                                            historyKey: `categories:${category}`,
                                        } );
                                    }}
                                    creatable={ options.creatable }
                                    width="450px"
                                />
                            </FieldContainer>
                        )
                    })
                }
                <div className="wprm-admin-modal-field-category-actions">
                    <Button
                        onClick={(e) => {
                            e.preventDefault();
                            this.props.openSecondaryModal('bulk-add-categories', {
                                tags: this.props.tags,
                                onBulkAdd: (newTags) => {
                                    this.props.onRecipeChange({ tags: newTags }, {
                                        historyMode: 'immediate',
                                        historyBoundary: true,
                                        historyKey: 'categories:bulk_add',
                                    });
                                }
                            });
                        } }
                    >{ __wprm( 'Bulk Add Categories' ) }</Button>
                    <Button
                        ai
                        onClick={(e) => {
                            e.preventDefault();
                            
                            // Check if ingredients and instructions are filled in
                            const ingredients = (this.props.recipe.ingredients_flat || []).filter(
                                (field) => 'ingredient' === field.type && field.name && field.name.trim() !== ''
                            );
                            const instructions = (this.props.recipe.instructions_flat || []).filter(
                                (field) => 'instruction' === field.type && field.text && field.text.trim() !== ''
                            );
                            
                            if (ingredients.length === 0 || instructions.length === 0) {
                                const missingItems = [];
                                if (ingredients.length === 0) {
                                    missingItems.push('howto' === this.props.recipe.type ? __wprm( 'materials' ) : __wprm( 'ingredients' ));
                                }
                                if (instructions.length === 0) {
                                    missingItems.push(__wprm( 'instructions' ));
                                }
                                
                                alert(
                                    __wprm( 'Please fill in the ' ) + 
                                    missingItems.join( __wprm( ' and ' ) ) + 
                                    __wprm( ' before using Suggest Tags. The AI needs this information to provide useful tag suggestions.' )
                                );
                                return;
                            }
                            
                            this.props.openSecondaryModal('suggest-tags', {
                                recipe: this.props.recipe,
                                tags: this.props.tags,
                                onSuggestTags: (newTags) => {
                                    this.props.onRecipeChange({ tags: newTags }, {
                                        historyMode: 'immediate',
                                        historyBoundary: true,
                                        historyKey: 'categories:suggest_tags',
                                    });
                                }
                            });
                        } }
                    >{ __wprm( 'Suggest Tags' ) }</Button>
                </div>
            </Fragment>
        );
    }
}
