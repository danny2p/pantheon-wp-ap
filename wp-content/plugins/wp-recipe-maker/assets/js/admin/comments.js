import Api from 'Shared/Api';
import { __wprm } from 'Shared/Translations';

import '../../css/admin/comments.scss';

window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.comments = {
    change: ( container ) => {
        const column = container.closest( '.column-wprm_rating' );
        const saveButton = column.querySelector( '.wprm-rating-change-save' );

        if ( saveButton ) {
            const oldRating = parseInt( container.dataset.originalRating );
            const newRating = parseInt( container.dataset.currentRating );

            if ( oldRating === newRating ) {
                saveButton.style.display = 'none';
            } else {
                saveButton.style.display = 'block';
                saveButton.querySelector( '.wprm-rating-change' ).innerHTML = ' (' + oldRating + ' &rArr; ' + newRating + ')';
            }
        }
    },
    save: ( el ) => {
        const column = el.closest( '.column-wprm_rating' );
        const container = column.querySelector( '.wprm-comment-ratings-container' );

        const commentId = parseInt( el.dataset.commentId );

        const oldRating = parseInt( container.dataset.originalRating );
        const newRating = parseInt( container.dataset.currentRating );
        
        if ( commentId && oldRating !== newRating ) {
            Api.rating.updateComment( commentId, newRating ).then((data) => {
                if ( data && data.hasOwnProperty( 'rating' ) ) {
                    container.dataset.originalRating = data.rating;
                    window.WPRecipeMaker.comments.change( container );
                } else {
                    alert( __wprm( 'Something went wrong during saving.' ) );
                }
            });
        }
    },
};