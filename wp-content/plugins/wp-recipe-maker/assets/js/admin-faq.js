import ReactDOM from 'react-dom';
import React from 'react';

import App from './admin-faq/App';

let appContainer = document.getElementById( 'wprm-admin-faq' );

if (appContainer) {
	ReactDOM.render(
		<App/>,
		appContainer
	);
}