const modalEndpoint = wprm_admin.endpoints.modal;

import ApiWrapper from '../ApiWrapper';

let gettingSuggestions = false;
let gettingSuggestionsNextArgs = false;

export default {
    getSuggestions(args) {
        if ( ! gettingSuggestions ) {
            return this.getSuggestionsDebounced(args);
        } else {
            gettingSuggestionsNextArgs = args;
            return new Promise(r => r(false));
        }
    },
    getSuggestionsDebounced(args) {
        gettingSuggestions = true;

        return ApiWrapper.call( `${modalEndpoint}/suggest`, 'POST', args ).then(json => {
            // Check if another request is queued.
            if ( gettingSuggestionsNextArgs ) {
                const newArgs = gettingSuggestionsNextArgs;
                gettingSuggestionsNextArgs = false;

                return this.getSuggestionsDebounced(newArgs);
            } else {
                // Return this request.
                gettingSuggestions = false;
                return json;
            }
        });
    },
};
