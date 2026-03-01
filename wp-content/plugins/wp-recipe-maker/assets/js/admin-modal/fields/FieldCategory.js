import React, { Component } from 'react';
import he from 'he';
import AsyncSelect from 'react-select/async';
import AsyncCreatableSelect from 'react-select/async-creatable';

import { __wprm } from 'Shared/Translations';
import ApiWrapper from 'Shared/ApiWrapper';

export default class FieldCategory extends Component {
    constructor(props) {
        super(props);
        
        // Cache for loaded terms to avoid re-fetching.
        this.loadedTerms = {};
        
        // Track if component is mounted to prevent setState warnings.
        this._isMounted = false;
        this.loadSelectedRequestId = 0;
        
        // Load selected terms initially.
        this.state = {
            selectedOptions: [],
            loadingSelected: false,
        };
    }

    componentDidMount() {
        this._isMounted = true;
        
        // Cache default terms from get_categories().
        const defaultTerms = wprm_admin_modal.categories[ this.props.id ].terms || [];
        defaultTerms.forEach(term => {
            this.loadedTerms[term.term_id] = term;
        });
        
        // Load selected terms after component is mounted.
        if ( this.props.value && this.props.value.length > 0 ) {
            this.loadSelectedTerms();
        }
    }

    componentWillUnmount() {
        this._isMounted = false;
        this.loadSelectedRequestId += 1;
    }

    componentDidUpdate(prevProps) {
        // Reload selected terms if value changed.
        if ( JSON.stringify(prevProps.value) !== JSON.stringify(this.props.value) ) {
            if ( this.props.value && this.props.value.length > 0 ) {
                this.loadSelectedTerms();
            } else {
                if ( this._isMounted ) {
                    this.setState({ selectedOptions: [] });
                }
            }
        }
    }

    loadSelectedTerms() {
        const requestId = ++this.loadSelectedRequestId;

        // Extract term IDs - can be numeric (existing terms) or strings (new terms not yet saved).
        const termIds = this.props.value
            .map(term => {
                const id = term.term_id || term.name;
                // Only fetch numeric IDs from API (new terms will be handled in render).
                return id && 'number' === typeof id ? id : null;
            })
            .filter(id => null !== id);

        // If we have numeric IDs, fetch them from API.
        if ( termIds.length > 0 ) {
            if ( ! this._isMounted ) {
                return;
            }
            this.setState({ loadingSelected: true });

            const modalEndpoint = wprm_admin.endpoints.modal;
            const endpoint = `${modalEndpoint}/categories`;

            ApiWrapper.call(endpoint, 'POST', {
                taxonomy: this.props.id,
                term_ids: termIds,
            }).then((response) => {
                if ( ! this._isMounted || requestId !== this.loadSelectedRequestId ) {
                    return;
                }

                if ( response && response.terms ) {
                    const selectedOptions = response.terms.map(term => ({
                        value: term.term_id,
                        label: he.decode(term.name),
                        term: term,
                    }));

                    // Cache loaded terms.
                    response.terms.forEach(term => {
                        this.loadedTerms[term.term_id] = term;
                    });

                    // Also add any new terms (with string IDs) that weren't fetched.
                    const newTerms = this.props.value.filter(term => {
                        const id = term.term_id || term.name;
                        return id && 'number' !== typeof id;
                    });

                    newTerms.forEach(term => {
                        const name = term.name || term.term_id;
                        selectedOptions.push({
                            value: name,
                            label: name,
                            term: term,
                        });
                    });

                    this.setState({
                        selectedOptions: selectedOptions,
                        loadingSelected: false,
                    });
                } else {
                    this.setState({ loadingSelected: false });
                }
            }).catch(() => {
                if ( this._isMounted && requestId === this.loadSelectedRequestId ) {
                    this.setState({ loadingSelected: false });
                }
            });
        } else {
            // No numeric IDs to fetch, but might have new terms with string IDs.
            if ( ! this._isMounted ) {
                return;
            }

            const newTerms = this.props.value.filter(term => {
                const id = term.term_id || term.name;
                return id && 'number' !== typeof id;
            });

            if ( newTerms.length > 0 ) {
                const selectedOptions = newTerms.map(term => {
                    const name = term.name || term.term_id;
                    return {
                        value: name,
                        label: name,
                        term: term,
                    };
                });
                this.setState({ selectedOptions: selectedOptions });
            } else {
                this.setState({ selectedOptions: [] });
            }
        }
    }

    loadOptions(input) {
        // Return empty array if no search input (unless we have cached terms).
        if ( ! input ) {
            return Promise.resolve([]);
        }

        const modalEndpoint = wprm_admin.endpoints.modal;
        const endpoint = `${modalEndpoint}/categories`;

        return ApiWrapper.call(endpoint, 'POST', {
            taxonomy: this.props.id,
            search: input,
        }).then((response) => {
            if ( response && response.terms ) {
                // Cache loaded terms.
                response.terms.forEach(term => {
                    this.loadedTerms[term.term_id] = term;
                });

                // Convert to options format.
                return response.terms.map(term => ({
                    value: term.term_id,
                    label: he.decode(term.name),
                    term: term,
                }));
            }
            return [];
        }).catch(() => {
            return [];
        });
    }

    shouldComponentUpdate(nextProps, nextState) {
        return this.props.id !== nextProps.id
               || JSON.stringify(this.props.value) !== JSON.stringify(nextProps.value)
               || this.state.loadingSelected !== nextState.loadingSelected
               || JSON.stringify(this.state.selectedOptions) !== JSON.stringify(nextState.selectedOptions);
    }

    render() {
        const customProps = this.props.custom ? this.props.custom : {};
        const SelectElem = this.props.creatable ? AsyncCreatableSelect : AsyncSelect;

        // Get default options from get_categories() (top 50 most frequently used terms).
        const defaultTerms = wprm_admin_modal.categories[ this.props.id ].terms || [];
        const defaultOptions = defaultTerms.map(term => ({
            value: term.term_id,
            label: he.decode(term.name),
            term: term,
        }));

        // Convert selected value to options format.
        let selectedOptions = this.state.selectedOptions;
        
        // Fallback: if we have value but no loaded options yet, create options from value.
        if ( ! this.state.loadingSelected && this.props.value && this.props.value.length > 0 && selectedOptions.length === 0 ) {
            selectedOptions = this.props.value.map(term => ({
                value: term.term_id || term.name,
                label: term.name || term.term_id,
                term: term,
            }));
        }

        return (
            <SelectElem
                isMulti
                defaultOptions={defaultOptions}
                loadOptions={this.loadOptions.bind(this)}
                value={selectedOptions}
                placeholder={ this.props.creatable ? __wprm( 'Select from list or type to create...' ) : __wprm( 'Select from list...' ) }
                onChange={(value) => {
                    this.setState({
                        selectedOptions: value || [],
                    });

                    let newValue = [];

                    if ( value ) {
                        for ( let option of value ) {
                            if ( option.hasOwnProperty('__isNew__') && option.__isNew__ ) {
                                // New term being created.
                                newValue.push({
                                    term_id: option.label,
                                    name: option.label,
                                });
                            } else {
                                // Existing term - get from cache or option.
                                let term = option.term || this.loadedTerms[option.value];
                                
                                if ( term ) {
                                    newValue.push(term);
                                } else {
                                    // Fallback if term not in cache.
                                    newValue.push({
                                        term_id: option.value,
                                        name: option.label,
                                    });
                                }
                            }
                        }
                    }

                    this.props.onChange(newValue);
                }}
                styles={{
                    placeholder: (provided) => ({
                        ...provided,
                        color: '#444',
                        opacity: '0.333',
                    }),
                    control: (provided) => ({
                        ...provided,
                        backgroundColor: 'white',
                    }),
                    container: (provided) => ({
                        ...provided,
                        width: '100%',
                        maxWidth: this.props.width ? this.props.width : '100%',
                    }),
                }}
                { ...customProps }
            />
        );
    }
}
