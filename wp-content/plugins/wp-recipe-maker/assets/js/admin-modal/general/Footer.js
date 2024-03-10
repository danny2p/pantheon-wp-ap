import React, { Fragment } from 'react';

import Loader from 'Shared/Loader';
 
const Footer = (props) => {
    const alwaysShow = props.hasOwnProperty( 'alwaysShow' ) && typeof props.alwaysShow === 'function' ? props.alwaysShow : () => {};

    return (
        <div className="wprm-admin-modal-footer">
            {
                props.savingChanges
                ?
                <Fragment>
                    { alwaysShow() }<Loader/>
                </Fragment>
                :
                <Fragment>
                    { alwaysShow() }{ props.children }
                </Fragment>
            }
        </div>
    );
}
export default Footer;