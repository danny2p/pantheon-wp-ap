import React from 'react';

export default {
    dependencyMet(object, settings) {
        if (object.hasOwnProperty('dependency')) {
            let dependencies = object.dependency;
            
            // Make sure dependencies is an array.
            if ( ! Array.isArray( dependencies ) ) {
                dependencies = [dependencies];
            }

            // Check all dependencies.
            for ( let dependency of dependencies ) {
                let dependency_value = settings[dependency.id];

                if ( dependency.hasOwnProperty('type') && 'inverse' == dependency.type ) {
                    if (dependency_value == dependency.value) {
                        return false;
                    }
                } else {
                    if (dependency_value != dependency.value) {
                        return false;
                    }
                }
            }
        }

        return true;
    },
    beforeSettingDisplay(id, settings) {
        let value = settings[id];

        if ( 'import_units' === id ) {
            value = value.join(wprm_admin.eol);
        } else if ( 'unit_conversion_units' === id ) {
            let newValue = {};

            for (let unit in value) {
                newValue[unit] = {
                    ...value[unit],
                    aliases: value[unit].aliases.join(';')
                }
            }

            value = newValue;
        }

        return value;
    },
    beforeSettingSave(value, id, settings) {
        if ( 'import_units' === id ) {
            value = value.split(wprm_admin.eol);
        } else if ( 'unit_conversion_units' === id ) {
            let newValue = {};

            for (let unit in value) {
                newValue[unit] = {
                    ...value[unit],
                    aliases: value[unit].aliases.split(';')
                }
            }

            value = newValue;
        }

        return value;
    },
    escapeHTML(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    },
    truncateText(text, maxLength = 36) {
        if ( text.length <= maxLength ) {
            return text;
        }

        return `${text.slice(0, maxLength - 1)}…`;
    },
    getSettingOptionLabel(setting, value) {
        if ( ! setting || ! setting.options ) {
            return false;
        }

        const optionKey = String(value);

        if ( Object.prototype.hasOwnProperty.call(setting.options, optionKey) ) {
            return setting.options[optionKey];
        }

        return false;
    },
    formatChangedSettingValue(setting, value) {
        const displayValue = this.beforeSettingDisplay(setting.id, { [setting.id]: value });

        if ( 'toggle' === setting.type || 'boolean' === typeof displayValue ) {
            return displayValue ? 'On' : 'Off';
        }

        if ( Array.isArray(displayValue) ) {
            if ( ! displayValue.length ) {
                return 'None';
            }

            const items = displayValue.map((item) => {
                const optionLabel = this.getSettingOptionLabel(setting, item);
                return optionLabel ? optionLabel : item;
            }).map((item) => this.escapeHTML(this.truncateText(String(item), 20)));

            if ( items.length > 3 ) {
                return `${items.slice(0, 3).join(', ')} +${items.length - 3} more`;
            }

            return items.join(', ');
        }

        const optionLabel = this.getSettingOptionLabel(setting, displayValue);
        if ( optionLabel ) {
            return this.escapeHTML(optionLabel);
        }

        if ( null === displayValue || undefined === displayValue ) {
            return 'Empty';
        }

        if ( 'object' === typeof displayValue ) {
            const itemCount = Object.keys(displayValue).length;

            if ( ! itemCount ) {
                return 'Empty';
            }

            return `${itemCount} item${1 === itemCount ? '' : 's'}`;
        }

        if ( 'string' === typeof displayValue ) {
            const normalizedValue = displayValue.replace(/\s+/g, ' ').trim();

            if ( ! normalizedValue.length ) {
                return 'Empty';
            }

            return `&quot;${this.escapeHTML(this.truncateText(normalizedValue))}&quot;`;
        }

        return this.escapeHTML(displayValue);
    },
    getChangedSettingDescription(setting, savedValue, currentValue) {
        const savedDescription = this.formatChangedSettingValue(setting, savedValue);
        const currentDescription = this.formatChangedSettingValue(setting, currentValue);
        const actionDescription = 'Click to cancel the above change';

        if ( savedDescription === currentDescription ) {
            return `${this.escapeHTML(setting.name ? setting.name : setting.id)}<br/><br/>${actionDescription}`;
        }

        return `${savedDescription} &rarr; ${currentDescription}<br/><br/>${actionDescription}`;
    },
    isPersistedSetting(setting, savedSettings, currentSettings) {
        if ( ! setting || ! setting.id ) {
            return false;
        }

        return Object.prototype.hasOwnProperty.call(savedSettings, setting.id)
            || Object.prototype.hasOwnProperty.call(currentSettings, setting.id);
    },
    getChangedSettings(structure, savedSettings, currentSettings) {
        const changedSettings = [];

        const maybeAddChangedSetting = (setting, group, subgroup, isCurrentlyVisible) => {
            if ( ! this.isPersistedSetting(setting, savedSettings, currentSettings) ) {
                return;
            }

            const savedValue = savedSettings[setting.id];
            const currentValue = currentSettings[setting.id];

            if ( JSON.stringify(savedValue) === JSON.stringify(currentValue) ) {
                return;
            }

            changedSettings.push({
                id: setting.id,
                name: setting.name ? setting.name : setting.id,
                groupName: group.name ? group.name : group.id,
                subgroupName: subgroup && subgroup.name ? subgroup.name : '',
                isCurrentlyVisible,
                targetAnchor: setting.id,
                fallbackGroupId: group.id,
                changeDescription: this.getChangedSettingDescription(setting, savedValue, currentValue),
            });
        };

        for ( let group of structure ) {
            if ( ! group || ! group.id || group.hasOwnProperty('header') ) {
                continue;
            }

            const groupVisible = this.dependencyMet(group, currentSettings);

            if ( group.settings ) {
                for ( let setting of group.settings ) {
                    maybeAddChangedSetting(
                        setting,
                        group,
                        false,
                        groupVisible && this.dependencyMet(setting, currentSettings)
                    );
                }
            }

            if ( group.subGroups ) {
                for ( let subgroup of group.subGroups ) {
                    const subgroupVisible = groupVisible && this.dependencyMet(subgroup, currentSettings);

                    if ( ! subgroup.settings ) {
                        continue;
                    }

                    for ( let setting of subgroup.settings ) {
                        maybeAddChangedSetting(
                            setting,
                            group,
                            subgroup,
                            subgroupVisible && this.dependencyMet(setting, currentSettings)
                        );
                    }
                }
            }
        }

        return changedSettings;
    },
    getSubgroupAnchor(group, subgroup, subgroupIndex = 0) {
        const subgroupSlugSource = subgroup && subgroup.name ? subgroup.name : `subgroup-${subgroupIndex + 1}`;
        const subgroupSlug = String(subgroupSlugSource)
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');

        return `wprm-settings-subgroup-${group.id}-${subgroupIndex + 1}-${subgroupSlug || 'section'}`;
    },
    getSettingsToolsResetAnchor() {
        return 'wprm-settings-tools-reset-defaults';
    },
    getSettingsToolsSearchResults(group, normalizedQuery) {
        const results = [];
        const resetDefaultsName = 'Reset to defaults';
        const resetDefaultsDescription = 'Reset all settings to their default values.';

        if (
            this.matchesSearch(resetDefaultsName, normalizedQuery)
            || this.matchesSearch(resetDefaultsDescription, normalizedQuery)
        ) {
            results.push({
                id: `${group.id}-reset-defaults`,
                name: resetDefaultsName,
                targetType: 'setting',
                targetAnchor: this.getSettingsToolsResetAnchor(),
            });
        }

        return results;
    },
    getSearchResults(structure, settings, normalizedQuery) {
        if ( ! normalizedQuery ) {
            return {
                groups: [],
                count: 0,
            };
        }

        const searchResults = [];
        let resultCount = 0;

        for ( let group of structure ) {
            if ( ! group || ! group.id || group.hasOwnProperty('header') || ! this.dependencyMet(group, settings) ) {
                continue;
            }

            const groupResult = {
                id: group.id,
                name: group.name ? group.name : group.id,
                icon: group.hasOwnProperty('icon') ? group.icon : false,
                targetId: `wprm-settings-group-${group.id}`,
                isDirectMatch: this.groupNameOrDescriptionMatches(group, normalizedQuery),
                settings: [],
                subgroups: [],
            };

            if ( 'settingsTools' === group.id ) {
                groupResult.settings = this.getSettingsToolsSearchResults(group, normalizedQuery);
            } else {
                if ( group.settings ) {
                    for ( let setting of group.settings ) {
                        if ( ! this.dependencyMet(setting, settings) || ! this.settingMatchesSearch(setting, normalizedQuery) ) {
                            continue;
                        }

                        groupResult.settings.push({
                            id: setting.id,
                            name: setting.name ? setting.name : setting.id,
                            targetType: 'setting',
                            targetAnchor: setting.id,
                        });
                    }
                }

                if ( group.subGroups ) {
                    for ( let i = 0; i < group.subGroups.length; i++ ) {
                        const subgroup = group.subGroups[i];

                        if ( ! this.dependencyMet(subgroup, settings) ) {
                            continue;
                        }

                        const subgroupResult = {
                            id: this.getSubgroupAnchor(group, subgroup, i),
                            name: subgroup.name ? subgroup.name : `Section ${i + 1}`,
                            targetId: this.getSubgroupAnchor(group, subgroup, i),
                            isDirectMatch: this.subgroupNameOrDescriptionMatches(subgroup, normalizedQuery),
                            settings: [],
                        };

                        if ( subgroup.settings ) {
                            for ( let setting of subgroup.settings ) {
                                if ( ! this.dependencyMet(setting, settings) || ! this.settingMatchesSearch(setting, normalizedQuery) ) {
                                    continue;
                                }

                                subgroupResult.settings.push({
                                    id: setting.id,
                                    name: setting.name ? setting.name : setting.id,
                                    targetType: 'setting',
                                    targetAnchor: setting.id,
                                });
                            }
                        }

                        if ( subgroupResult.isDirectMatch || subgroupResult.settings.length ) {
                            groupResult.subgroups.push(subgroupResult);
                        }
                    }
                }
            }

            if ( groupResult.isDirectMatch || groupResult.settings.length || groupResult.subgroups.length ) {
                resultCount += 1 + groupResult.settings.length;

                for ( let subgroupResult of groupResult.subgroups ) {
                    resultCount += 1 + subgroupResult.settings.length;
                }

                searchResults.push(groupResult);
            }
        }

        return {
            groups: searchResults,
            count: resultCount,
        };
    },
    matchesSearch(text, normalizedQuery) {
        if (!normalizedQuery || !text) {
            return false;
        }
        const normalizedText = String(text).toLowerCase();
        return normalizedText.includes(normalizedQuery);
    },
    highlightText(text, searchQuery) {
        if (!searchQuery || !text) {
            return text;
        }
        const normalizedText = String(text);
        const normalizedQuery = searchQuery.toLowerCase();
        const escapedQuery = searchQuery.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${escapedQuery})`, 'gi');
        const parts = normalizedText.split(regex);
        
        return parts.map((part, index) => {
            // Check if this part matches the search query (case-insensitive)
            if (part.toLowerCase() === normalizedQuery) {
                return React.createElement('mark', { key: index }, part);
            }
            return part;
        });
    },
    groupMatchesSearch(group, normalizedQuery) {
        if (!normalizedQuery) {
            return true;
        }
        
        // Check group name and description
        if (this.matchesSearch(group.name, normalizedQuery) || 
            (group.description && this.matchesSearch(group.description, normalizedQuery))) {
            return true;
        }
        
        // Check subgroups
        if (group.subGroups) {
            for (let subgroup of group.subGroups) {
                if (this.subgroupMatchesSearch(subgroup, normalizedQuery)) {
                    return true;
                }
            }
        }
        
        // Check direct settings
        if (group.settings) {
            for (let setting of group.settings) {
                if (this.settingMatchesSearch(setting, normalizedQuery)) {
                    return true;
                }
            }
        }
        
        return false;
    },
    subgroupMatchesSearch(subgroup, normalizedQuery) {
        if (!normalizedQuery) {
            return true;
        }
        
        // Check subgroup name and description
        if ((subgroup.name && this.matchesSearch(subgroup.name, normalizedQuery)) || 
            (subgroup.description && this.matchesSearch(subgroup.description, normalizedQuery))) {
            return true;
        }
        
        // Check settings in subgroup
        if (subgroup.settings) {
            for (let setting of subgroup.settings) {
                if (this.settingMatchesSearch(setting, normalizedQuery)) {
                    return true;
                }
            }
        }
        
        return false;
    },
    settingMatchesSearch(setting, normalizedQuery) {
        if (!normalizedQuery) {
            return true;
        }
        
        return (setting.name && this.matchesSearch(setting.name, normalizedQuery)) || 
               (setting.description && this.matchesSearch(setting.description, normalizedQuery));
    },
    groupNameOrDescriptionMatches(group, normalizedQuery) {
        if (!normalizedQuery) {
            return false;
        }
        
        return (group.name && this.matchesSearch(group.name, normalizedQuery)) || 
               (group.description && this.matchesSearch(group.description, normalizedQuery));
    },
    subgroupNameOrDescriptionMatches(subgroup, normalizedQuery) {
        if (!normalizedQuery) {
            return false;
        }
        
        return (subgroup.name && this.matchesSearch(subgroup.name, normalizedQuery)) || 
               (subgroup.description && this.matchesSearch(subgroup.description, normalizedQuery));
    }
};
