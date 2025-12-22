import React, { Component, Fragment } from 'react';
import he from 'he';

import '../../../../css/admin/modal/bulk-add.scss';

import Header from '../../general/Header';
import Footer from '../../general/Footer';
import { __wprm } from 'Shared/Translations';
import { convertTermNamesToObjects } from 'Shared/CategoryTerms';

import FieldContainer from '../../fields/FieldContainer';
import FieldText from '../../fields/FieldText';

export default class BulkAddCategories extends Component {
    constructor(props) {
        super(props);

        this.state = {
            separators: {
                comma: true,
                semicolon: true,
                pipe: true,
            },
            inputs: {},
            hasChanges: false,
        };

        // Initialize inputs for each category
        const categories = Object.keys( wprm_admin_modal.categories );
        const initialInputs = {};
        categories.forEach((category) => {
            initialInputs[category] = '';
        });
        this.state.inputs = initialInputs;

        this.useValues = this.useValues.bind(this);
        this.parseTerms = this.parseTerms.bind(this);
    }

    parseTerms(text) {
        if (!text || !text.trim()) {
            return [];
        }

        // Build regex pattern based on selected separators
        const separatorPatterns = [];
        if (this.state.separators.comma) {
            separatorPatterns.push(',');
        }
        if (this.state.separators.semicolon) {
            separatorPatterns.push(';');
        }
        if (this.state.separators.pipe) {
            separatorPatterns.push('\\|');
        }

        if (separatorPatterns.length === 0) {
            // No separators selected, return the whole text as one term
            return [text.trim()];
        }

        // Create regex pattern that matches any of the selected separators
        const regex = new RegExp(`[${separatorPatterns.join('')}]+`, 'g');
        const terms = text.split(regex).map(term => term.trim()).filter(term => term.length > 0);

        return terms;
    }

    useValues() {
        const categories = Object.keys( wprm_admin_modal.categories );
        const currentTags = this.props.tags || this.props.args?.tags || {};
        const newTags = { ...currentTags };

        categories.forEach((category) => {
            const inputText = this.state.inputs[category] || '';
            if (inputText.trim()) {
                const termNames = this.parseTerms(inputText);
                const existingTerms = newTags[category] || [];
                
                // Get existing term identifiers for duplicate checking (case-insensitive)
                const existingTermIdentifiers = new Set(
                    existingTerms.map(term => {
                        const identifier = term.name || String(term.term_id);
                        return identifier.trim().toLowerCase();
                    })
                );

                // Track terms we're adding in this batch to avoid duplicates within the input
                const termsInBatch = new Set();

                // Filter out terms that already exist in recipe or are duplicates in this batch
                const uniqueTermNames = termNames.filter((termName) => {
                    const trimmedTermName = termName.trim();
                    
                    // Skip empty terms
                    if (!trimmedTermName) {
                        return false;
                    }
                    
                    // Skip if already exists in recipe (case-insensitive match)
                    if (existingTermIdentifiers.has(trimmedTermName.toLowerCase())) {
                        return false;
                    }
                    
                    // Skip if already in this batch (to avoid duplicates within the same input)
                    if (termsInBatch.has(trimmedTermName.toLowerCase())) {
                        return false;
                    }
                    termsInBatch.add(trimmedTermName.toLowerCase());
                    
                    return true;
                });

                // Convert term names to term objects (looks up or creates terms)
                const termsToAdd = convertTermNamesToObjects(category, uniqueTermNames);

                // Merge with existing terms
                if (termsToAdd.length > 0) {
                    newTags[category] = [...existingTerms, ...termsToAdd];
                }
            }
        });

        // Call the callback to update the recipe
        const onBulkAdd = this.props.onBulkAdd || this.props.args?.onBulkAdd;
        if (onBulkAdd) {
            onBulkAdd(newTags);
        }
        this.props.maybeCloseModal();
    }

    render() {
        const categories = Object.keys( wprm_admin_modal.categories );
        const hasInput = Object.values(this.state.inputs).some(input => input && input.trim());

        return (
            <Fragment>
                <Header
                    onCloseModal={ this.props.maybeCloseModal }
                >
                    { __wprm( 'Bulk Add Categories' ) }
                </Header>
                <div
                    className="wprm-admin-modal-bulk-add-container wprm-admin-modal-bulk-add-categories-container"
                >
                    <h2>{ __wprm( 'Separators' ) }</h2>
                    <div className="wprm-admin-modal-bulk-add-categories-separators">
                        <label>
                            <input
                                type="checkbox"
                                checked={this.state.separators.comma}
                                onChange={(e) => {
                                    this.setState({
                                        separators: {
                                            ...this.state.separators,
                                            comma: e.target.checked,
                                        }
                                    });
                                }}
                            />
                            { __wprm( 'Comma' ) } (,)
                        </label>
                        <label>
                            <input
                                type="checkbox"
                                checked={this.state.separators.semicolon}
                                onChange={(e) => {
                                    this.setState({
                                        separators: {
                                            ...this.state.separators,
                                            semicolon: e.target.checked,
                                        }
                                    });
                                }}
                            />
                            { __wprm( 'Semicolon' ) } (;)
                        </label>
                        <label>
                            <input
                                type="checkbox"
                                checked={this.state.separators.pipe}
                                onChange={(e) => {
                                    this.setState({
                                        separators: {
                                            ...this.state.separators,
                                            pipe: e.target.checked,
                                        }
                                    });
                                }}
                            />
                            { __wprm( 'Pipe' ) } (|)
                        </label>
                    </div>
                    <h2>{ __wprm( 'Categories' ) }</h2>
                    <div className="wprm-admin-modal-bulk-add-categories-inputs">
                        {
                            categories.map((category, index) => {
                                const options = wprm_admin_modal.categories[category];
                                const isCreatable = options.creatable !== false;
                                const placeholder = isCreatable 
                                    ? __wprm( 'Paste or type categories separated by your chosen separators...' )
                                    : __wprm( 'Paste or type categories separated by your chosen separators (only existing terms allowed)...' );
                                
                                return (
                                    <FieldContainer
                                        id={ category }
                                        label={ options.label }
                                        help={ options.hasOwnProperty( 'help' ) ? options.help : null }
                                        key={ index }
                                    >
                                        <FieldText
                                            value={this.state.inputs[category] || ''}
                                            placeholder={ placeholder }
                                            onChange={(value) => {
                                                this.setState({
                                                    inputs: {
                                                        ...this.state.inputs,
                                                        [category]: value,
                                                    },
                                                    hasChanges: true,
                                                });
                                            }}
                                        />
                                    </FieldContainer>
                                );
                            })
                        }
                    </div>
                </div>
                <Footer>
                    <button
                        className="button"
                        onClick={ this.props.maybeCloseModal }
                    >
                        { __wprm( 'Cancel' ) }
                    </button>
                    <button
                        className="button button-primary"
                        onClick={ this.useValues }
                        disabled={ !hasInput }
                    >
                        { __wprm( 'Add' ) }
                    </button>
                </Footer>
            </Fragment>
        );
    }
}

