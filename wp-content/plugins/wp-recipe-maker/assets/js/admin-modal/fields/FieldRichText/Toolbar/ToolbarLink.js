import React from 'react';
import { Editor, Transforms } from 'slate';
import { useSlate } from 'slate-react'

import { __wprm } from 'Shared/Translations';
import Icon from 'Shared/Icon';
import Spacer from './Spacer';

const editHref = ( editor, block ) => {
	const link = block[0];

	const href = window.prompt( __wprm( 'Enter the URL of the link:' ), link.url );
	if ( href ) {
		editNode( editor, block, 'url', href );
	} else if ( '' === href ) {
		Transforms.unwrapNodes(editor, { match: n => n.type === 'link' });
	}
}

const toggleCheckbox = ( editor, block, field ) => {
	const link = block[0];
	const currentValue = link.hasOwnProperty( field ) ? link[field] : false;

	editNode( editor, block, field, ! currentValue );
}

const editNode = ( editor, block, field, value ) => {
	const link = block[0];
	const path = block[1];

	const properties = {
		[field]: link[field],
	};

	const newProperties = {
		[field]: value,
	}

	editor.apply({
		type: 'set_node',
		path,
		properties,
		newProperties,
	});
};
 
const ToolbarLink = (props) => {
	const editor = useSlate();
	const [block] = Editor.nodes(editor, { match: n => n.type === 'link' })
	if ( ! block ) {
		return null;
	}

	const link = block[0];

	return (
		<div className="wprm-admin-modal-toolbar-link">
			<Icon
				type="link"
				onClick={() => editHref( editor, block ) }
			/>
			<span
				className="wprm-admin-modal-toolbar-link-value"
				onMouseDown={ () => editHref( editor, block ) }
			>
				{ link.url }
			</span>
			<Spacer />
			<Icon
				type={ link.newTab ? 'checkbox-checked' : 'checkbox-empty' }
				onClick={() => toggleCheckbox( editor, block, 'newTab' ) }
			/>
			<span
				className="wprm-admin-modal-toolbar-link-value"
				onMouseDown={ () => toggleCheckbox( editor, block, 'newTab' ) }
			>{ __wprm( 'Open in new tab' ) }</span>
			<Spacer />
			<Icon
				type={ link.noFollow ? 'checkbox-checked' : 'checkbox-empty' }
				onClick={() => toggleCheckbox( editor, block, 'noFollow' ) }
			/>
			<span
				className="wprm-admin-modal-toolbar-link-value"
				onMouseDown={ () => toggleCheckbox( editor, block, 'noFollow' ) }
			>{ __wprm( 'Use nofollow' ) }</span>
			<Spacer />
			<Icon
				type={ link.sponsored ? 'checkbox-checked' : 'checkbox-empty' }
				onClick={() => toggleCheckbox( editor, block, 'sponsored' ) }
			/>
			<span
				className="wprm-admin-modal-toolbar-link-value"
				onMouseDown={ () => toggleCheckbox( editor, block, 'sponsored' ) }
			>{ __wprm( 'Use sponsored' ) }</span>
		</div>
	);
}
export default ToolbarLink;