import React from 'react';
 
const FieldCheckbox = (props) => {
    const disabled = props.hasOwnProperty( 'disabled' ) ? props.disabled : false;

    return (
        <input
            type="checkbox"
            className="wprm-admin-modal-field-checkbox"
            disabled={ disabled }
            name={props.name}
            checked={props.value}
            onChange={(e) => {
                props.onChange( e.target.checked );
            }}
        />
    );
}
export default FieldCheckbox;