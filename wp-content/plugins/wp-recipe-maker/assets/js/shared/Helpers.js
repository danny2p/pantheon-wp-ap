export default {
    getIngredientString( ingredient ) {
        let ingredientString = '';

        let fields = [];
        if ( ingredient.amount ) { fields.push( ingredient.amount ); }
        if ( ingredient.unit ) { fields.push( ingredient.unit ); }
        if ( ingredient.name ) { fields.push( ingredient.name ); }
        if ( ingredient.notes ) { fields.push( ingredient.notes ); }
        
        if ( fields.length ) {
            ingredientString = fields.join( ' ' ).replace( /(<([^>]+)>)/ig, '' ).trim();
        }

        return ingredientString;
    },
};
