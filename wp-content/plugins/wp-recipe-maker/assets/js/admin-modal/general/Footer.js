import React, { Fragment } from 'react';

import Loader from 'Shared/Loader';
 
const Footer = (props) => {
    const alwaysShow = props.hasOwnProperty( 'alwaysShow' ) && typeof props.alwaysShow === 'function' ? props.alwaysShow : () => {};
    const hasLeftActions = !! props.leftActions;
    const leftActions = 'function' === typeof props.leftActions ? props.leftActions() : props.leftActions;

    return (
        <div className={ `wprm-admin-modal-footer${ hasLeftActions ? ' wprm-admin-modal-footer-has-left' : '' }` }>
            {
                hasLeftActions
                &&
                <div className="wprm-admin-modal-footer-left">
                    { leftActions }
                </div>
            }
            <div className="wprm-admin-modal-footer-right">
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
        </div>
    );
}
export default Footer;
