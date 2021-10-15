import React from 'react';
import { __wprm } from 'Shared/Translations';
 
const SelectGroups = (props) => {
    return (
        <div className="wprm-admin-modal-field-text-import-groups">
            <p>{ __wprm( 'All of these will be imported. Use the checkbox to indicate group headers.' ) } </p>
            {
                props.value.map((field, index) => (
                    <div className="wprm-admin-modal-field-text-import-groups-field" key={index}>
                        <input
                            type="checkbox"
                            checked={ field.group }
                            onChange={(e) => {
                                let newFields = JSON.parse( JSON.stringify( props.value ) );
                                newFields[ index ].group = e.target.checked;
                                props.onChange(newFields);
                            } }
                        />
                        <input
                            type="text"
                            value={ field.text }
                            style={ field.group ? { fontWeight: 'bold' } : null }
                            onChange={(e) => {
                                let newFields = JSON.parse( JSON.stringify( props.value ) );
                                newFields[ index ].text = e.target.value;
                                props.onChange(newFields);
                            } }
                        />
                    </div>
                ))
            }
        </div>
    );
}
export default SelectGroups;