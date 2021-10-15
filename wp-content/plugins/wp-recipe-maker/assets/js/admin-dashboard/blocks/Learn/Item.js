import React, { Fragment } from 'react';
 
const Item = (props) => {
    return (
        <div className="wprm-admin-dashboard-learn-section-item">
            {
                props.hasOwnProperty( 'url' )
                ?
                <a href={ props.url } target="_blank">{ props.children }</a>
                :
                props.children
            }
        </div>
    );
}
export default Item;