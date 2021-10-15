export default {
    dependencyMet(object, settings) {
        if (object.hasOwnProperty('dependency')) {
            let dependencies = object.dependency;
            
            // Make sure dependencies is an array.
            if ( ! Array.isArray( dependencies ) ) {
                dependencies = [dependencies];
            }

            // Check all dependencies.
            for ( let dependency of dependencies ) {
                let dependency_value = settings[dependency.id];

                if ( dependency.hasOwnProperty('type') && 'inverse' == dependency.type ) {
                    if (dependency_value == dependency.value) {
                        return false;
                    }
                } else {
                    if (dependency_value != dependency.value) {
                        return false;
                    }
                }
            }
        }

        return true;
    },
    beforeSettingDisplay(id, settings) {
        let value = settings[id];

        if ( 'import_units' === id ) {
            value = value.join(wprm_admin.eol);
        } else if ( 'unit_conversion_units' === id ) {
            let newValue = {};

            for (let unit in value) {
                newValue[unit] = {
                    ...value[unit],
                    aliases: value[unit].aliases.join(';')
                }
            }

            value = newValue;
        }

        return value;
    },
    beforeSettingSave(value, id, settings) {
        if ( 'import_units' === id ) {
            value = value.split(wprm_admin.eol);
        } else if ( 'unit_conversion_units' === id ) {
            let newValue = {};

            for (let unit in value) {
                newValue[unit] = {
                    ...value[unit],
                    aliases: value[unit].aliases.split(';')
                }
            }

            value = newValue;
        }

        return value;
    }
};
