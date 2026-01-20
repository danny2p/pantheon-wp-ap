import { createRoot } from 'react-dom/client';
import React from 'react';

import App from './admin-modal/App';

let appContainer = document.getElementById( 'wprm-admin-modal' );

if (appContainer) {
	const root = createRoot(appContainer);
	root.render(
    	<App
			ref={(app) => {
				window.WPRM_Modal = app;
				// Expose secondary modal methods
				window.WPRM_Modal.openSecondary = app.openSecondary;
				window.WPRM_Modal.closeSecondary = app.closeSecondary;
				window.WPRM_Modal.close = app.close;
				window.WPRM_Modal.closeIfAllowed = app.closeIfAllowed;
				
			}}
		/>
	);
}
