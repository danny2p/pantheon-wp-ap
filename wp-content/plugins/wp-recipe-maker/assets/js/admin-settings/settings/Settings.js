import React from 'react';
import PropTypes from 'prop-types';

import Helpers from '../general/Helpers';
import Setting from './Setting';
import ErrorBoundary from '../general/ErrorBoundary';

const Settings = (props) => {
    return (
        <div className="wprm-settings-group-container">
            {
                props.outputSettings.map((setting, i) => {
                    if ( ! Helpers.dependencyMet(setting, props.settings ) ) {
                        return null;
                    }

                    // Only filter by search query if parent (group/subgroup) didn't match
                    // If parent matched, show all settings for context
                    if (!props.parentMatched && props.normalizedSearchQuery && !Helpers.settingMatchesSearch(setting, props.normalizedSearchQuery)) {
                        return null;
                    }

                    return (
                        <ErrorBoundary key={i}>
                            <Setting
                                settings={props.settings}
                                setting={setting}
                                settingsChanged={props.settingsChanged}
                                onSettingChange={props.onSettingChange}
                                value={props.settings[setting.id]}
                                searchQuery={props.searchQuery}
                                normalizedSearchQuery={props.normalizedSearchQuery}
                                key={i}
                            />
                        </ErrorBoundary>
                    )
                })
            }
        </div>
    );
}

Settings.propTypes = {
    settings: PropTypes.object.isRequired,
    outputSettings: PropTypes.array.isRequired,
    onSettingChange: PropTypes.func.isRequired,
    settingsChanged: PropTypes.bool.isRequired,
    searchQuery: PropTypes.string.isRequired,
    normalizedSearchQuery: PropTypes.string.isRequired,
    parentMatched: PropTypes.bool,
}

export default Settings;