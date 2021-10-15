import React from 'react';

import { __wprm } from 'Shared/Translations';
import Icon from 'Shared/Icon';
 
const Header = (props) => {
    return (
        <div className="wprm-admin-modal-header">
            <h2>{ props.children }</h2>
            <div
                className="wprm-admin-modal-close"
                onClick={props.onCloseModal}
            >
                <Icon
                    type="close"
                    title={ __wprm( 'Close' ) }
                />
            </div>
        </div>
    );
}
export default Header;