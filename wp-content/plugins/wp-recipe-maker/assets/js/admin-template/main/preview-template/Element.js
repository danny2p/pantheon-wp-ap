import React, { Component, Fragment } from 'react';
import Parser from 'html-react-parser';
import domToReact from 'html-react-parser/lib/dom-to-react';

import Api from 'Shared/Api';
import Loader from 'Shared/Loader';
import Helpers from '../../general/Helpers';
import BlockProperties from '../../menu/BlockProperties';
import Property from '../../menu/Property';

import Elements from '../../general/elements';

const getProperties = ( shortcode ) => {
    let properties = {};
    const elementId = shortcode.id.replace('wprm-layout-', '');
    const structure = Elements.propertiesForElement.hasOwnProperty( elementId ) ? Elements.propertiesForElement[ elementId ] : false;
    const classes = shortcode.hasOwnProperty( 'classes' ) ? shortcode.classes : [];

    if ( structure ) {
        for ( let id of structure ) {
            let property = Elements.potentialProperties.hasOwnProperty(id) ? { ...Elements.potentialProperties[id] } : false;

            if ( property ) {
                property.id = id;
                property.value = property.classesToValue( classes );

                properties[id] = property;
            }
        }
    }

    return properties;
}

const getClasses = ( properties ) => {
    let classes = [];

    for ( let property of Object.values(properties) ) {
        if ( property.value && property.hasOwnProperty( 'valueToClasses' ) ) {
            classes = classes.concat( property.valueToClasses( property.value ) );
        }
    }

    return classes;
}

const Element = (props) => {
    const properties = getProperties( props.shortcode );

    let classes = [
        props.shortcode.id,
    ];

    if ( props.shortcode.hasOwnProperty( 'classes' ) && props.shortcode.classes ) {
        classes = classes.concat( props.shortcode.classes );
    }
    
    if ( props.shortcode.uid === props.hoveringBlock ) {
        classes.push( 'wprm-template-block-hovering' );
    }

    return (
        <Fragment>
            <div
                className={ classes.join( ' ' ) }
            >
                { props.children }
            </div>
            {
                props.shortcode.uid === props.editingBlock
                ?
                <BlockProperties>
                    <div className="wprm-template-menu-block-details"><a href="#" onClick={ (e) => { e.preventDefault(); return props.onChangeEditingBlock(false); }}>Blocks</a> &gt; { props.shortcode.name }</div>
                    {
                        Object.values(properties).map((property, i) => {
                            return <Property
                                        properties={properties}
                                        property={property}
                                        onPropertyChange={(propertyId, value) => {
                                            const newProperties = { ...properties };
                                            newProperties[propertyId].value = value;

                                            const newClasses = getClasses( newProperties );

                                            props.onClassesChange( props.shortcode.uid, newClasses );
                                        }}
                                        key={i}
                                    />;
                        })
                    }
                    {
                        ! Object.keys(properties).length && <p>There are no adjustable properties for this block.</p>
                    }
                </BlockProperties>
                :
                null
            }
        </Fragment>
    );
}
export default Element;