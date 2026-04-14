import React, { Component } from 'react';
import '../../css/admin/settings.scss';

import Api from 'Shared/Api';
import MenuContainer from './menu/MenuContainer';
import SettingsContainer from './settings/SettingsContainer';
import { animateScroll as scroll, scroller } from 'react-scroll';
import Icon from './general/Icon';
import Helpers from './general/Helpers';

export default class App extends Component {

    constructor(props) {
        super(props);
        
        this.state = {
            savedSettings: { ...wprm_settings.settings },
            currentSettings: { ...wprm_settings.settings },
            savingChanges: false,
            searchQuery: '',
            showChangedSettings: false,
        }
        
        this.searchTimeout = null;
        this.searchInputRef = null;
    }

    scrollToElement(targetId, offset = 42) {
        const target = document.getElementById(targetId);

        if ( ! target ) {
            return false;
        }

        const top = target.getBoundingClientRect().top + window.scrollY - offset;

        scroll.scrollTo(
            0 > top ? 0 : top,
            {
                smooth: true,
                duration: 400,
            }
        );

        return true;
    }

    onSettingChange(setting, value) {
        const newSettings = {
            ...this.state.currentSettings,
            [setting]: value,
        };

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
                        showChangedSettings: false,
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
                showChangedSettings: false,
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
        
        // Debounce search-driven UI updates to avoid re-rendering on every keystroke.
        // This prevents React re-renders on every keystroke
        this.searchTimeout = setTimeout(() => {
            this.setState({
                searchQuery: query
            });
        }, 200);
    }

    onToggleChangedSettings() {
        this.setState((prevState) => ({
            showChangedSettings: ! prevState.showChangedSettings,
        }));
    }

    onRevertChangedSetting(settingId) {
        this.setState((prevState) => ({
            currentSettings: {
                ...prevState.currentSettings,
                [settingId]: prevState.savedSettings[settingId],
            },
        }), () => {
            const changedSettings = Helpers.getChangedSettings(
                wprm_settings.structure,
                this.state.savedSettings,
                this.state.currentSettings
            );

            if ( ! changedSettings.length && this.state.showChangedSettings ) {
                this.setState({
                    showChangedSettings: false,
                });
            }
        });
    }

    onJumpToChangedSetting(changedSetting) {
        if ( changedSetting.isCurrentlyVisible ) {
            scroller.scrollTo(changedSetting.targetAnchor, {
                smooth: true,
                duration: 400,
                offset: -110,
            });

            return;
        }

        if ( this.scrollToElement(`wprm-settings-group-${changedSetting.fallbackGroupId}`) ) {
            return;
        }

        this.scrollToElement('wprm-settings-container');
    }

    onJumpToSearchResult(searchResult) {
        if ( ! searchResult ) {
            return;
        }

        if ( 'setting' === searchResult.targetType ) {
            scroller.scrollTo(searchResult.targetAnchor, {
                smooth: true,
                duration: 400,
                offset: -110,
            });

            return;
        }

        if ( searchResult.targetId && this.scrollToElement(searchResult.targetId) ) {
            return;
        }

        this.scrollToElement('wprm-settings-container');
    }

    render() {
        // Cache normalized search query to avoid repeated toLowerCase() calls
        const normalizedSearchQuery = this.state.searchQuery ? this.state.searchQuery.toLowerCase() : '';
        const changedSettings = Helpers.getChangedSettings(
            wprm_settings.structure,
            this.state.savedSettings,
            this.state.currentSettings
        );
        const changedSettingsCount = changedSettings.length;
        const showChangedSettings = this.state.showChangedSettings && 0 < changedSettingsCount;
        const searchResultsData = Helpers.getSearchResults(
            wprm_settings.structure,
            this.state.currentSettings,
            normalizedSearchQuery
        );
        
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
                    onSearchChange={this.onSearchChange.bind(this)}
                    searchResults={searchResultsData.groups}
                    searchResultsCount={searchResultsData.count}
                    onJumpToSearchResult={this.onJumpToSearchResult.bind(this)}
                    changedSettings={changedSettings}
                    changedSettingsCount={changedSettingsCount}
                    showChangedSettings={showChangedSettings}
                    onToggleChangedSettings={this.onToggleChangedSettings.bind(this)}
                    onJumpToChangedSetting={this.onJumpToChangedSetting.bind(this)}
                    onRevertChangedSetting={this.onRevertChangedSetting.bind(this)}
                />
                <SettingsContainer
                    structure={wprm_settings.structure}
                    settings={this.state.currentSettings}
                    settingsChanged={this.settingsChanged()}
                    onSettingChange={this.onSettingChange.bind(this)}
                    onResetDefaults={this.onResetDefaults.bind(this)}
                    searchQuery={this.state.searchQuery}
                />
                <a href="#" className="wprm-settings-scroll-to-top" onClick={this.scrollToTop}>
                    <Icon type="up" />
                </a>
            </div>
        );
    }
}
