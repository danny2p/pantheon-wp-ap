import { createRoot } from 'react-dom/client';
import React from 'react';

import App from './admin-dashboard/App';

let appContainer = document.getElementById( 'wprm-admin-dashboard' );

if (appContainer) {
	const root = createRoot(appContainer);
	root.render(<App/>);
}