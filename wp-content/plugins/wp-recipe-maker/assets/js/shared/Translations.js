let translations = {};

// Get public translations.
if ( window.hasOwnProperty( 'wprm_public' ) && wprm_public.hasOwnProperty( 'translations' ) ) {
    translations = {
        ...translations,
        ...wprm_public.translations,
    };
}

// Get admin translations.
if ( window.hasOwnProperty( 'wprm_admin' ) && wprm_admin.hasOwnProperty( 'translations' ) ) {
    translations = {
        ...translations,
        ...wprm_admin.translations,
    };
}

export function __wprm( text, domain = 'wp-recipe-maker' ) {
    if ( translations.hasOwnProperty( text ) ) {
        return translations[ text ];
    } else {
        return text;
    }
};
