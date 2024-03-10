import React from 'react';
import PropTypes from 'prop-types';
import Select from 'react-select';


const SettingDropdownTemplateModern = (props) => {
    const templates = wprm_admin.recipe_templates.modern;
    let allSettings = [];
    let templateGroups = {
        'recipe': [],
        'snippet': [],
        'roundup': [],
    };

    // Put templates in correct categories.
    Object.entries(templates).forEach(([slug, template]) => {    
        if ( ! template.premium || wprm_admin.addons.premium ) {
            const templateOption = {
                value: slug,
                label: template.name,
            }

            if ( 'snippet' === template.type ) {
                templateGroups.snippet.push(templateOption);
            } else if ( 'roundup' === template.type ) {
                templateGroups.roundup.push(templateOption);
            } else {
                templateGroups.recipe.push(templateOption);
            }

            allSettings.push(templateOption);
        }
    });

    // Groups to use in dropdown.
    let dropdownGroups = {
        general: false,
        recipe: {
            label: 'Full Recipe Templates',
            options: templateGroups.recipe,
        },
        snippet: {
            label: 'Snippet Templates',
            options: templateGroups.snippet,
        },
        roundup: {
            label: 'Roundup Templates',
            options: templateGroups.roundup,
        },
    };

    // Optional General Options.
    if ( props.setting.hasOwnProperty( 'options' ) ) {
        let generalOptions = [];

        for (let option in props.setting.options) {
            let generalOption = {
                value: option,
                label: props.setting.options[option],
            };

            generalOptions.push(generalOption);
            allSettings.push(generalOption);
        }

        dropdownGroups.general = {
            label: 'General',
            options: generalOptions,
        };
    }

    // Put in groups for dropdown.
    let selectOptions = [];
    const priority = props.setting.hasOwnProperty( 'priority' ) && props.setting.priority && dropdownGroups.hasOwnProperty( props.setting.priority )? props.setting.priority : false;

    // First put priority templates.
    if ( priority ) {
        selectOptions.push( dropdownGroups[priority] );
    }

    // Then the rest of the templates.]
    Object.entries(dropdownGroups).forEach(([key, group]) => {    
        if ( key !== priority && group !== false ) {
            selectOptions.push( group );
        }
    });

    return (
        <Select
            className="wprm-setting-input"
            value={allSettings.filter(({value}) => value === props.value)}
            onChange={(option) => props.onValueChange(option.value)}
            options={selectOptions}
            clearable={false}
        />
    );
}

SettingDropdownTemplateModern.propTypes = {
    setting: PropTypes.object.isRequired,
    value: PropTypes.any.isRequired,
    onValueChange: PropTypes.func.isRequired,
}

export default SettingDropdownTemplateModern;