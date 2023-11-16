window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.elementor = {
	init: () => {
        // Recipes.
        elementor.channels.editor.on( 'wprm:recipe:create', function( view ) {
            WPRM_Modal.open( 'recipe', {
                saveCallback: ( recipe ) => {
                    WPRecipeMaker.elementor.changeRecipeId( view, recipe.id );
                },
            }, true );
        });
        elementor.channels.editor.on( 'wprm:recipe:edit', function( view ) {
            WPRM_Modal.open( 'recipe', {
                recipeId: view.container.settings.attributes.wprm_recipe_id,
                saveCallback: ( recipe ) => {
                    WPRecipeMaker.elementor.changeRecipeId( view, recipe.id );
                },
            }, true );
        });
        elementor.channels.editor.on( 'wprm:recipe:unset', function( view ) {
            WPRecipeMaker.elementor.changeRecipeId( view, 0 );
        });

        // Lists.
        elementor.channels.editor.on( 'wprm:list:create', function( view ) {
            WPRM_Modal.open( 'list', {
                saveCallback: ( list ) => {
                    WPRecipeMaker.elementor.changeListId( view, list.id );
                },
            }, true );
        });
        elementor.channels.editor.on( 'wprm:list:edit', function( view ) {
            WPRM_Modal.open( 'list', {
                listId: view.container.settings.attributes.wprm_list_id,
                saveCallback: ( list ) => {
                    WPRecipeMaker.elementor.changeListId( view, list.id );
                },
            }, true );
        });
        elementor.channels.editor.on( 'wprm:list:unset', function( view ) {
            WPRecipeMaker.elementor.changeListId( view, 0 );
        });
    },
    changeRecipeId( view, id ) {
        parent.window.$e.run( 'document/elements/settings', {
            container: view.container,
            settings: {
                wprm_recipe_id: id,
            },
            options: {
                external: true
            }
        });
    },
    changeListId( view, id ) {
        parent.window.$e.run( 'document/elements/settings', {
            container: view.container,
            settings: {
                wprm_list_id: id,
            },
            options: {
                external: true
            }
        });
    },
};


ready(() => {
	window.WPRecipeMaker.elementor.init();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}