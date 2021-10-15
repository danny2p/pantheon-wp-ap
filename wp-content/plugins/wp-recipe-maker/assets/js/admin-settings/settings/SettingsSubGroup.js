import React from 'react';
import PropTypes from 'prop-types';

import RequiredLabel from './RequiredLabel';
import Settings from './Settings';

const SettingsSubGroup = (props) => {
    return (
        <div className="wprm-settings-subgroup">
            <h3 className="wprm-settings-subgroup-name"><RequiredLabel object={props.subgroup}/>{props.subgroup.name}</h3>
            {
                props.subgroup.hasOwnProperty('description')
                ?
                <div className="wprm-settings-subgroup-description">{props.subgroup.description}</div>
                :
                null
            }
            {
                props.subgroup.hasOwnProperty('documentation')
                ?
                <a href={props.subgroup.documentation} target="_blank" className="wprm-setting-documentation">Learn More</a>
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
}

export default SettingsSubGroup;