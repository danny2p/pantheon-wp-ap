import React from 'react';
import Toggle from 'react-toggle';
import 'react-toggle/style.css'

import '../../../css/admin/dashboard/fields.scss';

const ToggleField = (props) => {
    return (
        <label className="wprm-admin-dashboard-toggle-container">
            <Toggle
                className="wprm-admin-dashboard-toggle"
                checked={ props.value }
                onChange={ (e) => props.onChange( e.target.checked ) }
            />
            <span className="wprm-admin-dashboard-toggle-label">{ props.children }</span>
        </label>
    );
}

export default ToggleField;