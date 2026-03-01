import ApiWrapper from '../ApiWrapper';
import AjaxWrapper from '../AjaxWrapper';

const templateEndpoint = wprm_admin.endpoints.template;
const debounceTime = 500;

let previewPromisesByContext = {};
let previewRequestsByContext = {};
let previewRequestsTimerByContext = {};
let previewRecipeByContext = {};

export default {
    previewShortcode(uid, shortcode, recipeId, previewContext = 'default') {
        if ( ! previewPromisesByContext[previewContext] ) {
            previewPromisesByContext[previewContext] = [];
        }
        if ( ! previewRequestsByContext[previewContext] ) {
            previewRequestsByContext[previewContext] = {};
        }

        previewRequestsByContext[previewContext][uid] = shortcode;
        previewRecipeByContext[previewContext] = recipeId;

        clearTimeout(previewRequestsTimerByContext[previewContext]);
        previewRequestsTimerByContext[previewContext] = setTimeout(() => {
            this.previewShortcodes( previewContext );
        }, debounceTime);

        return new Promise( r => previewPromisesByContext[previewContext].push( r ) );
    },
    previewShortcodes( previewContext = 'default' ) {
        const thesePromises = previewPromisesByContext[previewContext] || [];
        const theseRequests = previewRequestsByContext[previewContext] || {};
        const previewRecipe = previewRecipeByContext[previewContext] || false;
        previewPromisesByContext[previewContext] = [];
        previewRequestsByContext[previewContext] = {};
        previewRecipeByContext[previewContext] = false;

        const data = {
            recipeId: previewRecipe,
            shortcodes: theseRequests,
        };

        fetch(`${templateEndpoint}/preview`, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': wprm_admin.api_nonce,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify(data),
        }).then(response => {
            return response.json().then(json => {
                let result = response.ok ? json.preview : {};

                thesePromises.forEach( r => r( result ) );
            });
        });
    },
    searchRecipes(input) {
        return AjaxWrapper.call('wprm_search_recipes', {
            search: input,
        }).then((data) => {
            // Return recipes_with_id if available, otherwise empty array.
            return data && data.recipes_with_id ? data.recipes_with_id : [];
        });
    },
    save(template) {
        const data = {
            template,
        };

        return ApiWrapper.call( templateEndpoint, 'POST', data );
    },
    delete(slug) {
        const data = {
            slug,
        };

        return ApiWrapper.call( templateEndpoint, 'DELETE', data );
    },
};
