import React from 'react';
import ReactDOM from 'react-dom';
 
const Toolbar = (props) => {
    const toolbarContainer = document.getElementById( 'wprm-admin-modal-toolbar-container' );

    if ( ! toolbarContainer ) {
        return null;
    } else {
        return ReactDOM.createPortal(
            <div
                className="wprm-admin-modal-toolbar"
                onMouseDown={ (event) => {
                    event.preventDefault();
                }}
            >
                { props.children }
            </div>,
            toolbarContainer,
        );
    }
}
export default Toolbar;