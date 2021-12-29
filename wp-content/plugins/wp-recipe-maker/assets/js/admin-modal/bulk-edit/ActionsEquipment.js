import React, { Fragment } from 'react';

import FieldText from '../fields/FieldText';
import FieldRadio from '../fields/FieldRadio';
import { __wprm } from 'Shared/Translations';
 
const ActionsEquipment = (props) => {
    const selectedAction = props.action ? props.action.type : false;
    const actionOptions = [
        { value: 'change-link', label: __wprm( 'Change Link' ), default: '' },
        { value: 'change-nofollow', label: __wprm( 'Change Link Nofollow' ), default: 'default' },
        { value: 'change-html', label: __wprm( 'Change Affiliate HTML' ), default: '' },
        { value: 'export', label: __wprm( 'Export Equipment' ), default: false, required: 'premium' },
        { value: 'delete', label: __wprm( 'Delete Equipment' ), default: false },
    ];

    return (
        <form>
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
            {
                selectedAction && false !== props.action.options
                &&
                <Fragment>
                    <div className="wprm-admin-modal-bulk-edit-label">{ __wprm( 'Action options:' ) }</div>
                    <div className="wprm-admin-modal-bulk-edit-options">
                        {
                            'change-link' === selectedAction
                            &&
                            <FieldText
                                name="equipment-link"
                                value={props.action.options}
                                placeholder={ __wprm( 'Equipment Link' ) }
                                onChange={(group) => {
                                    const newAction = {
                                        ...props.action,
                                        options: group,
                                    }
                
                                    props.onActionChange(newAction);
                                }}
                            />
                        }
                        {
                            'change-nofollow' === selectedAction
                            &&
                            <FieldRadio
                                id="nofollow"
                                options={wprm_admin_modal.options.equipment_link_nofollow}
                                value={props.action.options}
                                onChange={(value) => {
                                    const newAction = {
                                        ...props.action,
                                        options: value,
                                    }
                
                                    props.onActionChange(newAction);
                                }}
                            />
                        }
                        {
                            'change-html' === selectedAction
                            &&
                            <FieldText
                                name="equipment-html"
                                value={props.action.options}
                                placeholder={ __wprm( 'Affiliate HTML' ) }
                                onChange={(group) => {
                                    const newAction = {
                                        ...props.action,
                                        options: group,
                                    }
                
                                    props.onActionChange(newAction);
                                }}
                            />
                        }
                    </div>
                </Fragment>
            }
        </form>
    );
}
export default ActionsEquipment;