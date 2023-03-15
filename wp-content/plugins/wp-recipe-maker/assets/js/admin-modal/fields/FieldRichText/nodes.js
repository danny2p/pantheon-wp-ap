import React from 'react';

export const Element = ({ attributes, children, element }) => {
	switch (element.type) {
		case 'link':
			let rel = [];

			if ( element.noFollow ) { rel.push( 'nofollow' ) }
			if ( element.sponsored ) { rel.push( 'sponsored' ) }

			return (
				<a
					href={ element.url }
					target={ element.newTab ? '_blank' : null }
					rel={ rel.length > 0 ? rel.join( ' ' ) : null }
					{...attributes}
				>{children}</a>
			)
		case 'affiliate-link':
			return (
				<a
					href={ element.url }
					data-eafl-id={ element.id }
					className="eafl-link"
					{...attributes}
				>{children}</a>
			)
		case 'code':
			return <wprm-code>{children}</wprm-code>
		case 'temperature':
			let icon = null;
			if ( element.icon && wprm_admin.temperature.icons.hasOwnProperty( element.icon ) ) {
				icon = (
					<img
						src={ wprm_admin.temperature.icons[ element.icon ].url }
						className="wprm-temperature-icon"
						contentEditable={false}
					/>
				)
			}

			let unit = null;
			if ( element.unit ) {
				unit = <span contentEditable={false}> °{ element.unit }</span>;
			}
			return <wprm-temperature
						icon={ element.icon }
						unit={ element.unit }
						help={ element.help }
					>{ icon }{ children }{ unit }</wprm-temperature>
		case 'ingredient':
			return (
				<wprm-ingredient
					uid={ element.uid }
					removed={ element.removed ? '1' : '0' }
				>{children}</wprm-ingredient>
			)
		default:
			return <p {...attributes}>{children}</p>
	}
}
	
export const Leaf = ({ attributes, children, leaf }) => {
	if (leaf.bold) {
		children = <strong>{children}</strong>
	}

	if (leaf.italic) {
		children = <em>{children}</em>
	}

	if (leaf.underline) {
		children = <u>{children}</u>
	}

	if (leaf.subscript) {
			children = <sub>{children}</sub>
	}

	if (leaf.superscript) {
			children = <sup>{children}</sup>
	}

	return <span {...attributes}>{children}</span>
}