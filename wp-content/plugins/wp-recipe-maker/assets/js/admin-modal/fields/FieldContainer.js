
import React, { Fragment } from 'react';

import Icon from 'Shared/Icon';
 
const FieldContainer = (props) => {
    let className = 'wprm-admin-modal-field-container';

    if ( props.id ) {
        className += ` wprm-admin-modal-field-container-${props.id}`;
    }

    let helpIcon = null;
    if ( props.help ) {
        helpIcon = (
            <Icon
                type="question"
                title={ props.help }
                className="wprm-admin-icon-help"
            />
        );
    }

    return (
        <div className={ className }>
            {
                props.label
                ?
                <Fragment>
                    <div className="wprm-admin-modal-field-label">{props.label}{ helpIcon }</div>
                    <div className="wprm-admin-modal-field">{ props.children }</div>
                </Fragment>
                :
                props.children
            }
        </div>
    );
}
export default FieldContainer;