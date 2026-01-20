const recipeEndpoint = wprm_admin.endpoints.recipe;
const manageEndpoint = wprm_admin.endpoints.manage;
import ApiWrapper from '../ApiWrapper';
import AjaxWrapper from '../AjaxWrapper';

const getMultilingualContext = () => {
    if ( 'undefined' !== typeof wprm_admin_modal && wprm_admin_modal && wprm_admin_modal.multilingual ) {
        return wprm_admin_modal.multilingual;
    }

    if ( 'undefined' !== typeof wprm_admin_manage && wprm_admin_manage && wprm_admin_manage.multilingual ) {
        return wprm_admin_manage.multilingual;
    }

    return false;
};

const getAdminLanguage = () => {
    const multilingual = getMultilingualContext();

    if ( ! multilingual ) {
        return false;
    }

    if ( multilingual.current ) {
        return multilingual.current;
    }

    if ( multilingual.default ) {
        return multilingual.default;
    }

    return false;
};

const maybeInjectAdminLanguage = ( recipe ) => {
    if ( 'public' === wprm_admin.settings.post_type_structure ) {
        return recipe;
    }

    // Only inject admin language if we're creating a new recipe.
    if ( recipe.id ) {
        return recipe;
    }

    if ( Object.prototype.hasOwnProperty.call( recipe, 'language' ) ) {
        return recipe;
    }

    const adminLanguage = getAdminLanguage();

    if ( ! adminLanguage ) {
        return recipe;
    }

    return {
        ...recipe,
        language: adminLanguage,
    };
};

export default {
    get(id) {
        return ApiWrapper.call( `${recipeEndpoint}/${id}?t=${ Date.now() }` );
    },
    getFrontend(id) {
        return ApiWrapper.call( `${recipeEndpoint}/${id}?t=${ Date.now() }` );
    },
    save(recipe) {
        const recipeWithLanguage = maybeInjectAdminLanguage( recipe );

        const data = {
            recipe: recipeWithLanguage,
        };

        // Default to create new recipe.
        let url = recipeEndpoint;
        let method = 'POST';

        // Recipe ID set? Update an existing one.
        const recipeId = recipe.id ? parseInt(recipe.id) : false;
        if ( recipeId ) {
            url += `/${recipeId}`
            method = 'PUT';
        }

        return ApiWrapper.call( url, method, data );
    },
    updateStatus(recipeId, status) {
        const data = {
            status,
        };

        return ApiWrapper.call( `${recipeEndpoint}/${recipeId}`, 'PUT', data );
    },
    delete(id, permanently = false) {
        let endpoint = `${recipeEndpoint}/${id}`;
        
        if ( permanently ) {
            endpoint += '?force=true';
        }

        return ApiWrapper.call( endpoint, 'DELETE' );
    },
    deleteRevision(id) {
        return ApiWrapper.call( `${manageEndpoint}/revision/${id}`, 'DELETE' );
    },
    createPostForRecipe(recipeId) {
        return AjaxWrapper.call('wprm_create_post_for_recipe', {
            recipe_id: recipeId,
        });
    },
    addRecipeToPost(recipeId, postId) {
        return AjaxWrapper.call('wprm_add_recipe_to_post', {
            recipe_id: recipeId,
            post_id: postId,
        });
    },
};
