import React, { Component } from 'react';

export default class TextFilter extends Component {
    constructor(props) {
        super(props);

        this.debouncedTimer = false;

        let value = '';

        // Check for initial value.
        if ( props.filter && props.filter.value ) {
            value = props.filter.value;
        }

        this.state = {
            value: value,
            passedValue: value,
        };

        this.onChange = this.onChange.bind(this);
        this.updateFilter = this.updateFilter.bind(this);
    }

    onChange(value) {
        // Cancel existing timer.
        clearTimeout( this.debouncedTimer );

        // Value different? Need to pass along soon.
        if ( value !== this.state.passedValue ) {
            this.debouncedTimer = setTimeout(() => {
                this.updateFilter( value );
            }, 500);
        }

        this.setState({
            value,
        });
    }

    updateFilter(value) {
        this.props.onChange(value);

        this.setState({
            passedValue: value,
        });
    }

    render() {
        return (
            <input
                className="wprm-admin-manage-text-filter"
                type="text"
                value={ this.state.value }
                onChange={ (e) => this.onChange(e.target.value) }
            />
        );
    }
}