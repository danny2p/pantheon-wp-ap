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

                    return (
                        <ErrorBoundary key={i}>
                            <Setting
                                settings={props.settings}
                                setting={setting}
                                settingsChanged={props.settingsChanged}
                                onSettingChange={props.onSettingChange}
                                value={props.settings[setting.id]}
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
}

export default Settings;