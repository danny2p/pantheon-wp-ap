import React from 'react';
import PropTypes from 'prop-types';

import Helpers from '../general/Helpers';

const SettingsTools = (props) => {
    return (
        <div id={`wprm-settings-group-${props.group.id}`} className="wprm-settings-group">
            <h2 className="wprm-settings-group-name">
                {props.searchQuery ? Helpers.highlightText(props.group.name, props.searchQuery) : props.group.name}
            </h2>
            <div className="wprm-settings-group-container">
                <div className="wprm-setting-container">
                    <div className="wprm-setting-label-container">
                        <span className="wprm-setting-label">
                            {props.searchQuery ? Helpers.highlightText('Reset to defaults', props.searchQuery) : 'Reset to defaults'}
                        </span>
                        <span className="wprm-setting-description">
                            {props.searchQuery ? Helpers.highlightText('Reset all settings to their default values.', props.searchQuery) : 'Reset all settings to their default values.'}
                        </span>
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
    searchQuery: PropTypes.string.isRequired,
}

export default SettingsTools;