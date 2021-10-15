import React, { Fragment } from 'react';

import Loader from 'Shared/Loader';
 
const Footer = (props) => {
    return (
        <div className="wprm-admin-modal-footer">
            {
                props.savingChanges
                ?
                <Loader/>
                :
                <Fragment>
                    { props.children }
                </Fragment>
            }
        </div>
    );
}
export default Footer;