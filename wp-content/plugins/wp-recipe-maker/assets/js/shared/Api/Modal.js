const modalEndpoint = wprm_admin.endpoints.modal;

import ApiWrapper from '../ApiWrapper';

const rateLimit = 500;

let gettingSuggestions = false;
let gettingSuggestionsAt = false;
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
        const now = Date.now();
        
        if ( false !== gettingSuggestionsAt && rateLimit > now - gettingSuggestionsAt ) {
            return new Promise(r => {
                setTimeout(() => {
                    r( this.getSuggestionsDebounced( args ) );
                }, now - gettingSuggestionsAt );
            });
        }

        gettingSuggestionsAt = now;

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
