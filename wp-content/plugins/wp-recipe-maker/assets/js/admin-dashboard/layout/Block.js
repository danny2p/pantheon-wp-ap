import React from 'react';
 
const Block = (props) => {
    return (
        <div className="wprm-admin-dashboard-block-container">
            <div className="wprm-admin-dashboard-block-header">
                <div className="wprm-admin-dashboard-block-header-title">{ props.title }</div>
                {
                    props.hasOwnProperty( 'button' )
                    &&
                    <div className="wprm-admin-dashboard-block-header-button">
                        <button
                            className="button button-primary"
                            onClick={ () => { props.buttonAction(); } }
                        >{ props.button }</button>
                    </div>
                }
            </div>
            <div className="wprm-admin-dashboard-block">{ props.children }</div>
        </div>
    );
}
export default Block;