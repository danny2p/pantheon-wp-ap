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
            <h2 className="wprm-settings-group-name">{props.group.name}</h2>
            {
                props.group.hasOwnProperty('description')
                ?
                <div className="wprm-settings-group-description">{props.group.description}</div>
                :
                null
            }
            {
                props.group.hasOwnProperty('documentation')
                ?
                <a href={props.group.documentation} target="_blank" className="wprm-setting-documentation">Learn More</a>
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
                        settings={props.settings}
                        onSettingChange={props.onSettingChange}
                        settingsChanged={props.settingsChanged}
                        subgroup={subgroup}
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
}

export default SettingsGroup;