import React, { Fragment} from 'react';

import Property from './Property';

const TemplateProperties = (props) => {
    let propertiesColor = [];
    let propertiesText = [];
    let propertiesOther = [];

    for ( let property of Object.values(props.template.style.properties) ) {
        switch(property.type) {
            case 'color':
                propertiesColor.push(property);
                break;
            case 'align':
                property.options = {
                    left: 'Left',
                    center: 'Center',
                    right: 'Right',
                };
            case 'font':
            case 'font_size':
                propertiesText.push(property);
                break;
            case 'float':
                property.options = {
                    left: 'Left',
                    none: 'None',
                    right: 'Right',
                };
                propertiesOther.push(property);
                break;
            case 'border':
                property.options = {
                    solid: 'Solid',
                    dashed: 'Dashed',
                    dotted: 'Dotted',
                    double: 'Double',
                    groove: 'Groove',
                    ridge: 'Ridge',
                    inset: 'Inset',
                    outset: 'Outset',
                };
                propertiesOther.push(property);
                break;
            case 'percentage':
                property.suffix = '%';
                propertiesOther.push(property);
                break;
            default:
                propertiesOther.push(property);
        }
    }

    const groups = [
        {
            header: 'Colors',
            properties: propertiesColor
        },
        {
            header: 'Text',
            properties: propertiesText
        },
        {
            header: 'Other',
            properties: propertiesOther
        },
    ];

    return (
        <div id="wprm-template-properties" className="wprm-template-properties">
            {
               Object.values(props.template.style.properties).length > 0
                ?
                <Fragment>
                    {
                        groups.map((group, i) => {
                            if ( group.properties.length > 0 ) {
                                return (
                                    <Fragment key={i}>
                                        <div className="wprm-template-properties-header">{group.header}</div>
                                        {
                                            group.properties.map((property, j) => {
                                                return <Property
                                                            property={property}
                                                            onPropertyChange={props.onChangeTemplateProperty}
                                                            key={j}
                                                        />;
                                            })
                                        }
                                    </Fragment>
                                )
                            }
                        })
                    }
                </Fragment>
                :
                <p>This template does not have any adjustable properties.</p>
            }
        </div>
    );
}

export default TemplateProperties;