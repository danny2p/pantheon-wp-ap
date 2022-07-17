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
						Transforms.unwrapNodes(editor, { match: n => n.type === props.type });
					}}
				>{ props.IconRemove() }</span>
				:
				<span
					className="wprm-admin-modal-toolbar-button"
					onMouseDown={ (event) => {
						event.preventDefault();

						const { selection } = editor;
						let isCollapsed = selection && Range.isCollapsed(selection);

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