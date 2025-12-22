import { createRoot } from 'react-dom/client';
import React from 'react';

import App from './admin-faq/App';

let appContainer = document.getElementById( 'wprm-admin-faq' );

if (appContainer) {
	const root = createRoot(appContainer);
	root.render(<App/>);
}