window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.classicEditor = {
	init: () => {
		document.addEventListener( 'click', function(e) {
			for ( var target = e.target; target && target != this; target = target.parentNode ) {
				if ( target.matches( '.wprm-modal-menu-button' ) ) {
					window.WPRecipeMaker.classicEditor.onClickNew( target, e );
					break;
				}
				if ( target.matches( '.wprm-modal-edit-button' ) ) {
					window.WPRecipeMaker.classicEditor.onClickEdit( target, e );
					break;
				}
			}
        }, false );
    },
	onClickNew: ( el, e ) => {
		e.preventDefault();

        let insertedRecipe = false;

        WPRM_Modal.open( 'menu', {
            insertCallback: ( shortcode ) => {
                const editorId = el.dataset.editor;
                
                if ( editorId ) {
                    WPRM_Modal.addTextToEditor( shortcode, editorId );
                }
            },
            saveCallback: ( recipe ) => {
                const editorId = el.dataset.editor;

                if ( editorId ) {
                    if ( ! insertedRecipe ) {
                        WPRM_Modal.addTextToEditor( '[wprm-recipe id="' + recipe.id + '"]', editorId );
                        insertedRecipe = true;
                    } else {
                        WPRM_Modal.refreshEditor( editorId );
                    }
                }
            },
        } );
    },
    onClickEdit: ( el, e ) => {
		e.preventDefault();

        const recipeId = el.dataset.recipe;

        WPRM_Modal.open( 'recipe', {
            recipeId,
            saveCallback: ( recipe ) => {
                const editorId = el.dataset.editor;

                if ( editorId ) {
                    WPRM_Modal.refreshEditor( editorId );
                }
            },
        } );
    },
};

ready(() => {
	window.WPRecipeMaker.classicEditor.init();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}