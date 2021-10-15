import React from 'react';
import PropTypes from 'prop-types';
import Select from 'react-select';


const SettingDropdownTemplateLegacy = (props) => {
    let selectOptions = [];
    const templates = wprm_admin.recipe_templates.legacy;

    for (let template in templates) {
        selectOptions.push({
            value: template,
            label: templates[template].name,
        });
    }

    return (
        <Select
            className="wprm-setting-input"
            value={selectOptions.filter(({value}) => value === props.value)}
            onChange={(option) => props.onValueChange(option.value)}
            options={selectOptions}
            clearable={false}
        />
    );
}

SettingDropdownTemplateLegacy.propTypes = {
    setting: PropTypes.object.isRequired,
    value: PropTypes.any.isRequired,
    onValueChange: PropTypes.func.isRequired,
}

export default SettingDropdownTemplateLegacy;