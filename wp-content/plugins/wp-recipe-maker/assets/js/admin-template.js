import ReactDOM from 'react-dom';
import React from 'react';
import App from './admin-template/App';

import './public/smooth-scroll';

let appContainer = document.getElementById( 'wprm-template' );

if (appContainer) {
	ReactDOM.render(
		<App/>,
		appContainer
	);
}