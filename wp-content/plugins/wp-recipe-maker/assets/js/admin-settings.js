import { createRoot } from 'react-dom/client';
import React from 'react';
import App from './admin-settings/App';

const container = document.getElementById( 'wprm-settings' );
if (container) {
	const root = createRoot(container);
	root.render(<App/>);
}