export default {
    stripAdjustableShortcodes( text = '' ) {
        return String( text ).replace( /\[\/?adjustable]/ig, '' ).trim();
    },
    getIngredientString( ingredient, includeNotes = true ) {
        let ingredientString = '';

        let fields = [];
        if ( ingredient.amount ) { fields.push( ingredient.amount ); }
        if ( ingredient.unit ) { fields.push( ingredient.unit ); }
        if ( ingredient.name ) { fields.push( ingredient.name ); }
        if ( includeNotes && ingredient.notes ) { fields.push( ingredient.notes ); }
        
        if ( fields.length ) {
            ingredientString = fields.join( ' ' )
            
            // Remove HTML elements.
            ingredientString = ingredientString.replace( /(<([^>]+)>)/ig, '' );

            // Remove adjustable shortcodes.
            ingredientString = this.stripAdjustableShortcodes( ingredientString );
        }

        return ingredientString;
    },
};
