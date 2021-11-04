import React from 'react';
 
const FieldText = (props) => {
    const disabled = props.hasOwnProperty( 'disabled' ) ? props.disabled : false;
    const type = props.hasOwnProperty( 'type' ) ? props.type : 'text';

    return (
        <input
            type={ type }
            min={ props.hasOwnProperty( 'min' ) ? props.min : null }
            max={ props.hasOwnProperty( 'max' ) ? props.max : null }
            step={ props.hasOwnProperty( 'step' ) ? props.step : null }
            disabled={ disabled }
            name={props.name}
            value={props.value}
            placeholder={props.placeholder}
            onChange={(e) => {
                props.onChange( e.target.value );
            }}
            onKeyDown={(e) => {
                if ( 'number' === props.type ) {
                    // Don't allow dash in number field as it breaks the value.
                    if ( '-' === e.key ) {
                        e.preventDefault();
                    }
                }
                
                if ( props.onKeyDown ) {
                    props.onKeyDown(e);
                }
            }}
        />
    );
}
export default FieldText;