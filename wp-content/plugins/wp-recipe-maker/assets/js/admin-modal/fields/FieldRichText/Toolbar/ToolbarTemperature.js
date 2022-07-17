import React from 'react';
import { Editor, Transforms } from 'slate';
import { useSlate } from 'slate-react'

import { __wprm } from 'Shared/Translations';
import Icon from 'Shared/Icon';
import Spacer from './Spacer';

const editNode = ( editor, block, field, value ) => {
	const temperature = block[0];
	const path = block[1];

	const properties = {
		[field]: temperature[field],
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
 
const ToolbarTemperature = (props) => {
	const editor = useSlate();
	const [block] = Editor.nodes(editor, { match: n => n.type === 'temperature' })
	if ( ! block ) {
		return null;
	}

	const temperature = block[0];

	return (
		<div className="wprm-admin-modal-toolbar-temperature">
			<span className="wprm-admin-modal-toolbar-temperature-label">Icon:</span>
			{
				Object.keys( wprm_admin.temperature.icons ).map( ( key, index ) => {
					const icon = wprm_admin.temperature.icons.hasOwnProperty( key ) ? wprm_admin.temperature.icons[ key ] : false;

					if ( ! icon ) {
						return null;
					}

					return (
						<img
							src={ icon.url }
							key={ index }
							className={ `wprm-admin-modal-toolbar-temperature-icon${ temperature.icon === key ? ' wprm-admin-modal-toolbar-temperature-icon-selected' : '' }` }
							onClick={ () => {
								if ( temperature.icon === key ) {
									editNode( editor, block, 'icon', '' );
								} else {
									editNode( editor, block, 'icon', key );
								}
							} }
						/>
					)
				} )
			}
			<Spacer />
			<span className="wprm-admin-modal-toolbar-temperature-label">Unit:</span>
			<Icon
				type={ 'F' === temperature.unit ? 'checkbox-checked' : 'checkbox-empty' }
				onClick={() => editNode( editor, block, 'unit', 'F' ) }
			/>
			<span
				className="wprm-admin-modal-toolbar-temperature-value"
				onMouseDown={ () => editNode( editor, block, 'unit', 'F' ) }
			> °F</span>
			<Spacer />
			<Icon
				type={ 'C' === temperature.unit ? 'checkbox-checked' : 'checkbox-empty' }
				onClick={() => editNode( editor, block, 'unit', 'C' ) }
			/>
			<span
				className="wprm-admin-modal-toolbar-temperature-value"
				onMouseDown={ () => editNode( editor, block, 'unit', 'C' ) }
			> °C</span>
			<Spacer />
			<span className="wprm-admin-modal-toolbar-temperature-label">Tooltip:</span>
			<span
				className="wprm-admin-modal-toolbar-temperature-value"
				onMouseDown={ () => {
					const help = window.prompt( __wprm( 'Temperature tooltip (e.g. Fan Assisted Heating):' ), temperature.help );

					if ( help ) {
						editNode( editor, block, 'help', help );
					} else {
						editNode( editor, block, 'help', '' );
					}
				} }
			>{ temperature.help ? temperature.help : __wprm( 'Click to set an optional tooltip' ) }</span>
		</div>
	);
}
export default ToolbarTemperature;