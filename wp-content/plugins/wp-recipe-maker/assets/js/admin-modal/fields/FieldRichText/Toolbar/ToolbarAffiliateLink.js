import React from 'react';
import { Editor } from 'slate';
import { useSlate } from 'slate-react'

import { __wprm } from 'Shared/Translations';
import Icon from 'Shared/Icon';
import Spacer from './Spacer';
 
const ToolbarAffiliateLink = () => {
	const editor = useSlate();
	const [block] = Editor.nodes(editor, { match: n => n.type === 'affiliate-link' })
	if ( ! block ) {
		return null;
	}

	const link = block[0];

	return (
		<div className="wprm-admin-modal-toolbar-link">
			<Icon
				type="eafl-link"
			/>
			<span>#{ link.id }</span>
			<Spacer/>
			<Icon
				type="link"
			/>
			<span>{ link.url }</span>
		</div>
	);
}
export default ToolbarAffiliateLink;