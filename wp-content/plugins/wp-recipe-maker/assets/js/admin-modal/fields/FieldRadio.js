import React, { Fragment } from 'react';
 
const FieldRadio = (props) => {
    return (
        <Fragment>
            {
                props.options.map((option) => (
                    <div className="wprm-admin-modal-field-radio-option" key={option.value}>
                        <input
                            type="radio"
                            value={option.value}
                            name={`wprm-admin-radio-${props.id}`}
                            id={`wprm-admin-radio-${props.id}-${option.value}`}
                            checked={props.value == option.value}
                            onChange={(e) => {
                                props.onChange(e.target.value);
                            }}
                        /><label htmlFor={`wprm-admin-radio-${props.id}-${option.value}`}>{ option.label }</label>
                    </div>
                ))
            }
        </Fragment>
    );
}
export default FieldRadio;