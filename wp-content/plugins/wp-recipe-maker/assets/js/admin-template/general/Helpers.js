export default {
    parseCSS(template) {
        let css = template.style.css;

        // Reconstruct CSS with new value and comments.
        for ( let property of Object.values(template.style.properties) ) {
            let fields = '';
            Object.entries(property).forEach(([field, value]) => {    
                if ( ! ['id', 'name', 'default', 'value', 'options'].includes( field ) ) {
                    fields = ` ${field}=${value}`;
                }
            });

            const replacement = `${property.value}; /*wprm_${property.id}${fields}*/`;
            css = css.replace( new RegExp( `%wprm_${property.id}%\s*;`, 'g' ), replacement );
        }

        return css;
    },
    getShortcodeName(id) {
        let name = id;

        name = name.replace('wprm-layout-', '');
        name = name.replace('wprm-recipe-', '');
        name = name.replace('wprm-', '');

        name = name.replace(/-/g, ' ');
        name = name.toLowerCase().replace(/\b[a-z]/g, function(letter) {
            return letter.toUpperCase();
        });

        return name;
    },
    getShortcodeAttributes(shortcode) {
        let shortcode_atts = {};
        let attributes = shortcode.match(/(\w+=\"[^\"]*?\"|\w+=\'[^\']*?\'|\w+=\w*)/gmi);

        if (attributes) {
            for (let i = 0; i < attributes.length; i++) {
                let attribute = attributes[i];
                let property = attribute.substring(0, attribute.indexOf('='));
                let value = attribute.substring(attribute.indexOf('=') + 1);

                // Trim value if necessary.
                if ('"' === value[0] || "'" === value[0] ) {
                    value = value.substr(1, value.length-2);
                }

                shortcode_atts[property] = value;
            }
        }

        return shortcode_atts;
    },
    getFullShortcode(shortcode, withContent = false) {
        let fullShortcode = '[' + shortcode.id;

        // Add shortcode attributes.
        for (let attribute in shortcode.attributes) {
            if ( shortcode.attributes.hasOwnProperty(attribute) ) {
                let value = shortcode.attributes[attribute];
                
                // Replace " and ] with HTML entity to prevent breaking shortcode.
                value = value.replace(/"/gm, '&quot;');
                value = value.replace(/\]/gm, '&#93;');
                fullShortcode += ' ' + attribute + '="' + value + '"';
            }
        }

        // Close shortcode.
        fullShortcode += ']';

        if ( withContent && false !== shortcode.content ) {
            fullShortcode += shortcode.content;
            fullShortcode += '[/' + shortcode.id + ']';
        }

        return fullShortcode;
    },
    dependencyMet(object, properties) {
        let dependencyMet = true;

        if (properties && object.hasOwnProperty('dependency')) {
            let dependencies = object.dependency;
            
            // Make sure dependencies is an array.
            if ( ! Array.isArray( dependencies ) ) {
                dependencies = [dependencies];
            }

            // Check all dependencies.
            const dependencyCompare = object.hasOwnProperty( 'dependency_compare' ) ? object.dependency_compare : 'AND';
            let firstDependencyChecked = true;

            for ( let dependency of dependencies ) {
                if ( properties.hasOwnProperty(dependency.id) ) {
                    let thisDependencyMet = false;
                    const thisDependencyValue = properties[dependency.id].value;
                    const thisDependencyType = dependency.hasOwnProperty('type') ? dependency.type : 'match';

                    if ( 'inverse' == thisDependencyType ) {
                        if ( thisDependencyValue != dependency.value ) {
                            thisDependencyMet = true;
                        }
                    } else if ( 'includes' == thisDependencyType ) {
                        if ( thisDependencyValue.includes( dependency.value ) ) {
                            thisDependencyMet = true;
                        }
                    } else {
                        if ( thisDependencyValue == dependency.value ) {
                            thisDependencyMet = true;
                        }
                    }

                    // Combine multiple dependencies.
                    if ( 'OR' === dependencyCompare ) {
                        if ( firstDependencyChecked ) {
                            dependencyMet = false;
                            firstDependencyChecked = false;
                        }

                        dependencyMet = dependencyMet || thisDependencyMet;
                    } else {
                        dependencyMet = dependencyMet && thisDependencyMet;
                    }
                }
            }
        }

        return dependencyMet;
    },
};
