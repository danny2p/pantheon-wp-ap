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
