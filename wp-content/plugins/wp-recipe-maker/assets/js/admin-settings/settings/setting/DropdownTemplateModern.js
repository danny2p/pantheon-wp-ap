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

    let selectOptions = [
        {
            label: 'Full Recipe Templates',
            options: templateGroups.recipe,
        },{
            label: 'Snippet Templates',
            options: templateGroups.snippet,
        },{
            label: 'Roundup Templates',
            options: templateGroups.roundup,
        },
    ];

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

        selectOptions.unshift({
            label: 'General',
            options: generalOptions,
        });
    }

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