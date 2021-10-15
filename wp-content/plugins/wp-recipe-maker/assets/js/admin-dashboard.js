import ReactDOM from 'react-dom';
import React from 'react';

import App from './admin-dashboard/App';

let appContainer = document.getElementById( 'wprm-admin-dashboard' );

if (appContainer) {
	ReactDOM.render(
		<App/>,
		appContainer
	);
}