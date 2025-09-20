import React, { Fragment } from 'react';

import BlockProperties from '../../menu/BlockProperties';
import Property from '../../menu/Property';

import Elements from '../../general/elements';

const getProperties = ( shortcode ) => {
    let properties = {};
    const elementId = shortcode.id.replace('wprm-layout-', '');
    const structure = Elements.propertiesForElement.hasOwnProperty( elementId ) ? Elements.propertiesForElement[ elementId ] : false;
    const classes = shortcode.hasOwnProperty( 'classes' ) ? shortcode.classes : [];
    const style = shortcode.hasOwnProperty( 'style' ) ? shortcode.style : [];

    if ( structure ) {
        for ( let id of structure ) {
            let property = Elements.potentialProperties.hasOwnProperty(id) ? { ...Elements.potentialProperties[id] } : false;

            if ( property ) {
                property.id = id;

                if ( property.hasOwnProperty( 'classesToValue' ) ) {
                    property.value = property.classesToValue( classes );
                } else if ( property.hasOwnProperty( 'styleToValue' ) ) {
                    property.value = property.styleToValue( style );
                }

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

const getStyle = ( properties ) => {
    let style = [];

    for ( let property of Object.values(properties) ) {
        if ( property.value && property.hasOwnProperty( 'valueToStyle' ) ) {
            style = style.concat( property.valueToStyle( property.value ) );
        }
    }

    return style;
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

    // Styles.
    let inlineStyle = {};
    if ( props.shortcode.hasOwnProperty( 'style' ) && props.shortcode.style.length ) {
        for ( let style of props.shortcode.style ) {
            const parts = style.split( ': ', 2 );
            inlineStyle[ '--' + props.shortcode.id + '-' + parts[0] ] = parts[1];
        } ';';
    }

    return (
        <Fragment>
            <div
                className={ classes.join( ' ' ) }
                style={ inlineStyle }
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

                                            if ( property.hasOwnProperty( 'valueToClasses' ) ) {
                                                const newClasses = getClasses( newProperties );
                                                props.onClassesChange( props.shortcode.uid, newClasses );
                                            } else if ( property.hasOwnProperty( 'valueToStyle' ) ) {
                                                const newStyle = getStyle( newProperties );
                                                props.onStyleChange( props.shortcode.uid, newStyle );
                                            }
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