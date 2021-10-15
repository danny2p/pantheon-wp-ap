import React from 'react';
 
const FieldTextarea = (props) => {
    return (
        <textarea
            value={props.value}
            placeholder={props.placeholder}
            onChange={(e) => {
                props.onChange(e.target.value);
            }}
        />
    );
}
export default FieldTextarea;