const layoutElements = [
    'wprm-layout-container',
    'wprm-layout-column-container',
    'wprm-layout-column',
];

const propertiesForElement = {
    container: [ 'float', 'align', 'padding', 'custom' ],
    'column-container': [ 'column-mobile', 'column-gap', 'custom' ],
    column: [ 'column-width', 'align', 'padding', 'custom' ],
}

const potentialProperties = {
    float: {
        name: 'Float',
        type: 'dropdown',
        options: {
            none: 'None',
            left: 'Float Left',
            right: 'Float Right',
        },
        classesToValue: ( classes ) => {
            if ( classes.includes( 'wprm-container-float-left' ) ) { return 'left'; }
            if ( classes.includes( 'wprm-container-float-right' ) ) { return 'right'; }
            return 'none';
        },
        valueToClasses: ( value ) => {
            if ( 'none' === value ) {
                return [];
            }
            return [ 'wprm-container-float-' + value ];
        },
    },
    align: {
        name: 'Text Align',
        type: 'dropdown',
        options: {
            left: 'Left',
            center: 'Center',
            right: 'Right',
        },
        classesToValue: ( classes ) => {
            if ( classes.includes( 'wprm-align-center' ) ) { return 'center'; }
            if ( classes.includes( 'wprm-align-right' ) ) { return 'right'; }
            return 'left';
        },
        valueToClasses: ( value ) => {
            if ( 'left' === value ) {
                return [];
            }
            return [ 'wprm-align-' + value ];
        },
    },
    padding: {
        name: 'Padding',
        type: 'dropdown',
        options: {
            none: 'None',
            '5': '5px',
            '10': '10px',
            '20': '20px',
            '30': '30px',
            '40': '40px',
            '50': '50px',
            
        },
        classesToValue: ( classes ) => {
            const widthClass = classes.find( ( c ) => c.startsWith( 'wprm-padding-' ) );
            if ( widthClass ) {
                return widthClass.replace( 'wprm-padding-', '' );
            }

            // Default to none.
            return 'none';
        },
        valueToClasses: ( value ) => {
            if ( 'none' === value ) {
                return [];
            }
            return [ 'wprm-padding-' + value ];
        },
    },
    'column-mobile': {
        name: 'Switch to Rows',
        type: 'dropdown',
        options: {
            never: 'Never',
            mobile: 'On Mobile',
            tablet: 'On Tablet',
            
        },
        classesToValue: ( classes ) => {
            const matchingClass = classes.find( ( c ) => c.startsWith( 'wprm-column-rows-' ) );
            if ( matchingClass ) {
                return matchingClass.replace( 'wprm-column-rows-', '' );
            }
            return 'mobile';
        },
        valueToClasses: ( value ) => {
            if ( 'mobile' === value ) {
                return [];
            }
            return [ 'wprm-column-rows-' + value ];
        },
    },
    'column-gap': {
        name: 'Column Gap',
        type: 'dropdown',
        options: {
            none: 'None',
            '5': '5px',
            '10': '10px',
            '20': '20px',
            '30': '30px',
            '40': '40px',
            '50': '50px',
            
        },
        classesToValue: ( classes ) => {
            const widthClass = classes.find( ( c ) => c.startsWith( 'wprm-column-gap-' ) );
            if ( widthClass ) {
                return widthClass.replace( 'wprm-column-gap-', '' );
            }

            // Default to none.
            return 'none';
        },
        valueToClasses: ( value ) => {
            if ( 'none' === value ) {
                return [];
            }
            return [ 'wprm-column-gap-' + value ];
        },
    },
    'column-width': {
        name: 'Column Width',
        type: 'dropdown',
        options: {
            auto: 'Auto',
            '20': '20%',
            '25': '25%',
            '40': '40%',
            '33': '33.33%',
            '50': '50%',
            '60': '60%',
            '66': '66.66%',
            '75': '75%',
            '80': '80%',
        },
        classesToValue: ( classes ) => {
            const widthClass = classes.find( ( c ) => c.startsWith( 'wprm-column-width-' ) );
            if ( widthClass ) {
                return widthClass.replace( 'wprm-column-width-', '' );
            }

            // Default to auto.
            return 'auto';
        },
        valueToClasses: ( value ) => {
            if ( 'auto' === value ) {
                return [];
            }
            return [ 'wprm-column-width-' + value ];
        },
    },
    custom: {
        name: 'Custom Class',
        help: 'Should not start with wprm-',
        type: 'text',
        classesToValue: ( classes ) => {
            const otherClasses = classes.filter( ( c ) => ! c.startsWith( 'wprm-' ) );
            return otherClasses.join( ' ' );
        },
        valueToClasses: ( value ) => {
            let clean = value.replace( /[^a-zA-Z0-9-_]/g, '' );

            if ( clean ) {
                return [ clean ];
            }

            return [];
        },
    },
};

export default {
    layoutElements,
    propertiesForElement,
    potentialProperties,
};