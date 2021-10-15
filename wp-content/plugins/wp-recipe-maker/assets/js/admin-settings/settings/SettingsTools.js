import React from 'react';
import PropTypes from 'prop-types';

const SettingsTools = (props) => {
    return (
        <div id={`wprm-settings-group-${props.group.id}`} className="wprm-settings-group">
            <h2 className="wprm-settings-group-name">{props.group.name}</h2>
            <div className="wprm-settings-group-container">
                <div className="wprm-setting-container">
                    <div className="wprm-setting-label-container">
                        <span className="wprm-setting-label">
                            Reset to defaults
                        </span>
                        <span className="wprm-setting-description">Reset all settings to their default values.</span>
                    </div>
                    <div className="wprm-setting-input-container">
                        <button
                            className="button"
                            onClick={props.onResetDefaults}
                        >Reset to Defaults</button>
                    </div>
                </div>
            </div>
        </div>
    );
}

SettingsTools.propTypes = {
    group: PropTypes.object.isRequired,
    settings: PropTypes.object.isRequired,
    onResetDefaults: PropTypes.func.isRequired,
}

export default SettingsTools;