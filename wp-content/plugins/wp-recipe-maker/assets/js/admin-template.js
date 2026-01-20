import { createRoot } from 'react-dom/client';
import React from 'react';
import { HashRouter } from 'react-router-dom';
import App from './admin-template/App';

import './public/smooth-scroll';

let appContainer = document.getElementById( 'wprm-template' );

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