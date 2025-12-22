import React, { Component, Fragment } from 'react';
import Header from '../../general/Header';
import Footer from '../../general/Footer';
import Loader from 'Shared/Loader';
import ApiWrapper from 'Shared/ApiWrapper';
import Button from 'Shared/Button';
import FieldCheckbox from '../../fields/FieldCheckbox';
import { convertTermNamesToObjects } from 'Shared/CategoryTerms';
import { __wprm } from 'Shared/Translations';

import '../../../../css/admin/modal/recipe/suggest-tags.scss';

export default class SuggestTags extends Component {
    constructor(props) {
        super(props);
        
        // Check if there are existing tags
        const existingTags = props.tags || {};
        const hasExistingTags = Object.keys(existingTags).some(category => {
            return existingTags[category] && Array.isArray(existingTags[category]) && existingTags[category].length > 0;
        });
        
        this.state = {
            loading: true,
            error: false,
            errorMessage: '',
            suggestions: {},
            selectedSuggestions: {}, // Track which suggestions are selected per category
            replaceExisting: false,
            hasExistingTags: hasExistingTags,
        };
    }

    componentDidMount() {
        this.fetchSuggestions();
    }

    fetchSuggestions() {
        // Get recipe data from props (passed from parent modal)
        const recipe = this.props.recipe;
        
        // Get categories from global modal state
        const categories = Object.keys( wprm_admin_modal.categories ).map( categoryKey => {
            const category = wprm_admin_modal.categories[ categoryKey ];
            return {
                key: categoryKey,
                name: category.label || categoryKey,
            };
        });

        // Prepare data for API
        const data = {
            recipe: recipe,
            categories: categories,
        };

        // Get modal endpoint
        const modalEndpoint = wprm_admin.endpoints.modal;
        const endpoint = `${modalEndpoint}/ai-suggest-tags`;

        // Call API
        ApiWrapper.call( endpoint, 'POST', data )
            .then( ( response ) => {
                if ( response && response.success && response.suggestions ) {
                    // Convert suggestions to proper format and initialize selected state
                    const formattedSuggestions = {};
                    const selectedSuggestions = {};
                    
                    Object.keys(response.suggestions).forEach(categoryKey => {
                        const categorySuggestions = response.suggestions[categoryKey] || [];
                        formattedSuggestions[categoryKey] = categorySuggestions.map(suggestion => {
                            // Convert string suggestions to tag format
                            return {
                                term_id: suggestion,
                                name: suggestion,
                            };
                        });
                        // Initialize all suggestions as selected
                        selectedSuggestions[categoryKey] = [...formattedSuggestions[categoryKey]];
                    });
                    
                    this.setState({
                        loading: false,
                        error: false,
                        suggestions: formattedSuggestions,
                        selectedSuggestions: selectedSuggestions,
                    });
                } else {
                    // Handle error
                    const errorMessage = response?.error || __wprm( 'Failed to get AI suggestions. Please try again.' );
                    this.setState({
                        loading: false,
                        error: true,
                        errorMessage: errorMessage,
                    });
                }
            } )
            .catch( ( error ) => {
                console.error( 'Error fetching AI suggestions:', error );
                this.setState({
                    loading: false,
                    error: true,
                    errorMessage: __wprm( 'An error occurred while fetching suggestions. Please try again.' ),
                });
            } );
    }

    toggleSuggestion(categoryKey, suggestion) {
        const { selectedSuggestions } = this.state;
        const categorySelected = selectedSuggestions[categoryKey] || [];
        
        // Check if suggestion is already selected
        const isSelected = categorySelected.some(s => 
            s.term_id === suggestion.term_id && s.name === suggestion.name
        );
        
        let newSelected = { ...selectedSuggestions };
        
        if (isSelected) {
            // Remove from selected
            newSelected[categoryKey] = categorySelected.filter(s => 
                !(s.term_id === suggestion.term_id && s.name === suggestion.name)
            );
        } else {
            // Add to selected
            newSelected[categoryKey] = [...categorySelected, suggestion];
        }
        
        this.setState({
            selectedSuggestions: newSelected,
        });
    }

    selectAll() {
        const { suggestions } = this.state;
        this.setState({
            selectedSuggestions: JSON.parse(JSON.stringify(suggestions)),
        });
    }

    deselectAll() {
        this.setState({
            selectedSuggestions: {},
        });
    }

    handleSave() {
        const { selectedSuggestions, replaceExisting } = this.state;
        const existingTags = this.props.tags || {};
        
        // Convert selected suggestions to tag format
        const newTags = {};
        
        Object.keys(selectedSuggestions).forEach(categoryKey => {
            const selected = selectedSuggestions[categoryKey] || [];
            
            if (selected.length > 0) {
                // Extract term names from selected suggestions
                const termNames = selected.map(suggestion => 
                    (suggestion.name || String(suggestion.term_id)).trim()
                );
                
                // Convert term names to term objects (looks up or creates terms)
                const processedTerms = convertTermNamesToObjects(categoryKey, termNames);
                
                if (processedTerms.length > 0) {
                    if (replaceExisting) {
                        // Replace existing tags
                        newTags[categoryKey] = processedTerms;
                    } else {
                        // Merge with existing tags
                        const existing = existingTags[categoryKey] || [];
                        // Avoid duplicates
                        const existingNames = new Set(
                            existing.map(t => (t.name || String(t.term_id)).trim().toLowerCase())
                        );
                        const merged = [...existing];
                        
                        processedTerms.forEach(term => {
                            const termName = (term.name || String(term.term_id)).trim().toLowerCase();
                            if (!existingNames.has(termName)) {
                                merged.push(term);
                                existingNames.add(termName);
                            }
                        });
                        
                        newTags[categoryKey] = merged;
                    }
                }
            } else if (replaceExisting && existingTags[categoryKey]) {
                // If replacing and no selections, clear this category
                newTags[categoryKey] = [];
            }
        });
        
        // Merge with existing tags for categories not in suggestions
        if (!replaceExisting) {
            Object.keys(existingTags).forEach(categoryKey => {
                if (!newTags.hasOwnProperty(categoryKey)) {
                    newTags[categoryKey] = existingTags[categoryKey];
                }
            });
        } else {
            // When replacing, keep categories not in suggestions
            Object.keys(existingTags).forEach(categoryKey => {
                if (!newTags.hasOwnProperty(categoryKey)) {
                    newTags[categoryKey] = existingTags[categoryKey];
                }
            });
        }
        
        // Call the callback
        if (this.props.onSuggestTags) {
            this.props.onSuggestTags(newTags);
        }
        
        // Close modal
        this.props.maybeCloseModal();
    }

    render() {
        const { loading, error, errorMessage, suggestions, selectedSuggestions, replaceExisting, hasExistingTags } = this.state;
        const categories = Object.keys( wprm_admin_modal.categories );

        // Check if there are any selected suggestions
        const hasSelectedSuggestions = Object.keys(selectedSuggestions).some(categoryKey => {
            const selected = selectedSuggestions[categoryKey] || [];
            return selected.length > 0;
        });

        // Check if all suggestions are selected
        const allSelected = Object.keys(suggestions).every(categoryKey => {
            const categorySuggestions = suggestions[categoryKey] || [];
            const categorySelected = selectedSuggestions[categoryKey] || [];
            return categorySuggestions.length > 0 && categorySuggestions.length === categorySelected.length;
        });

        // Determine save button text
        let saveButtonText = __wprm( 'Add Tags' );
        if (hasExistingTags && replaceExisting) {
            saveButtonText = __wprm( 'Replace Tags' );
        }

        return (
            <Fragment>
                <Header
                    onCloseModal={ this.props.maybeCloseModal }
                >
                    { __wprm( 'Suggest Tags' ) }
                </Header>
                <div className="wprm-admin-modal-suggest-tags-container" style={{ padding: '20px', maxHeight: 'calc(100vh - 200px)', overflowY: 'auto' }}>
                    { loading && (
                        <div style={{ textAlign: 'center', padding: '20px' }}>
                            <Loader />
                            <p style={{ marginTop: '10px' }}>{ __wprm( 'Getting Tag Suggestions...' ) }</p>
                        </div>
                    ) }
                    
                    { error && (
                        <div className="wprm-admin-modal-suggest-tags-error">
                            <p><strong>{ __wprm( 'Error' ) }</strong></p>
                            <p>{ errorMessage }</p>
                        </div>
                    ) }
                    
                    { !loading && !error && Object.keys(suggestions).length > 0 && (
                        <Fragment>
                            <div style={{ marginBottom: '20px', display: 'flex', gap: '10px' }}>
                                <Button
                                    onClick={ (e) => {
                                        e.preventDefault();
                                        this.deselectAll();
                                    } }
                                    disabled={ !hasSelectedSuggestions }
                                >
                                    { __wprm( 'Deselect All' ) }
                                </Button>
                                <Button
                                    onClick={ (e) => {
                                        e.preventDefault();
                                        this.selectAll();
                                    } }
                                    disabled={ allSelected }
                                >
                                    { __wprm( 'Select All' ) }
                                </Button>
                            </div>
                            
                            { categories.map((categoryKey) => {
                                const categoryData = wprm_admin_modal.categories[categoryKey];
                                const categorySuggestions = suggestions[categoryKey] || [];
                                const categorySelected = selectedSuggestions[categoryKey] || [];
                                
                                if (categorySuggestions.length === 0) {
                                    return null;
                                }
                                
                                return (
                                    <div key={categoryKey} className="wprm-admin-modal-suggest-tags-category">
                                        <h3>
                                            { categoryData.label || categoryKey }
                                        </h3>
                                        <div className="wprm-admin-modal-suggest-tags-suggestions">
                                            { categorySuggestions.map((suggestion, index) => {
                                                const isSelected = categorySelected.some(s => 
                                                    s.term_id === suggestion.term_id && s.name === suggestion.name
                                                );
                                                
                                                return (
                                                    <button
                                                        key={index}
                                                        type="button"
                                                        onClick={() => this.toggleSuggestion(categoryKey, suggestion)}
                                                        className={`wprm-admin-modal-suggest-tags-suggestion ${isSelected ? 'selected' : ''}`}
                                                    >
                                                        { suggestion.name }
                                                    </button>
                                                );
                                            }) }
                                        </div>
                                    </div>
                                );
                            }) }
                            
                            { hasExistingTags && (
                                <div className="wprm-admin-modal-suggest-tags-replace">
                                    <label>
                                        <FieldCheckbox
                                            value={ replaceExisting }
                                            onChange={ (checked) => {
                                                this.setState({ replaceExisting: checked });
                                            } }
                                        />
                                        <span>{ __wprm( 'Replace existing tags' ) }</span>
                                    </label>
                                </div>
                            ) }
                        </Fragment>
                    ) }
                    
                    { !loading && !error && Object.keys(suggestions).length === 0 && (
                        <p>{ __wprm( 'No suggestions available.' ) }</p>
                    ) }
                </div>
                <Footer>
                    <button
                        className="button"
                        onClick={ this.props.maybeCloseModal }
                    >
                        { __wprm( 'Close' ) }
                    </button>
                    { !loading && !error && Object.keys(suggestions).length > 0 && (
                        <button
                            className="button button-primary"
                            onClick={ (e) => {
                                e.preventDefault();
                                this.handleSave();
                            } }
                            disabled={ !hasSelectedSuggestions }
                            style={{ marginLeft: '10px' }}
                        >
                            { saveButtonText }
                        </button>
                    ) }
                </Footer>
            </Fragment>
        );
    }
}

