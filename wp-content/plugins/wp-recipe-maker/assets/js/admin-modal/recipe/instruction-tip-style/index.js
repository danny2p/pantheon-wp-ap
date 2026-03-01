import React, { Fragment, useMemo, useState } from 'react';
import SVG from 'react-inlinesvg';

import '../../../../css/admin/modal/recipe/instruction-tip-style.scss';

import Header from '../../general/Header';
import Footer from '../../general/Footer';
import { __wprm } from 'Shared/Translations';

const noIconValue = '__none__';
const defaultIconValue = 'lightbulb';
const defaultTextColorValue = '#000000';
const isValidHexColor = (value) => /^#[0-9a-f]{3,6}$/i.test(value);
const isKnownIcon = (icons, value) => value && icons && icons.hasOwnProperty( value );
const validTipStyles = [ 'left-border-straight', 'left-border-rounded', 'filled', 'outline', 'banner' ];

const getPreviewText = (text) => {
    if ( ! text ) {
        return '';
    }

    return text.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
};

const InstructionTipStyle = (props) => {
    const allIcons = wprm_admin_modal && wprm_admin_modal.icons ? wprm_admin_modal.icons : {};
    const icons = useMemo(() => Object.keys( allIcons ).sort().map( ( id ) => allIcons[id] ), [ allIcons ]);

    const initialIcon = props.tip_icon || props.args?.tip_icon || '';
    const initialStyleRaw = props.tip_style || props.args?.tip_style || '';
    const normalizedInitialStyle = 'left-border' === initialStyleRaw ? 'left-border-straight' : initialStyleRaw;
    const initialStyle = validTipStyles.includes( normalizedInitialStyle ) ? normalizedInitialStyle : '';
    const initialAccentRaw = props.tip_accent || props.args?.tip_accent || '';
    const initialAccent = isValidHexColor( initialAccentRaw ) ? initialAccentRaw : '';
    const initialTextColorRaw = props.tip_text_color || props.args?.tip_text_color || '';
    const initialTextColor = isValidHexColor( initialTextColorRaw ) ? initialTextColorRaw : '';
    const initialTipText = props.tip_text || props.args?.tip_text || '';
    const showTipTextInput = !! ( props.show_tip_text_input || props.args?.show_tip_text_input );
    const initialIconMode = initialIcon && noIconValue === initialIcon.toLowerCase() ? 'none' : ( ! initialIcon ? 'default' : ( isKnownIcon( allIcons, initialIcon ) ? 'pick' : 'custom' ) );

    const [ iconMode, setIconMode ] = useState( initialIconMode );
    const [ selectedIcon, setSelectedIcon ] = useState( isKnownIcon( allIcons, initialIcon ) ? initialIcon : '' );
    const [ customIcon, setCustomIcon ] = useState( isKnownIcon( allIcons, initialIcon ) ? '' : initialIcon );
    const [ tipStyle, setTipStyle ] = useState( initialStyle );
    const [ tipAccent, setTipAccent ] = useState( initialAccent );
    const [ tipTextColor, setTipTextColor ] = useState( initialTextColor );
    const [ tipText, setTipText ] = useState( initialTipText );
    const usesDefaultStyle = ! tipStyle;
    const usesDefaultIcon = 'default' === iconMode;
    const usesDefaultAccent = ! tipAccent;
    const usesDefaultTextColor = ! tipTextColor;
    const hasDefaultOptions = usesDefaultStyle || usesDefaultIcon || usesDefaultAccent || usesDefaultTextColor;

    const tipIcon = 'none' === iconMode ? noIconValue : ( 'pick' === iconMode ? selectedIcon : ( 'custom' === iconMode ? customIcon.trim() : '' ) );
    const selectedIconData = tipIcon && isKnownIcon( allIcons, tipIcon ) ? allIcons[tipIcon] : false;
    const defaultIconData = isKnownIcon( allIcons, defaultIconValue ) ? allIcons[defaultIconValue] : false;
    const previewIconData = 'none' === iconMode ? false : ( selectedIconData ? selectedIconData : ( 'default' === iconMode ? defaultIconData : false ) );
    const previewIconUrl = previewIconData ? previewIconData.url : ( 'custom' === iconMode ? customIcon.trim() : '' );
    const previewIconName = previewIconData ? previewIconData.name : __wprm( 'Tip Icon' );
    const previewStyle = validTipStyles.includes( tipStyle ) ? tipStyle : 'left-border-straight';
    const previewAccent = tipAccent && isValidHexColor( tipAccent ) ? tipAccent : '#2b6cb0';
    const previewTextColor = tipTextColor && isValidHexColor( tipTextColor ) ? tipTextColor : defaultTextColorValue;
    const previewIconRenderKey = `${ previewIconUrl }|${ previewAccent }`;
    const previewText = getPreviewText( tipText ) || __wprm( 'Tip to clarify this instruction step.' );
    const title = props.title || props.args?.title || __wprm( 'Change Tip Style' );
    const saveLabel = props.save_button || props.args?.save_button || __wprm( 'Save' );

    const save = () => {
        const onSave = props.onSave || props.args?.onSave;

        if ( onSave ) {
            onSave({
                tip_icon: tipIcon,
                tip_style: tipStyle,
                tip_accent: tipAccent,
                tip_text_color: tipTextColor,
                tip_text: tipText,
            });
        }

        props.maybeCloseModal();
    };

    return (
        <Fragment>
            <Header
                onCloseModal={ props.maybeCloseModal }
            >
                { title }
            </Header>
            <div className="wprm-admin-modal-instruction-tip-style-container">
                <div className="wprm-admin-modal-instruction-tip-style-control">
                    <label>{ __wprm( 'Tip Style' ) }</label>
                    <select
                        value={ tipStyle }
                        onChange={ (e) => setTipStyle( e.target.value ) }
                    >
                        <option value="">{ __wprm( 'Use default style' ) }</option>
                        <option value="left-border-straight">{ __wprm( 'Left Border Straight' ) }</option>
                        <option value="left-border-rounded">{ __wprm( 'Left Border Rounded' ) }</option>
                        <option value="filled">{ __wprm( 'Filled' ) }</option>
                        <option value="outline">{ __wprm( 'Outline' ) }</option>
                        <option value="banner">{ __wprm( 'Banner' ) }</option>
                    </select>
                </div>
                <div className="wprm-admin-modal-instruction-tip-style-control">
                    <label>{ __wprm( 'Tip Icon' ) }</label>
                    <select
                        value={ iconMode }
                        onChange={ (e) => {
                            const nextMode = e.target.value;
                            setIconMode( nextMode );

                            if ( 'pick' === nextMode && ! selectedIcon && icons.length > 0 ) {
                                setSelectedIcon( icons[0].id );
                            }
                        } }
                    >
                        <option value="default">{ __wprm( 'Use default icon' ) }</option>
                        <option value="none">{ __wprm( 'No icon' ) }</option>
                        <option value="pick">{ __wprm( 'Pick custom icon' ) }</option>
                        <option value="custom">{ __wprm( 'Set custom icon' ) }</option>
                    </select>
                    {
                        'pick' === iconMode
                        &&
                        <div className="wprm-admin-modal-instruction-tip-style-icon-picker">
                            {
                                icons.map( ( icon ) => (
                                    <button
                                        type="button"
                                        key={ icon.id }
                                        className={ `wprm-admin-modal-instruction-tip-style-icon-picker-item${ selectedIcon === icon.id ? ' wprm-admin-modal-instruction-tip-style-icon-picker-item-selected' : '' }` }
                                        onClick={ () => setSelectedIcon( icon.id ) }
                                        title={ icon.name }
                                    >
                                        <img
                                            src={ icon.url }
                                            alt={ icon.name }
                                        />
                                    </button>
                                ) )
                            }
                        </div>
                    }
                    {
                        'custom' === iconMode
                        &&
                        <input
                            type="text"
                            value={ customIcon }
                            className="wprm-admin-modal-instruction-tip-style-custom-input"
                            placeholder={ __wprm( 'Custom icon URL (SVG or image)' ) }
                            onChange={ (e) => setCustomIcon( e.target.value ) }
                        />
                    }
                </div>
                <div className="wprm-admin-modal-instruction-tip-style-control">
                    <label>{ __wprm( 'Tip Accent Color' ) }</label>
                    <div className="wprm-admin-modal-instruction-tip-style-control-accent">
                        <input
                            type="color"
                            value={ tipAccent || '#2b6cb0' }
                            onChange={ (e) => setTipAccent( e.target.value ) }
                        />
                        <button
                            type="button"
                            className="button button-small"
                            onClick={ () => setTipAccent( '' ) }
                            disabled={ ! tipAccent }
                        >
                            { __wprm( 'Use default accent color' ) }
                        </button>
                    </div>
                </div>
                <div className="wprm-admin-modal-instruction-tip-style-control">
                    <label>{ __wprm( 'Tip Text Color' ) }</label>
                    <div className="wprm-admin-modal-instruction-tip-style-control-accent">
                        <input
                            type="color"
                            value={ tipTextColor || defaultTextColorValue }
                            onChange={ (e) => setTipTextColor( e.target.value ) }
                        />
                        <button
                            type="button"
                            className="button button-small"
                            onClick={ () => setTipTextColor( '' ) }
                            disabled={ ! tipTextColor }
                        >
                            { __wprm( 'Use default text color' ) }
                        </button>
                    </div>
                </div>
                {
                    showTipTextInput
                    &&
                    <div className="wprm-admin-modal-instruction-tip-style-control">
                        <label>{ __wprm( 'Tip Text' ) }</label>
                        <input
                            type="text"
                            value={ tipText }
                            className="wprm-admin-modal-instruction-tip-style-text-input"
                            placeholder={ __wprm( 'Tip text' ) }
                            onChange={ (e) => setTipText( e.target.value ) }
                        />
                    </div>
                }
                <div className="wprm-admin-modal-instruction-tip-style-preview">
                    <div className="wprm-admin-modal-instruction-tip-style-preview-label">{ __wprm( 'Preview' ) }</div>
                    {
                        hasDefaultOptions
                        &&
                        <div className="wprm-admin-modal-instruction-tip-style-preview-disclaimer">{ __wprm( 'When using default options, the final tip style depends on your recipe template settings.' ) }</div>
                    }
                    <div
                        className={ `wprm-admin-modal-instruction-tip-style-preview-tip wprm-admin-modal-instruction-tip-style-preview-tip-style-${ previewStyle }` }
                        style={ {
                            '--wprm-admin-tip-preview-accent': previewAccent,
                            '--wprm-admin-tip-preview-text-color': previewTextColor,
                        } }
                    >
                        {
                            previewIconData
                            &&
                            <SVG
                                key={ previewIconRenderKey }
                                src={ previewIconData.url }
                                className="wprm-admin-modal-instruction-tip-style-preview-icon"
                                preProcessor={ ( code ) => code.replace(/#[0-9a-f]{3,6}/gi, previewAccent ) }
                            />
                        }
                        {
                            ! previewIconData && previewIconUrl
                            &&
                            <img
                                src={ previewIconUrl }
                                alt={ previewIconName }
                                className="wprm-admin-modal-instruction-tip-style-preview-icon"
                            />
                        }
                        <div className="wprm-admin-modal-instruction-tip-style-preview-text">{ previewText }</div>
                    </div>
                </div>
            </div>
            <Footer>
                <button
                    className="button button-primary"
                    onClick={ save }
                >
                    { saveLabel }
                </button>
                <button
                    className="button"
                    onClick={ props.maybeCloseModal }
                >
                    { __wprm( 'Cancel' ) }
                </button>
            </Footer>
        </Fragment>
    );
};
export default InstructionTipStyle;
