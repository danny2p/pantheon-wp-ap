import ReactDOM from 'react-dom';
import React from 'react';

import App from './admin-modal/App';

let appContainer = document.getElementById( 'wprm-admin-modal' );

if (appContainer) {
	ReactDOM.render(
    	<App
			ref={(app) => {window.WPRM_Modal = app}}
		/>,
		appContainer
	);
}
