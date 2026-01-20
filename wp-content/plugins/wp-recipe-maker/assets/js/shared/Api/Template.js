import ApiWrapper from '../ApiWrapper';
import AjaxWrapper from '../AjaxWrapper';

const templateEndpoint = wprm_admin.endpoints.template;
const debounceTime = 500;

let previewPromises = [];
let previewRequests = {};
let previewRequestsTimer = null;

export default {
    previewShortcode(uid, shortcode, recipeId) {
        previewRequests[uid] = shortcode;

        clearTimeout(previewRequestsTimer);
        previewRequestsTimer = setTimeout(() => {
            this.previewShortcodes( recipeId );
        }, debounceTime);

        return new Promise( r => previewPromises.push( r ) );
    },
    previewShortcodes( recipeId ) {
        const thesePromises = previewPromises;
        const theseRequests = previewRequests;
        previewPromises = [];
        previewRequests = {};

        const data = {
            recipeId,
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
