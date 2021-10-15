import React from 'react';
import Toggle from 'react-toggle';
import 'react-toggle/style.css'

const PropertyToggle = (props) => {
    return (
        <Toggle
            className="wprm-template-property-input"
            checked={'1' === props.value}
            onChange={(e) => {
                const value = e.target.checked ? '1' : '0';
                return props.onValueChange(value)
            }}
        />
    );
}

export default PropertyToggle;