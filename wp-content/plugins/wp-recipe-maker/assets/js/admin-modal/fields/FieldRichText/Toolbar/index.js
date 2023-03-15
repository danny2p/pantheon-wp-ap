import React, { Fragment } from 'react';
import { Editor, Transforms } from 'slate';
import { useFocused, useSlate } from 'slate-react';
import { serialize } from '../html';

import Icon from 'Shared/Icon';
import { __wprm } from 'Shared/Translations';

import ModalToolbar from '../../../general/toolbar';
import ButtonAffiliateLink from './ButtonAffiliateLink';
import ButtonBlock from './ButtonBlock';
import ButtonCharacter from './ButtonCharacter';
import ButtonMark from './ButtonMark';
import ButtonWrap from './ButtonWrap';
import Spacer from './Spacer';
import ToolbarAffiliateLink from './ToolbarAffiliateLink';
import ToolbarInlineIngredient from './ToolbarInlineIngredient';
import ToolbarLink from './ToolbarLink';
import ToolbarTemperature from './ToolbarTemperature';
import ToolbarSuggest from './ToolbarSuggest';

const Toolbar = (props) => {
	// Get values for suggestions.
	let editor;
	let value = '';
	if ( 'ingredient' === props.type || 'equipment' === props.type ) {
		editor = useSlate();
		value = serialize( editor );
	}

	// Only show when focussed (needs to be after useSlate()).
	const focused = useFocused();
	if ( ! focused ) {
		return null;
	}

	// Hide some parts of the toolbar.
	const hidden = {
		visibility: 'hidden'
	};

	let hideStyling = false;
	let hideLink = false;

	if ( 'none' === props.type ) {
		return null;
	}

	switch( props.type ) {
		case 'no-styling':
			hideStyling = true;
			break;
		case 'no-link':
			hideLink = true;
			break;
		case 'equipment':
		case 'ingredient':
			hideLink = true;
			break;
	}

	return (
		<ModalToolbar>
			<ToolbarAffiliateLink/>
			<ToolbarLink/>
			<ToolbarTemperature/>
			<ToolbarInlineIngredient/>
			{
				( 'ingredient' === props.type || 'equipment' === props.type )
				&&
				<ToolbarSuggest
					value={ value }
					onSelect={ (value) => {
						// Select all, delete and insert.
						Transforms.deselect( editor );
                        Transforms.select( editor, {
                            path: [0,0],
                            offset: 0,
                        });
                        Transforms.move( editor, {
                            unit: 'line',
                            edge: 'end',
                        });
						Transforms.delete(editor);
						Editor.insertText( editor, value );
					}}
					type={ props.type }
				/>
			}
			<div className="wprm-admin-modal-toolbar-buttons">
				<span
					style={ hideStyling ? hidden : null }
				>
					<ButtonMark {...props} type="bold" title={ __wprm( 'Bold' ) } />
					<ButtonMark {...props} type="italic" title={ __wprm( 'Italic' ) } />
					<ButtonMark {...props} type="underline" title={ __wprm( 'Underline' ) } />
					<Spacer />
					<ButtonMark {...props} type="subscript" title={ __wprm( 'Subscript' ) } />
					<ButtonMark {...props} type="superscript" title={ __wprm( 'Superscript' ) } />
				</span>
				<Spacer />
				<span
					style={ hideLink ? hidden : null }
				>
					<ButtonBlock
						type="link"
						IconAdd={ () => <Icon type="link" title={ __wprm( 'Add Link' ) } /> }
						IconRemove={ () => <Icon type="unlink" title={ __wprm( 'Remove Link' ) } /> }
					/>
					<ButtonAffiliateLink />
				</span>
				<Spacer />
				<ButtonBlock
					type="code"
					IconAdd={ () => <Icon type="code" title={ __wprm( 'Add HTML or Shortcode' ) } /> }
					IconRemove={ () => <Icon type="code" title={ __wprm( 'Remove HTML or Shortcode' ) } /> }
				/>
				{
					'roundup' !== props.type
					&&
					<Fragment>
						<ButtonWrap
							before="[adjustable]"
							after="[/adjustable]"
							Icon={ () => <Icon type="adjustable" title={ __wprm( 'Add Adjustable Shortcode' ) } /> }
						/>
						<ButtonWrap
							before="[timer minutes=0]"
							after="[/timer]"
							Icon={ () => <Icon type="clock" title={ __wprm( 'Add Timer Shortcode' ) } /> }
						/>
						<ButtonBlock
							type="temperature"
							IconAdd={ () => <Icon type="temperature" title={ __wprm( 'Add Temperature' ) } /> }
							IconRemove={ () => <Icon type="temperature" title={ __wprm( 'Remove Temperature' ) } /> }
						/>
						<Spacer />
						<ButtonCharacter character="½" />
						<ButtonCharacter character="⅓" />
						<ButtonCharacter character="⅔" />
						<ButtonCharacter character="¼" />
						<ButtonCharacter character="¾" />
						<ButtonCharacter character="⅕" />
						<ButtonCharacter character="⅖" />
						<ButtonCharacter character="⅗" />
						<ButtonCharacter character="⅘" />
						<ButtonCharacter character="⅙" />
						<ButtonCharacter character="⅚" />
						<ButtonCharacter character="⅐" />
						<ButtonCharacter character="⅛" />
						<ButtonCharacter character="⅜" />
						<ButtonCharacter character="⅝" />
						<ButtonCharacter character="⅞" />
						<Spacer />
						<ButtonCharacter character="°" />
						<ButtonCharacter character="℉" />
						<ButtonCharacter character="℃" />
						<ButtonCharacter character="Ø" />
					</Fragment>
				}
			</div>
		</ModalToolbar>
	);
}
export default Toolbar;