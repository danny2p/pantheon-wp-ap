import React, { Fragment, useMemo } from 'react';
import { Editor, Transforms } from 'slate';
import { useFocused, useSlate } from 'slate-react';

import Icon from 'Shared/Icon';
import { __wprm } from 'Shared/Translations';

import ModalToolbar from '../../../general/Toolbar';
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
	// Get editor and extract plain text for suggestions (much faster than serializing HTML).
	let editor;
	let value = '';
	if ( 'ingredient-unit' === props.type || 'ingredient' === props.type || 'equipment' === props.type ) {
		editor = useSlate();
		// Use useMemo to only recalculate when editor content actually changes.
		// Extract plain text instead of full HTML for better performance.
		value = useMemo(() => {
			return Editor.string(editor, []);
		}, [editor.children]);
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
	let showHeading = false;

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
		case 'ingredient-unit':
			if ( ! wprm_admin.addons.premium ) {
				hideLink = true;
			}
			break;
		case 'equipment':
		case 'ingredient':
			hideLink = true;
			break;
		case 'list':
			showHeading = true;
			break;
	}

	return (
		<ModalToolbar>
			<ToolbarAffiliateLink/>
			<ToolbarLink/>
			<ToolbarTemperature/>
			<ToolbarInlineIngredient/>
			{
				( 'ingredient-unit' === props.type || 'ingredient' === props.type || 'equipment' === props.type )
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
				{
					showHeading && (
						<span>
							<Spacer />
							<ButtonBlock
								type="heading-1"
								IconAdd={ () => <Icon type="heading-1" title={ __wprm( 'H1' ) } /> }
								IconRemove={ () => <Icon type="heading-1" title={ __wprm( 'Remove H1' ) } /> }
							/>
							<ButtonBlock
								type="heading-2"
								IconAdd={ () => <Icon type="heading-2" title={ __wprm( 'H2' ) } /> }
								IconRemove={ () => <Icon type="heading-2" title={ __wprm( 'Remove H2' ) } /> }
							/>
							<ButtonBlock
								type="heading-3"
								IconAdd={ () => <Icon type="heading-3" title={ __wprm( 'H3' ) } /> }
								IconRemove={ () => <Icon type="heading-3" title={ __wprm( 'Remove H3' ) } /> }
							/>
							<ButtonBlock
								type="heading-4"
								IconAdd={ () => <Icon type="heading-4" title={ __wprm( 'H4' ) } /> }
								IconRemove={ () => <Icon type="heading-4" title={ __wprm( 'Remove H4' ) } /> }
							/>
							<ButtonBlock
								type="heading-5"
								IconAdd={ () => <Icon type="heading-5" title={ __wprm( 'H5' ) } /> }
								IconRemove={ () => <Icon type="heading-5" title={ __wprm( 'Remove H5' ) } /> }
							/>
							<ButtonBlock
								type="heading-6"
								IconAdd={ () => <Icon type="heading-6" title={ __wprm( 'H6' ) } /> }
								IconRemove={ () => <Icon type="heading-6" title={ __wprm( 'Remove H6' ) } /> }
							/>
						</span>
					)
				}
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