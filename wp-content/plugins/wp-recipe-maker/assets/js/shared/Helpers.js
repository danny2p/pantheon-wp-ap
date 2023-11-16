export default {
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
            ingredientString = ingredientString.replace( /\[\/?adjustable]/ig, '' );

            // Trim.
            ingredientString = ingredientString.trim();
        }

        return ingredientString;
    },
};
