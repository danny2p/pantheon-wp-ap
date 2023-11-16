import React, { Fragment } from 'react';
import FieldDropdown from './FieldDropdown';

const FieldDropdownTemplate = (props) => {
    const templates = wprm_admin.recipe_templates.modern;
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
        }
    });

    // Groups to use in dropdown.
    const dropdownGroups = {
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

    // Put in groups for dropdown.
    let selectOptions = props.hasOwnProperty( 'options' ) ? props.options : [];
    const priority = props.priority && dropdownGroups.hasOwnProperty( props.priority )? props.priority : 'recipe';

    // First put priority templates.
    selectOptions.push( dropdownGroups[props.priority] );

    // Then the rest of the templates.]
    Object.entries(dropdownGroups).forEach(([key, group]) => {    
        if ( key !== priority ) {
            selectOptions.push( group );
        }
    });

    return (
        <FieldDropdown
            isDisabled={ props.isDisabled }
            options={selectOptions}
            value={props.value}
            placeholder={props.placeholder}
            onChange={props.onChange}
            width={props.width}
            custom={props.custom}
        />
    );
}
export default FieldDropdownTemplate;