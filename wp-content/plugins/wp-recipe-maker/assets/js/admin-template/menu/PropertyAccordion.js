import React, { useState } from 'react';

import Property from './Property';
import PropertyHeader from './properties/Header';
import Icon from '../general/Icon';
import Helpers from '../general/Helpers';

const PropertyAccordion = (props) => {
    // Convert properties object to array if needed, maintaining order
    const propertiesArray = Array.isArray(props.properties) 
        ? props.properties 
        : Object.values(props.properties || {});

    // Filter out properties that don't meet dependencies
    const visibleProperties = propertiesArray.filter(property => {
        // Headers are always visible
        if (property.type === 'header') {
            return true;
        }
        // Check dependency for other properties
        return Helpers.dependencyMet(property, props.properties);
    });

    // Group properties by headers
    const groups = [];
    let currentGroup = {
        headerProperty: null,
        properties: []
    };

    for (let property of visibleProperties) {
        if (property.type === 'header') {
            // Save previous group if it has visible properties
            if (currentGroup.properties.length > 0) {
                groups.push(currentGroup);
            } else if (currentGroup.headerProperty) {
                // If group only has a header but no properties, don't include it
            }
            // Start new group
            currentGroup = {
                headerProperty: property,
                properties: []
            };
        } else {
            // Add property to current group
            currentGroup.properties.push(property);
        }
    }

    // Add the last group only if it has visible properties
    if (currentGroup.properties.length > 0) {
        groups.push(currentGroup);
    }

    // If no groups were created (no headers), create a single group with all non-header properties
    if (groups.length === 0 && visibleProperties.length > 0) {
        const nonHeaderProperties = visibleProperties.filter(p => p.type !== 'header');
        if (nonHeaderProperties.length > 0) {
            groups.push({
                headerProperty: null,
                properties: nonHeaderProperties
            });
        }
    }

    // State for expanded sections (all collapsed by default)
    // Store the index of the currently expanded section, or null if none
    const [expandedSection, setExpandedSection] = useState(null);

    const toggleSection = (index) => {
        setExpandedSection(prev => {
            // If clicking the same section that's open, close it
            // Otherwise, open the clicked section (closing any previously open one)
            return prev === index ? null : index;
        });
    };

    if (groups.length === 0) {
        return null;
    }

    return (
        <div className="wprm-template-property-accordion">
            {groups.map((group, groupIndex) => {
                const hasHeader = group.headerProperty !== null;
                const hasVisibleProperties = group.properties.length > 0;
                // Only show header if there are visible properties below it
                const shouldShowHeader = hasHeader && hasVisibleProperties;
                // All sections with headers collapsed by default
                // Sections without headers are always expanded (no way to expand them otherwise)
                const isExpanded = shouldShowHeader 
                    ? (expandedSection === groupIndex)
                    : true;

                return (
                    <div 
                        key={groupIndex} 
                        className={`wprm-template-property-accordion-section ${isExpanded ? 'is-expanded' : 'is-collapsed'}`}
                    >
                        {shouldShowHeader ? (
                            <div
                                className="wprm-template-property-accordion-header"
                                onClick={() => toggleSection(groupIndex)}
                            >
                                <div className="wprm-template-property-accordion-header-text">
                                    <PropertyHeader property={group.headerProperty} />
                                </div>
                                <Icon
                                    type={isExpanded ? 'arrow-up' : 'arrow-down'}
                                    className="wprm-template-property-accordion-icon"
                                />
                            </div>
                        ) : null}
                        <div
                            className={`wprm-template-property-accordion-content ${isExpanded ? 'expanded' : 'collapsed'}`}
                        >
                            <div className="wprm-template-property-accordion-content-inner">
                                {group.properties.map((property, propertyIndex) => {
                                    return (
                                        <Property
                                            properties={props.properties}
                                            property={property}
                                            onPropertyChange={props.onPropertyChange}
                                            fonts={props.fonts}
                                            onChangeFonts={props.onChangeFonts}
                                            key={propertyIndex}
                                        />
                                    );
                                })}
                            </div>
                        </div>
                    </div>
                );
            })}
        </div>
    );
};

export default PropertyAccordion;
