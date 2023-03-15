import React from 'react';
import { Editor, Transforms } from 'slate';
import { useSlate } from 'slate-react'

import { __wprm } from 'Shared/Translations';
 
const ToolbarInlineIngredient = (props) => {
	const editor = useSlate();
	const [block] = Editor.nodes(editor, { match: n => n.type === 'ingredient' })
	if ( ! block ) {
		return null;
	}

	const ingredient = block[0];

	return (
		<div className="wprm-admin-modal-toolbar-ingredient">
			<span className="wprm-admin-modal-toolbar-temperature-label">{ ingredient.removed ? __wprm( 'Removed Inline Ingredient' ) : __wprm( 'Inline Ingredient' ) }</span>
			<a
				href="#"
				className="wprm-admin-modal-toolbar-temperature-action"
				onMouseDown={
					(e) => {
						e.preventDefault();
						Transforms.unwrapNodes(editor, { match: n => n.type === 'ingredient' });
					}
				}
			>{ __wprm( 'Convert to regular text' ) }</a>
		</div>
	);
}
export default ToolbarInlineIngredient;