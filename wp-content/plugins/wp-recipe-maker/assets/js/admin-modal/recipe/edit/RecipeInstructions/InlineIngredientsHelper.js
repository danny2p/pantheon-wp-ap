export default {
    updateInlineIngredientInText( ingredient, text, removed = false ) {
        let updatedText = text;

        if ( ingredient.hasOwnProperty( 'uid' ) ) {
            const uid = parseInt( ingredient.uid );

            const ingredientText = this.getIngredientText( ingredient );
            const atts = {
                uid,
                text: ingredientText,
                removed,
            }
            const ingredientShortcode = this.getShortcodeFor( atts );
            const ingredientHtml = this.getHtmlFor( atts );

            const ingredientsInText = this.findAll( text );
            for ( let ingredientInText of ingredientsInText ) {
                if ( uid === ingredientInText.uid ) {
                    if ( 'html' === ingredientInText.type ) {
                        updatedText = text.replace( ingredientInText.full, ingredientHtml );
                    } else {
                        updatedText = text.replace( ingredientInText.full, ingredientShortcode );
                    }
                }
            }
        }

        return updatedText;
    },
    getIngredientText( ingredient, includeNotes = false ) {
        let ingredientString = '';

        let fields = [];
        if ( ingredient.amount ) { fields.push( ingredient.amount ); }
        if ( ingredient.unit ) { fields.push( ingredient.unit ); }
        if ( ingredient.name ) { fields.push( ingredient.name ); }
        if ( includeNotes && ingredient.notes ) { fields.push( ingredient.notes ); }
        
        if ( fields.length ) {
            ingredientString = fields.join( ' ' ).replace( /(<([^>]+)>)/ig, '' ).trim();
        }

        return ingredientString;
    },
    getShortcodeFor( atts ) {
        const uid = atts.hasOwnProperty( 'uid' ) ? parseInt( atts.uid ) : 0;
        let text = atts.hasOwnProperty( 'text' ) ? atts.text : '';
        const deleted = atts.hasOwnProperty( 'removed' ) && atts.removed ? true : false;

        text = text.replace(/"/gm, '&quot;');
        text = text.replace(/\]/gm, '&#93;');

        let shortcode = `[wprm-ingredient text="${ text }" uid="${uid}"`;
        if ( deleted ) {
            shortcode += ' removed="1";'
        }
        shortcode += ']';

        return shortcode;
    },
    getHtmlFor( atts ) {
        const uid = atts.hasOwnProperty( 'uid' ) ? parseInt( atts.uid ) : 0;
        const text = atts.hasOwnProperty( 'text' ) ? atts.text : '';
        const deleted = atts.hasOwnProperty( 'removed' ) && atts.removed ? true : false;

        let html = `<wprm-ingredient uid="${uid}"`;
        if ( deleted ) {
            html += ' removed="1";'
        }
        html += `>${ text }</wprm-ingredient>`;

        return html;
    },
    findAll( text ) {
        let ingredients = [
            ...this.findByShortcode( text ),
            ...this.findByHtml( text ),
        ];

        return ingredients;
    },
    findByShortcode( text ) {
        const regex = /\[wprm-ingredient\s([^\]]*)\]/gm;
        let m;
        let ingredients = [];

        while ((m = regex.exec(text)) !== null) {
            if (m.index === regex.lastIndex) {
                regex.lastIndex++;
            }

            ingredients.push({
                full: m[0],
                uid: this.getUidFromAttributes( ' ' + m[1] ),
                type: 'shortcode',
            });
        }

        return ingredients;
    },
    findByHtml( text ) {
        const regex = /<wprm-ingredient\s([^>]*)>(.*?)<\/wprm-ingredient>/gm;
        let m;
        let ingredients = [];

        while ((m = regex.exec(text)) !== null) {
            if (m.index === regex.lastIndex) {
                regex.lastIndex++;
            }

            ingredients.push({
                full: m[0],
                uid: this.getUidFromAttributes( ' ' + m[1] ),
                type: 'html',
            });
        }

        return ingredients;
    },
    getUidFromAttributes( attributeString ) {
        const regex = /\suid=['"]?(\d+)['"]/gm;
        let m;
        let uid = false;

        while ((m = regex.exec( attributeString )) !== null) {
            if (m.index === regex.lastIndex) {
                regex.lastIndex++;
            }
            
            uid = parseInt( m[1] );
        }

        return uid;
    },
};