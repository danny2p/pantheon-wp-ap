import React, { Fragment } from 'react';
import { Editor, Range, Transforms } from 'slate';
import { useSlate } from 'slate-react'

import { __wprm } from 'Shared/Translations';
import Icon from 'Shared/Icon';

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

const ButtonAffiliateLink = () => {
	// Only show when Easy Affiliate Links is active.
	if ( ! window.hasOwnProperty( 'EAFL_Modal' ) ) {
		return null;
	}

	const editor = useSlate();

	const [inline] = Editor.nodes(editor, { match: n => n.type === 'affiliate-link' })
	const isActive = !!inline;

	return (
		<Fragment>
			<span
				className={ `wprm-admin-modal-toolbar-button${ isActive ? ' wprm-admin-modal-toolbar-button-active' : '' }` }
				onMouseDown={ (event) => {
					event.preventDefault();

					const { selection } = editor;
					const isCollapsed = selection && Range.isCollapsed(selection);

					let selectedText = '';
					if ( ! isCollapsed ) {
						if ( window.getSelection ) {
							selectedText = window.getSelection().toString();
						} else if ( document.selection && document.selection.type != "Control" ) {
							selectedText = document.selection.createRange().text;
						}
					}

					if ( isActive ) {
						const node = inline[0];

						EAFL_Modal.open('edit', {
							linkId: node.id,
							saveCallback: (link) => {
								if ( node.url !== link.url ) {
									editNode( editor, inline, 'url', link.url );
								}
							},
						});
					} else {
						EAFL_Modal.open('insert', {
							insertCallback: ( link, text ) => {				
								if ( ! text ) {
									text = 'affiliate link';
								}
	
								let node = {
									type: 'affiliate-link',
									children: isCollapsed ? [{ text }] : [],
									url: link.url,
									id: link.id,
								};
	
								if (isCollapsed) {
									Transforms.insertNodes(editor, node)
								} else {
									Transforms.select(editor, selection)
	
									Transforms.wrapNodes(editor, node, { split: true })
									Transforms.collapse(editor, { edge: 'end' })
								}
							},
							selectedText,
						});
					}
				}}
			>
				<Icon type="eafl-link" title={ isActive ? __wprm( 'Edit Affiliate Link' ) : __wprm( 'Add Affiliate Link' ) } />
			</span>
			{
				isActive
				&&
				<span
					className="wprm-admin-modal-toolbar-button wprm-admin-modal-toolbar-button-active"
					onMouseDown={ (event) => {
						event.preventDefault();
						Transforms.unwrapNodes(editor, { match: n => n.type === 'affiliate-link' });
					}}
				>
					<Icon type="eafl-unlink" title={ __wprm( 'Remove Affiliate Link' ) } />
				</span>
			}
		</Fragment>
	);
}
export default ButtonAffiliateLink;