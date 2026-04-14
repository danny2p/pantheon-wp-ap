import React from 'react';
import PropTypes from 'prop-types';

import Helpers from '../general/Helpers';
import Settings from './Settings';
import SettingsSubGroup from './SettingsSubGroup';
import RequiredLabel from './RequiredLabel';

const SettingsGroup = (props) => {
    return (
        <div id={`wprm-settings-group-${props.group.id}`} className="wprm-settings-group">
            <RequiredLabel object={props.group}/>
            <h2 className="wprm-settings-group-name">
                {props.searchQuery ? Helpers.highlightText(props.group.name, props.searchQuery) : props.group.name}
            </h2>
            {
                props.group.hasOwnProperty('description')
                ?
                <div className="wprm-settings-group-description">
                    {props.searchQuery ? Helpers.highlightText(props.group.description, props.searchQuery) : props.group.description}
                </div>
                :
                null
            }
            {
                props.group.hasOwnProperty('documentation')
                ?
                <a href={props.group.documentation} target="_blank" className="wprm-setting-documentation">{ props.group.hasOwnProperty('documentation_text' ) ? props.group.documentation_text : 'Learn More' }</a>
                :
                null
            }
            {
                props.group.hasOwnProperty('settings')
                ?
                <Settings
                    outputSettings={props.group.settings}
                    settings={props.settings}
                    onSettingChange={props.onSettingChange}
                    settingsChanged={props.settingsChanged}
                    searchQuery={props.searchQuery}
                />
                :
                null
            }
            {
                props.group.hasOwnProperty('subGroups')
                ?
                props.group.subGroups.map((subgroup, i) => {
                    if ( ! Helpers.dependencyMet(subgroup, props.settings ) ) {
                        return null;
                    }

                    return <SettingsSubGroup
                        group={props.group}
                        subgroupIndex={i}
                        settings={props.settings}
                        onSettingChange={props.onSettingChange}
                        settingsChanged={props.settingsChanged}
                        subgroup={subgroup}
                        searchQuery={props.searchQuery}
                        key={i}
                    />
                })
                :
                null
            }
        </div>
    );
}

SettingsGroup.propTypes = {
    group: PropTypes.object.isRequired,
    settings: PropTypes.object.isRequired,
    settingsChanged: PropTypes.bool.isRequired,
    onSettingChange: PropTypes.func.isRequired,
    searchQuery: PropTypes.string.isRequired,
}

export default SettingsGroup;
