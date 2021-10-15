import React from 'react';
import PropTypes from 'prop-types';
import { Element } from 'react-scroll';

import Helpers from '../general/Helpers';
import RequiredLabel from './RequiredLabel';

import SettingCode from './setting/Code';
import SettingColor from './setting/Color';
import SettingDropdown from './setting/Dropdown';
import SettingDropdownMultiselect from './setting/DropdownMultiselect';
import SettingDropdownRecipe from './setting/DropdownRecipe';
import SettingDropdownTemplateLegacy from './setting/DropdownTemplateLegacy';
import SettingDropdownTemplateModern from './setting/DropdownTemplateModern';
import SettingEmail from './setting/Email';
import SettingNumber from './setting/Number';
import SettingRichTextarea from './setting/RichTextarea';
import SettingText from './setting/Text';
import SettingTextarea from './setting/Textarea';
import SettingToggle from './setting/Toggle';
import SettingUnitConversionUnits from './special/UnitConversionUnits';

import InformationButton from './information/Button';

const settingTypes = {
    code: SettingCode,
    color: SettingColor,
    dropdown: SettingDropdown,
    dropdownMultiselect: SettingDropdownMultiselect,
    dropdownRecipe: SettingDropdownRecipe,
    dropdownTemplateLegacy: SettingDropdownTemplateLegacy,
    dropdownTemplateModern: SettingDropdownTemplateModern,
    email: SettingEmail,
    number: SettingNumber,
    richTextarea: SettingRichTextarea,
    text: SettingText,
    textarea: SettingTextarea,
    toggle: SettingToggle,
    unitConversionUnits: SettingUnitConversionUnits,
}
const informationTypes = {
    button: InformationButton,
}

const Setting = (props) => {
    const SettingComponent = settingTypes.hasOwnProperty(props.setting.type) ? settingTypes[props.setting.type] : false;
    const InformationComponent = informationTypes.hasOwnProperty(props.setting.type) ? informationTypes[props.setting.type] : false;
    
    const displayValue = SettingComponent ? Helpers.beforeSettingDisplay(props.setting.id, props.settings) : false;

    // Special Settings.
    if ( 'unitConversionUnits' === props.setting.type ) {
        return (
            <SettingComponent
                setting={props.setting}
                settingsChanged={props.settingsChanged}
                onValueChange={(value) => {
                    const saveValue = Helpers.beforeSettingSave(value, props.setting.id, props.settings);
                    return props.onSettingChange(props.setting.id, saveValue)
                }}
                value={displayValue}
            />
        )
    }

    return (
        <Element className="wprm-setting-container" name={props.setting.id} >
            <div className="wprm-setting-label-container">
                <span className="wprm-setting-label">
                    <RequiredLabel object={props.setting} />{props.setting.name}
                </span>
                <span className="wprm-setting-description">{props.setting.description}</span>
                {
                    props.setting.hasOwnProperty('documentation')
                    ?
                    <a href={props.setting.documentation} target="_blank" className="wprm-setting-documentation">Learn More</a>
                    :
                    null
                }
            </div>
            <div className="wprm-setting-input-container">
                {
                    SettingComponent ?
                    <SettingComponent
                        setting={props.setting}
                        settingsChanged={props.settingsChanged}
                        onValueChange={(value) => {
                            const saveValue = Helpers.beforeSettingSave(value, props.setting.id, props.settings);
                            return props.onSettingChange(props.setting.id, saveValue)
                        }}
                        value={displayValue}
                    />
                    :
                    null
                }
                {
                    InformationComponent ?
                    <InformationComponent
                        setting={props.setting}
                        settingsChanged={props.settingsChanged}
                    />
                    :
                    null
                }
            </div>
        </Element>
    );
}

Setting.propTypes = {
    settings: PropTypes.object.isRequired,
    setting: PropTypes.object.isRequired,
    onSettingChange: PropTypes.func.isRequired,
    settingsChanged: PropTypes.bool.isRequired,
}

export default Setting;