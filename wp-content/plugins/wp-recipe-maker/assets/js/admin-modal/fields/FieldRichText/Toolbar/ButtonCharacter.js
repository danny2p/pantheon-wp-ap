import React from 'react';
import { Editor } from 'slate';
import { useSlate } from 'slate-react'

import Tooltip from 'Shared/Tooltip';

const ButtonCharacter = (props) => {
	const editor = useSlate();

	return (
		<span
			className="wprm-admin-modal-toolbar-button"
			onMouseDown={ (event) => {
				event.preventDefault();
				Editor.insertText( editor, props.character );
			}}
		>
			<Tooltip
				content={ props.title }
			>
				<span className="wprm-admin-modal-toolbar-button-character">{ props.character }</span>
			</Tooltip>
		</span>
	);
}
export default ButtonCharacter;