import React from 'react';
 
const Footer = (props) => {
    return (
        <div className="wprm-admin-dashboard-block-footer-container">
            {
                props.hasOwnProperty( 'title' )
                &&
                <div className="wprm-admin-dashboard-block-footer-title">{ props.title }</div>
            }
            <div className="wprm-admin-dashboard-block-footer">{ props.children }</div>
        </div>
    );
}
export default Footer;