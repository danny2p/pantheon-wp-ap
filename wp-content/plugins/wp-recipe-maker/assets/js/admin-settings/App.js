import React, { Component } from 'react';
import '../../css/admin/settings.scss';

import Api from 'Shared/Api';
import MenuContainer from './menu/MenuContainer';
import SettingsContainer from './settings/SettingsContainer';
import { animateScroll as scroll, scroller } from 'react-scroll';
import Icon from './general/Icon';

export default class App extends Component {

    constructor(props) {
        super(props);
        
        this.state = {
            savedSettings: { ...wprm_settings.settings },
            currentSettings: { ...wprm_settings.settings },
            savingChanges: false,
            searchQuery: '',
        }
        
        this.searchTimeout = null;
        this.searchInputRef = null;
    }

    onSettingChange(setting, value) {
        let newSettings = this.state.currentSettings;
        newSettings[setting] = value;

        this.setState({
            currentSettings: newSettings
        }, () => {
            // Setting specific actions.
            if ( 'features_custom_style' === setting ) {
                scroller.scrollTo(setting, {
                    smooth: true,
                    duration: 400,
                    offset: -110,    
                });
            }
        });
    }

    onSaveChanges() {
        this.setState({
            savingChanges: true,
        });

        Api.settings.save(this.state.currentSettings)
            .then(settings => {
                if ( settings ) {
                    this.setState({
                        savingChanges: false,
                        savedSettings: { ...settings },
                        currentSettings: { ...settings },
                    });
                } else {
                    this.setState({
                        savingChanges: false,
                    });
                }
                
            });
    }

    onCancelChanges() {
        if(confirm('Are you sure you want to cancel the changes you made?')) {
            this.setState({
                currentSettings: { ...this.state.savedSettings },
            });
        }
    }

    onResetDefaults() {
        if(confirm('Are you sure you want to reset the settings to their default values? This will not save them yet.')) {
            this.setState({
                currentSettings: {
                    ...this.state.savedSettings,
                    ...wprm_settings.defaults
                },
            });
        }
    }

    scrollToTop() {
        scroll.scrollToTop();
    }

    componentDidMount() {
        window.addEventListener( 'beforeunload', this.beforeWindowClose.bind(this) );
    }
    
    componentWillUnmount() {
        window.removeEventListener( 'beforeunload', this.beforeWindowClose.bind(this) );
        // Clear timeout on unmount
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }
    }

    beforeWindowClose(event) {
        if ( this.settingsChanged() ) {
            return false;
        }
    }

    settingsChanged() {
        return JSON.stringify(this.state.savedSettings) !== JSON.stringify(this.state.currentSettings);
    }

    onSearchChange(query) {
        // Clear existing timeout
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }
        
        // Debounce both filtering AND highlighting (200ms delay)
        // This prevents React re-renders on every keystroke
        this.searchTimeout = setTimeout(() => {
            this.setState({
                searchQuery: query
            });
        }, 200);
    }

    render() {
        // Cache normalized search query to avoid repeated toLowerCase() calls
        const normalizedSearchQuery = this.state.searchQuery ? this.state.searchQuery.toLowerCase() : '';
        
        return (
            <div>
                <MenuContainer
                    structure={wprm_settings.structure}
                    settings={this.state.currentSettings}
                    settingsChanged={this.settingsChanged()}
                    savingChanges={this.state.savingChanges}
                    onSaveChanges={this.onSaveChanges.bind(this)}
                    onCancelChanges={this.onCancelChanges.bind(this)}
                    searchQuery={this.state.searchQuery}
                    normalizedSearchQuery={normalizedSearchQuery}
                    onSearchChange={this.onSearchChange.bind(this)}
                />
                <SettingsContainer
                    structure={wprm_settings.structure}
                    settings={this.state.currentSettings}
                    settingsChanged={this.settingsChanged()}
                    onSettingChange={this.onSettingChange.bind(this)}
                    onResetDefaults={this.onResetDefaults.bind(this)}
                    searchQuery={this.state.searchQuery}
                    normalizedSearchQuery={normalizedSearchQuery}
                />
                <a href="#" className="wprm-settings-scroll-to-top" onClick={this.scrollToTop}>
                    <Icon type="up" />
                </a>
            </div>
        );
    }
}
