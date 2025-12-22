import React from 'react';
import PropTypes from 'prop-types';

import Helpers from '../general/Helpers';
import RequiredLabel from './RequiredLabel';
import Settings from './Settings';

const SettingsSubGroup = (props) => {
    // Check if the subgroup itself (name/description) matches the search
    const subgroupMatches = props.normalizedSearchQuery ? Helpers.subgroupNameOrDescriptionMatches(props.subgroup, props.normalizedSearchQuery) : false;
    // If either the parent group matched OR this subgroup matches, show all settings
    const showAllSettings = props.parentMatched || subgroupMatches;
    
    return (
        <div className="wprm-settings-subgroup">
            <h3 className="wprm-settings-subgroup-name">
                <RequiredLabel object={props.subgroup}/>
                {props.subgroup.name && (props.searchQuery ? Helpers.highlightText(props.subgroup.name, props.searchQuery) : props.subgroup.name)}
            </h3>
            {
                props.subgroup.hasOwnProperty('description')
                ?
                <div className="wprm-settings-subgroup-description">
                    {props.searchQuery ? Helpers.highlightText(props.subgroup.description, props.searchQuery) : props.subgroup.description}
                </div>
                :
                null
            }
            {
                props.subgroup.hasOwnProperty('documentation')
                ?
                <a href={props.subgroup.documentation} target="_blank" className="wprm-setting-documentation">{ props.subgroup.hasOwnProperty('documentation_text' ) ? props.subgroup.documentation_text : 'Learn More' }</a>
                :
                null
            }
            {
                props.subgroup.hasOwnProperty('settings')
                ?
                <Settings
                    outputSettings={props.subgroup.settings}
                    settings={props.settings}
                    onSettingChange={props.onSettingChange}
                    settingsChanged={props.settingsChanged}
                    searchQuery={props.searchQuery}
                    normalizedSearchQuery={props.normalizedSearchQuery}
                    parentMatched={showAllSettings}
                />
                :
                null
            }
        </div>
    );
}

SettingsSubGroup.propTypes = {
    subgroup: PropTypes.object.isRequired,
    settings: PropTypes.object.isRequired,
    onSettingChange: PropTypes.func.isRequired,
    settingsChanged: PropTypes.bool.isRequired,
    searchQuery: PropTypes.string.isRequired,
    normalizedSearchQuery: PropTypes.string.isRequired,
    parentMatched: PropTypes.bool,
}

export default SettingsSubGroup;