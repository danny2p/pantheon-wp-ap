import ApiWrapper from '../ApiWrapper';

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
        return fetch(wprm_admin.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: 'action=wprm_search_recipes&security=' + wprm_admin.nonce + '&search=' + encodeURIComponent( input ),
            headers: {
                'Accept': 'application/json, text/plain, */*',
                'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8',
            },
        })
        .then((response) => response.json())
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
