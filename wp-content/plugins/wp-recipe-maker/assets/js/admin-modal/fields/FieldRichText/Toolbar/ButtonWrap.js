import React, { Fragment } from 'react';
import { Editor, Range, Transforms } from 'slate';
import { useSlate } from 'slate-react'

import { __wprm } from 'Shared/Translations';

const ButtonWrap = (props) => {
	const editor = useSlate();

	return (
		<span
			className="wprm-admin-modal-toolbar-button"
			onMouseDown={ (event) => {
				event.preventDefault();
				
				const before = props.hasOwnProperty( 'before' ) ? props.before : '';
				const after = props.hasOwnProperty( 'after' ) ? props.after : '';

				const { selection } = editor;
				const isCollapsed = selection && Range.isCollapsed(selection);

				if (isCollapsed) {
					editor.insertText( `${before}${after}` );
					Transforms.move( editor, { distance: after.length, reverse: true } );
				} else {
					let [start, end] = Range.edges(selection);

					// If in same block, adjust offset.
					if ( JSON.stringify( start.path ) === JSON.stringify( end.path ) ) {
						end = {
							...end,
							offset: end.offset + before.length,
						};
					}

					Transforms.insertText( editor, before, { at: start } );
					Transforms.insertText( editor, after, { at: end } );
					Transforms.collapse( editor, { edge: 'end' } );
				}
			}}
		>{ props.Icon() }</span>
	);
}
export default ButtonWrap;