import React, { Fragment } from 'react';

import FieldDropdown from '../fields/FieldDropdown';
import { __wprm } from 'Shared/Translations';
import { ideaStatusOptions } from 'Shared/Ideas';

const actionOptions = [
    { value: 'change-status', label: __wprm( 'Change Idea Status' ), default: 'idea' },
    { value: 'delete', label: __wprm( 'Delete Ideas' ), default: false },
];

const ActionsIdea = ( props ) => {
    const selectedAction = props.action ? props.action.type : false;

    return (
        <form>
            <div className="wprm-admin-modal-bulk-edit-label">{ __wprm( 'Select an action to perform:' ) }</div>
            <div className="wprm-admin-modal-bulk-edit-actions">
                {
                    actionOptions.map( ( option ) => (
                        <div className="wprm-admin-modal-bulk-edit-action" key={ option.value }>
                            <input
                                type="radio"
                                value={ option.value }
                                name="wprm-admin-radio-bulk-edit-action-idea"
                                id={ `wprm-admin-radio-bulk-edit-action-idea-${ option.value }` }
                                checked={ selectedAction === option.value }
                                onChange={ () => {
                                    props.onActionChange( {
                                        type: option.value,
                                        options: option.default,
                                    } );
                                } }
                            />
                            <label htmlFor={ `wprm-admin-radio-bulk-edit-action-idea-${ option.value }` }>{ option.label }</label>
                        </div>
                    ) )
                }
            </div>
            {
                selectedAction && false !== props.action.options
                &&
                <Fragment>
                    <div className="wprm-admin-modal-bulk-edit-label">{ __wprm( 'Action options:' ) }</div>
                    <div className="wprm-admin-modal-bulk-edit-options">
                        {
                            'change-status' === selectedAction
                            &&
                            <FieldDropdown
                                options={ ideaStatusOptions }
                                value={ props.action.options }
                                onChange={ ( options ) => {
                                    props.onActionChange( {
                                        ...props.action,
                                        options,
                                    } );
                                } }
                                width={ 300 }
                            />
                        }
                        {
                        }
                    </div>
                </Fragment>
            }
        </form>
    );
};

export default ActionsIdea;
