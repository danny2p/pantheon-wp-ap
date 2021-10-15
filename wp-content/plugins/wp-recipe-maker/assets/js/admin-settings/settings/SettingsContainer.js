import React from 'react';
import PropTypes from 'prop-types';

import Helpers from '../general/Helpers';
import SettingsGroup from './SettingsGroup';
import SettingsTools from './SettingsTools';

const SettingsContainer = (props) => {

    let offsetNeeded = 0;

    if ( props.structure.length > 0 ) {
        const lastGroup = props.structure[props.structure.length -1];
        const lastSection = document.getElementById(`wprm-settings-group-${lastGroup.id}`);
        if (lastSection) {
            const topOfLastSection = lastSection.getBoundingClientRect().top + window.scrollY;
            const pageScrollNeeded = document.body.scrollHeight - topOfLastSection;
            offsetNeeded = (window.innerHeight + 42) - pageScrollNeeded;
        }
    }

    return (
        <div id="wprm-settings-container">
            {
                props.structure.map((group, i) => {
                    if (group.hasOwnProperty('description') || group.hasOwnProperty('subGroups') || group.hasOwnProperty('settings')) {
                        if ( ! Helpers.dependencyMet(group, props.settings ) ) {
                            return null;
                        }
                        
                        return <SettingsGroup
                            settings={props.settings}
                            onSettingChange={props.onSettingChange}
                            settingsChanged={props.settingsChanged}
                            group={group}
                            key={i}
                        />
                    }

                    if('settingsTools' === group.id) {
                        return <SettingsTools
                            settings={props.settings}
                            onResetDefaults={props.onResetDefaults}
                            group={group}
                            key={i}
                        />
                    }
                })
            }
            <div className='wprm-settings-spacer' style={{height: offsetNeeded}}></div>
        </div>
    );
}

SettingsContainer.propTypes = {
    structure: PropTypes.array.isRequired,
    settings: PropTypes.object.isRequired,
    settingsChanged: PropTypes.bool.isRequired,
    onSettingChange: PropTypes.func.isRequired,
    onResetDefaults: PropTypes.func.isRequired,
}

export default SettingsContainer;