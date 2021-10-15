
import React from 'react';
import { Element } from 'react-scroll';
 
const FieldGroup = (props) => {
    let id = null;

    if ( props.id ) {
        id = `wprm-admin-modal-fields-group-${props.id}`;
    }

    return (
        <Element className="wprm-admin-modal-fields-group" id={ id } name={ id } >
            {
                props.header
                ?
                <div className="wprm-admin-modal-fields-group-header">{ props.header }</div>
                :
                null
            }
            <div className="wprm-admin-modal-fields">
                { props.children }
            </div>
        </Element>
    );
}
export default FieldGroup;