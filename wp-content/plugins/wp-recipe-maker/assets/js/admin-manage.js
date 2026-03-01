import { createRoot } from 'react-dom/client';
import React from 'react';
import { HashRouter } from 'react-router-dom';

import App from './admin-manage/App';
import ExportCsvModal from './admin-manage/export-csv/Modal';
const { hooks } = WPRecipeMakerAdmin['wp-recipe-maker/dist/shared'];

hooks.addFilter( 'modal', 'wp-recipe-maker/manage-export-csv', ( modal ) => {
    modal['manage-export-csv'] = ExportCsvModal;
    return modal;
} );

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
