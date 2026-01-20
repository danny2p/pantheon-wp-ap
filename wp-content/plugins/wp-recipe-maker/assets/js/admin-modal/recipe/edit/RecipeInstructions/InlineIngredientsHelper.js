import Helpers from 'Shared/Helpers';

export default {
    updateInlineIngredientInText( ingredient, text, removed = false ) {
        let updatedText = text;

        if ( ingredient.hasOwnProperty( 'uid' ) ) {
            // Support both regular UIDs (number) and split UIDs (format: "uid:splitId")
            const uid = typeof ingredient.uid === 'string' && ingredient.uid.includes(':') 
                ? ingredient.uid 
                : parseInt( ingredient.uid );

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
                // Compare UIDs - handle both number and string formats
                const uidStr = String(uid);
                const ingredientUidStr = String(ingredientInText.uid);
                if ( uid === ingredientInText.uid || uidStr === ingredientUidStr ) {
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
        return Helpers.getIngredientString( ingredient, includeNotes );
    },
    getShortcodeFor( atts ) {
        // Support both regular UIDs (number) and split UIDs (format: "uid:splitId")
        let uid = atts.hasOwnProperty( 'uid' ) ? atts.uid : 0;
        if ( typeof uid !== 'string' || ! uid.includes( ':' ) ) {
            uid = parseInt( uid ) || 0;
        }
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
        // Support both regular UIDs (number) and split UIDs (format: "uid:splitId")
        let uid = atts.hasOwnProperty( 'uid' ) ? atts.uid : 0;
        if ( typeof uid !== 'string' || ! uid.includes( ':' ) ) {
            uid = parseInt( uid ) || 0;
        }
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
        // Support both regular UIDs (number) and split UIDs (format: "uid:splitId")
        const regex = /\suid=['"]?([^'"]+)['"]/gm;
        let m;
        let uid = false;

        while ((m = regex.exec( attributeString )) !== null) {
            if (m.index === regex.lastIndex) {
                regex.lastIndex++;
            }
            
            const uidValue = m[1];
            // If it contains a colon, it's a split (format: "uid:splitId"), return as string
            // Otherwise, it's a regular UID, return as number for backwards compatibility
            if ( uidValue.includes( ':' ) ) {
                uid = uidValue; // Return as string for splits
            } else {
                uid = parseInt( uidValue ); // Return as number for regular UIDs
            }
        }

        return uid;
    },
};