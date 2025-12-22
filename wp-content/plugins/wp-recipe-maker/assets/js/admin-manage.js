import { createRoot } from 'react-dom/client';
import React from 'react';
import { HashRouter } from 'react-router-dom';

import App from './admin-manage/App';

let appContainer = document.getElementById( 'wprm-admin-manage' );

if (appContainer) {
	const root = createRoot(appContainer);
	root.render(
		<HashRouter
			hashType="noslash"
		>
    	    <App/>
  	    </HashRouter>
	);
}