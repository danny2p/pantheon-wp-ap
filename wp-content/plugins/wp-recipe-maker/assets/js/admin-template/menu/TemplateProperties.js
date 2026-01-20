import React, { Fragment } from 'react';

import PropertyAccordion from './PropertyAccordion';

const TemplateProperties = (props) => {
    // Prepare properties with options/suffix modifications
    const properties = {};
    for (let property of Object.values(props.template.style.properties)) {
        const propertyCopy = { ...property };
        
        switch(property.type) {
            case 'align':
                propertyCopy.options = {
                    left: 'Left',
                    center: 'Center',
                    right: 'Right',
                };
                break;
            case 'float':
                propertyCopy.options = {
                    left: 'Left',
                    none: 'None',
                    right: 'Right',
                };
                break;
            case 'border':
                propertyCopy.options = {
                    solid: 'Solid',
                    dashed: 'Dashed',
                    dotted: 'Dotted',
                    double: 'Double',
                    groove: 'Groove',
                    ridge: 'Ridge',
                    inset: 'Inset',
                    outset: 'Outset',
                };
                break;
            case 'percentage':
                propertyCopy.suffix = '%';
                break;
        }
        
        properties[property.id] = propertyCopy;
    }

    return (
        <div id="wprm-template-properties" className="wprm-template-properties">
            {
               Object.values(properties).length > 0
                ?
                <PropertyAccordion
                    properties={properties}
                    onPropertyChange={props.onChangeTemplateProperty}
                    fonts={props.fonts}
                    onChangeFonts={props.onChangeFonts}
                />
                :
                <p>This template does not have any adjustable properties.</p>
            }
        </div>
    );
}

export default TemplateProperties;