import React from 'react';
import { useSlate } from 'slate-react'

import Icon from 'Shared/Icon';

const ButtonMark = (props) => {
	const editor = useSlate();
	const isActive = props.isMarkActive( editor, props.type );

	return (
		<span
			className={ `wprm-admin-modal-toolbar-button${isActive ? ' wprm-admin-modal-toolbar-button-active' : ''}` }
			onMouseDown={ (event) => {
				event.preventDefault();
				props.toggleMark( editor, props.type );
			}}
		>
			<Icon
				type={ props.type }
				title={ props.title }
			/>
		</span>
	);
}
export default ButtonMark;