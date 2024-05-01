import React, { Component } from 'react';

export default class FieldDropdown extends Component {
    shouldComponentUpdate(nextProps) {
        return this.props.value !== nextProps.value;
    }

    render() {
        return (
            <input
                type="datetime-local"
                value={ this.props.value }
                onChange={ (event) => {
                    this.props.onChange( event.target.value );
                } }
            />
        );
    }
}