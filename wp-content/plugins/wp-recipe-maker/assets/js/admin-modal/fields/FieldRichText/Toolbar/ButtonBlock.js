import React, { Fragment } from 'react';
import { Editor, Range, Transforms } from 'slate';
import { useSlate } from 'slate-react'

import { __wprm } from 'Shared/Translations';

const isBlockActive = ( editor, type ) => {
	const [inline] = Editor.nodes(editor, { match: n => n.type === type })
	return !!inline;
}

const ButtonBlock = (props) => {
	const editor = useSlate();
	const isActive = isBlockActive( editor, props.type );

	return (
		<Fragment>
			{
				isActive
				?
				<span
					className="wprm-admin-modal-toolbar-button wprm-admin-modal-toolbar-button-active"
					onMouseDown={ (event) => {
						event.preventDefault();
						
						// For headings, convert to paragraph instead of unwrapping
						if ( props.type.startsWith('heading-') ) {
							const [match] = Editor.nodes(editor, {
								match: n => n.type === props.type,
							});
							
							if (match) {
								const [node, path] = match;
								// Convert heading to paragraph, preserving children
								Transforms.setNodes(editor, {
									type: 'paragraph',
								}, { at: path });
								// Remove the level property if it exists
								Transforms.unsetNodes(editor, 'level', { at: path });
							}
						} else {
							// For other block types (like links, code), unwrap as before
							Transforms.unwrapNodes(editor, { match: n => n.type === props.type });
						}
					}}
				>{ props.IconRemove() }</span>
				:
				<span
					className="wprm-admin-modal-toolbar-button"
					onMouseDown={ (event) => {
						event.preventDefault();

						const { selection } = editor;
						let isCollapsed = selection && Range.isCollapsed(selection);

						// Handle headings without prompt - toggle like bold/italic
						if ( props.type.startsWith('heading-') ) {
							if (isCollapsed) {
								// Find the block node at the selection
								const match = Editor.above(editor, {
									match: n => Editor.isBlock(editor, n),
								});
								
								if (match) {
									const [node, path] = match;
									// Just change the block type to the new heading level
									// This works whether it's already a heading or a paragraph
									Transforms.setNodes(editor, {
										type: props.type,
										level: parseInt(props.type.replace('heading-', ''), 10),
									}, { at: path });
								}
							} else {
								// For selected text, check if selection is already in a heading
								const [headingMatch] = Editor.nodes(editor, {
									match: n => n.type && n.type.startsWith('heading-'),
								});
								
								if (headingMatch) {
									// Selection is in a heading, just change its type
									const [node, path] = headingMatch;
									Transforms.setNodes(editor, {
										type: props.type,
										level: parseInt(props.type.replace('heading-', ''), 10),
									}, { at: path });
								} else {
									// Wrap selected text in heading
									let node = {
										type: props.type,
										level: parseInt(props.type.replace('heading-', ''), 10),
										children: [],
									};
									Transforms.wrapNodes(editor, node, { split: true })
									Transforms.collapse(editor, { edge: 'end' })
								}
							}
							return;
						}

						let prompt = true;
						if ( 'link' === props.type ) {
							prompt = window.prompt( __wprm( 'Enter the URL of the link:' ) );
						}
						if ( 'code' === props.type && isCollapsed ) {
							prompt = window.prompt( __wprm( 'HTML or Shortcode:' ) );
						}
						if ( 'temperature' === props.type ) {
							if ( ! isCollapsed ) {
								Transforms.collapse(editor, { edge: 'end' })
								isCollapsed = true;
							}
							prompt = window.prompt( __wprm( 'Temperature value (e.g. 350):' ) );
						}

						if ( prompt ) {
							let node = {
								type: props.type,
								children: isCollapsed ? [{ text: '' }] : [],
							};

							switch ( props.type ) {
								case 'link':
									node.url = prompt;
									if ( isCollapsed ) {
										node.children = [{ text: prompt }];
									}
									break;
								case 'code':
									if ( isCollapsed ) {
										node.children = [{ text: prompt }];
									}
									break;
								case 'temperature':
									node.icon = '';
									node.unit = wprm_admin.temperature.default_unit;
									node.help = '';
									node.children = [{ text: prompt }];
									break;
								default:
									if ( isCollapsed ) {
										node.children = [{ text: props.type }];
									}
							}

							if (isCollapsed) {
								Transforms.insertNodes(editor, node)
							} else {
								Transforms.wrapNodes(editor, node, { split: true })
								Transforms.collapse(editor, { edge: 'end' })
							}
						}
					}}
				>{ props.IconAdd() }</span>
			}
		</Fragment>
	);
}
export default ButtonBlock;