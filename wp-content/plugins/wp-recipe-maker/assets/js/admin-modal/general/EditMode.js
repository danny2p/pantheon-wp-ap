import React from 'react';


import Icon from 'Shared/Icon';

import '../../../css/admin/modal/general/edit-mode.scss';

const EditMode = (props) => {
    if ( ! props.modes ) {
        return null;
    }

    return (
        <div
            className="wprm-admin-modal-field-edit-mode-container"
        >
            {
                Object.keys( props.modes ).map((id, index) => {
                    const mode = props.modes[id];

                    // Optional help icon.
                    let helpIcon = null;
                    if ( mode.hasOwnProperty( 'help' ) ) {
                        helpIcon = (
                            <Icon
                                type="question"
                                title={ mode.help }
                                className="wprm-admin-icon-help"
                            />
                        );
                    }

                    return (
                        <a
                            href="#"
                            className={ `wprm-admin-modal-field-edit-mode${ id === props.mode ? ' wprm-admin-modal-field-edit-mode-selected' : '' }` }
                            onClick={(e) => {
                                e.preventDefault();
                                props.onModeChange( id );
                            }}
                            key={index}
                        >
                            { mode.label }{ helpIcon }
                        </a>
                    )
                })
            }
        </div>
    );
}
export default EditMode;