import React, { Fragment } from 'react';

import { __wprm } from 'Shared/Translations';
 
const ActionsRating = (props) => {
    const selectedAction = props.action ? props.action.type : false;
    const actionOptions = [
        { value: 'delete', label: __wprm( 'Delete Revisions' ), default: false },
    ];

    return (
        <Fragment>
            <div className="wprm-admin-modal-bulk-edit-label">{ __wprm( 'Select an action to perform:' ) }</div>
            <div className="wprm-admin-modal-bulk-edit-actions">
                {
                    actionOptions.map((option) => (
                        <div className="wprm-admin-modal-bulk-edit-action" key={option.value}>
                            <input
                                type="radio"
                                value={option.value}
                                name={`wprm-admin-radio-bulk-edit-action`}
                                id={`wprm-admin-radio-bulk-edit-action-${option.value}`}
                                checked={selectedAction === option.value}
                                onChange={() => {
                                    const newAction = {
                                        type: option.value,
                                        options: option.default,
                                    }
                
                                    props.onActionChange(newAction);
                                }}
                            /><label htmlFor={`wprm-admin-radio-bulk-edit-action-${option.value}`}>{ option.label }</label>
                        </div>
                    ))
                }
            </div>
        </Fragment>
    );
}
export default ActionsRating;