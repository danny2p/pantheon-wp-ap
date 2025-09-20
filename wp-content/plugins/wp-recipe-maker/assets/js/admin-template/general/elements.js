const layoutElements = [
    'wprm-layout-container',
    'wprm-layout-column-container',
    'wprm-layout-column',
];

const propertiesForElement = {
    container: [ 'float', 'align', 'padding', 'background-color', 'text-color', 'custom' ],
    'column-container': [ 'vertical-align', 'column-gap', 'column-mobile', 'column-mobile-reverse', 'row-gap', 'custom' ],
    column: [ 'column-width', 'align', 'align-rows', 'padding', 'background-color', 'text-color', 'custom' ],
}

const potentialProperties = {
    'text-color': {
        name: 'Text Color',
        type: 'color',
        styleToValue: ( style ) => {
            for ( let i = 0; i < style.length; i++ ) {
                if ( style[i].startsWith( 'text-color: ' ) ) {
                    return style[i].replace( 'text-color: ', '' );
                }
            }
            return 'inherit';
        },
        valueToStyle: ( value ) => {
            if ( 'inherit' === value ) {
                return [];
            }
            return [ 'text-color: ' + value ];
        },
    },
    'background-color': {
        name: 'Background Color',
        type: 'color',
        styleToValue: ( style ) => {
            for ( let i = 0; i < style.length; i++ ) {
                if ( style[i].startsWith( 'background-color: ' ) ) {
                    return style[i].replace( 'background-color: ', '' );
                }
            }
            return 'inherit';
        },
        valueToStyle: ( value ) => {
            if ( 'inherit' === value ) {
                return [];
            }
            return [ 'background-color: ' + value ];
        },
    },
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
    'vertical-align': {
        name: 'Vertical Align',
        type: 'dropdown',
        options: {
            top: 'Top',
            middle: 'Middle',
            bottom: 'Bottom',
        },
        classesToValue: ( classes ) => {
            if ( classes.includes( 'wprm-align-middle' ) ) { return 'middle'; }
            if ( classes.includes( 'wprm-align-bottom' ) ) { return 'bottom'; }
            return 'top';
        },
        valueToClasses: ( value ) => {
            if ( 'top' === value ) {
                return [];
            }
            return [ 'wprm-align-' + value ];
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
    'align-rows': {
        name: 'Text Align (switched to rows)',
        type: 'dropdown',
        options: {
            default: 'Same as default',
            left: 'Left',
            center: 'Center',
            right: 'Right',
        },
        classesToValue: ( classes ) => {
            if ( classes.includes( 'wprm-align-rows-left' ) ) { return 'left'; }
            if ( classes.includes( 'wprm-align-rows-center' ) ) { return 'center'; }
            if ( classes.includes( 'wprm-align-rows-right' ) ) { return 'right'; }
            return 'default';
        },
        valueToClasses: ( value ) => {
            if ( 'default' === value ) {
                return [];
            }
            return [ 'wprm-align-rows-' + value ];
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
            mobile: 'On Mobile (480px screen width)',
            tablet: 'On Tablet (768px screen width)',
            'recipe-900': 'At 900px recipe card width',
            'recipe-800': 'At 800px recipe card width',
            'recipe-700': 'At 700px recipe card width',
            'recipe-600': 'At 600px recipe card width',
            'recipe-500': 'At 500px recipe card width',
            'recipe-400': 'At 400px recipe card width',
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
    'column-mobile-reverse': {
        name: 'Reverse Order on Switch',
        type: 'toggle',
        classesToValue: ( classes ) => {
            const matchingClass = classes.includes( 'wprm-column-rows-reverse' );
            if ( matchingClass ) {
                return '1';
            }
            return '0';
        },
        valueToClasses: ( value ) => {
            if ( '1' === value ) {
                return [ 'wprm-column-rows-reverse' ];
            }
            return [];
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
    'row-gap': {
        name: 'Row Gap',
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
            const gapClass = classes.find( ( c ) => c.startsWith( 'wprm-row-gap-' ) );
            if ( gapClass ) {
                return gapClass.replace( 'wprm-row-gap-', '' );
            }

            // Default to none.
            return 'none';
        },
        valueToClasses: ( value ) => {
            if ( 'none' === value ) {
                return [];
            }
            return [ 'wprm-row-gap-' + value ];
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