import React from 'react';

const PropertyInfo = (props) => {

    let style = {};
    if ( props.property.hasOwnProperty( 'color' ) ) {
        style.color = props.property.color;
    }

    return (
        <div
            className="wprm-template-property-info"
            style={ style }
        >
            { props.property.text }
        </div>
    );
}

export default PropertyInfo;