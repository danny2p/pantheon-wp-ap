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
                        
                        // Filter by search query
                        if (props.normalizedSearchQuery && !Helpers.groupMatchesSearch(group, props.normalizedSearchQuery)) {
                            return null;
                        }
                        
                        return <SettingsGroup
                            settings={props.settings}
                            onSettingChange={props.onSettingChange}
                            settingsChanged={props.settingsChanged}
                            group={group}
                            searchQuery={props.searchQuery}
                            normalizedSearchQuery={props.normalizedSearchQuery}
                            key={i}
                        />
                    }

                    if('settingsTools' === group.id) {
                        // Check if settingsTools matches the search query
                        const toolsMatches = props.normalizedSearchQuery ? (
                            Helpers.groupNameOrDescriptionMatches(group, props.normalizedSearchQuery) ||
                            Helpers.matchesSearch('Reset to defaults', props.normalizedSearchQuery) ||
                            Helpers.matchesSearch('Reset all settings to their default values.', props.normalizedSearchQuery)
                        ) : true;
                        
                        // Filter by search query
                        if (props.normalizedSearchQuery && !toolsMatches) {
                            return null;
                        }
                        
                        return <SettingsTools
                            settings={props.settings}
                            onResetDefaults={props.onResetDefaults}
                            group={group}
                            searchQuery={props.searchQuery}
                            normalizedSearchQuery={props.normalizedSearchQuery}
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
    searchQuery: PropTypes.string.isRequired,
    normalizedSearchQuery: PropTypes.string.isRequired,
}

export default SettingsContainer;