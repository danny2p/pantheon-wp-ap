import React, { Fragment } from 'react';

const PropertyNumber= (props) => {
    const suffix = props.property.hasOwnProperty('suffix') ? props.property.suffix : '';
    const value = suffix ? props.value.replace(suffix, '') : props.value;

    return (
        <Fragment>
            <input
                className="wprm-template-property-input"
                type="number"
                value={value}
                onChange={(e) => {
                    const newValue = `${e.target.value}${suffix}`;
                    return props.onValueChange(newValue);
                }}
            />
            {
                suffix
                &&
                <span className="wprm-template-property-number-suffix"> { suffix }</span>
            }
        </Fragment>
    );
}

export default PropertyNumber;