import React, { Fragment } from 'react';

import '../../../css/admin/template/property.scss';

import Icon from 'Shared/Icon';
import Helpers from '../general/Helpers';

import PropertyColor from './properties/Color';
import PropertyDropdown from './properties/Dropdown';
import PropertyFont from './properties/Font';
import PropertyHeader from './properties/Header';
import PropertyIcon from './properties/Icon';
import PropertyImage from './properties/Image';
import PropertyImageSize from './properties/ImageSize';
import PropertyInfo from './properties/Info';
import PropertyNumber from './properties/Number';
import PropertySize from './properties/Size';
import PropertyText from './properties/Text';
import PropertyToggle from './properties/Toggle';

const propertyTypes = {
    color: PropertyColor,
    align: PropertyDropdown,
    border: PropertyDropdown,
    dropdown: PropertyDropdown,
    float: PropertyDropdown,
    font: PropertyFont,
    font_size: PropertySize,
    header: PropertyHeader,
    icon: PropertyIcon,
    image: PropertyImage,
    image_size: PropertyImageSize,
    info: PropertyInfo,
    percentage: PropertyNumber,
    number: PropertyNumber,
    size: PropertySize,
    text: PropertyText,
    toggle: PropertyToggle,
}

const Property = (props) => {
    const PropertyComponent = propertyTypes.hasOwnProperty(props.property.type) ? propertyTypes[props.property.type] : false;

    if ( ! PropertyComponent ) {
        return null;
    }

    if ( ! Helpers.dependencyMet(props.property, props.properties) ) {
        return null;
    }

    let helpIcon = null;
    if ( props.property.hasOwnProperty( 'help' ) ) {
        helpIcon = (
            <Icon
                type="question"
                title={ props.property.help }
                className="wprm-admin-icon-help"
            />
        );
    }

    return (
        <div className="wprm-template-property">
            {
                ['header', 'info'].includes( props.property.type )
                ?
                <PropertyComponent property={props.property} />
                :
                <Fragment>
                    <div className="wprm-template-property-label">
                        { props.property.name } { helpIcon }
                    </div>
                    <div className={ `wprm-template-property-value wprm-template-property-value-${props.property.type}` }>
                        <PropertyComponent
                            property={props.property}
                            value={props.property.value}
                            onValueChange={(value) => { props.onPropertyChange(props.property.id, value); } }
                        />
                    </div>
                </Fragment>
            }
        </div>
    );
}

export default Property;